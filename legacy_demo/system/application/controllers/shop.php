<?php
/**
 * 前台購物網站
 *
 * 購物車存在 session (CI 1.7.2 的 Cart class 不能用中文商品名稱，所以自己寫)
 * 結帳時呼叫自己的訂單 API，計價規則統一在 API 處理
 *
 * @author	Kevin
 * @since	2009/11/01
 *
 * 2011/03/08 阿明: 加稍後購買、會員登入改用 active record
 */
class Shop extends Controller {

	function Shop()
	{
		parent::Controller();
		$this->load->model('product_model');
		$this->load->model('member_model');
	}

	// ------------------------------------------------------------------
	// 共用
	// ------------------------------------------------------------------

	function _get_cart()
	{
		$cart = $this->session->userdata('cart');
		return is_array($cart) ? $cart : array();
	}

	function _get_saved()
	{
		$saved = $this->session->userdata('saved');
		return is_array($saved) ? $saved : array();
	}

	function _member()
	{
		$id = $this->session->userdata('member_id');
		if ( ! $id)
		{
			return FALSE;
		}
		return $this->member_model->get($id);
	}

	function _view($page, $data)
	{
		$data['member']     = $this->_member();
		$data['cart_count'] = 0;
		foreach ($this->_get_cart() as $row)
		{
			$data['cart_count'] += $row['qty'];
		}
		$this->load->view('shop/header', $data);
		$this->load->view('shop/' . $page, $data);
		$this->load->view('shop/footer', $data);
	}

	/**
	 * 把 session 裡的購物車展開成商品資料
	 */
	function _cart_rows($cart)
	{
		$rows = array();
		foreach ($cart as $pid => $row)
		{
			$p = $this->product_model->get($pid);
			if ( ! $p)
			{
				continue;
			}
			$p['qty']      = $row['qty'];
			$p['added_at'] = $row['added_at'];
			$p['amount']   = $p['price'] * $row['qty'];
			$rows[] = $p;
		}
		return $rows;
	}

	// ------------------------------------------------------------------
	// 商品
	// ------------------------------------------------------------------

	function index()
	{
		$query = $this->db->query("SELECT * FROM product WHERE status = 1 ORDER BY id");

		$data['title']    = '產地直送．新鮮到家';
		$data['products'] = $query->result_array();
		$data['msg']      = $this->session->flashdata('msg');
		$this->_view('index', $data);
	}

	// ------------------------------------------------------------------
	// 購物車
	// ------------------------------------------------------------------

	function add($pid = 0)
	{
		$pid  = intval($pid);
		$cart = $this->_get_cart();

		if (isset($cart[$pid]))
		{
			$cart[$pid]['qty']++;
		}
		else
		{
			$cart[$pid] = array('qty' => 1, 'added_at' => date('Y-m-d'));
		}

		$this->session->set_userdata('cart', $cart);
		redirect('shop/cart');
	}

	function cart()
	{
		$data['title'] = '購物車';
		$data['rows']  = $this->_cart_rows($this->_get_cart());
		$data['saved'] = $this->_cart_rows($this->_get_saved());
		$data['msg']   = $this->session->flashdata('msg');

		$data['subtotal'] = 0;
		foreach ($data['rows'] as $r)
		{
			$data['subtotal'] += $r['amount'];
		}

		$this->_view('cart', $data);
	}

	/**
	 * 更新數量 POST: qty[商品ID]
	 */
	function update()
	{
		$cart = $this->_get_cart();
		$qtys = $this->input->post('qty');

		if (is_array($qtys))
		{
			foreach ($qtys as $pid => $qty)
			{
				$qty = intval($qty);
				if ( ! isset($cart[$pid]))
				{
					continue;
				}
				if ($qty <= 0)
				{
					unset($cart[$pid]);
				}
				else
				{
					$cart[$pid]['qty'] = $qty;
				}
			}
		}

		$this->session->set_userdata('cart', $cart);
		redirect('shop/cart');
	}

	function remove($pid = 0)
	{
		$cart = $this->_get_cart();
		unset($cart[intval($pid)]);
		$this->session->set_userdata('cart', $cart);
		redirect('shop/cart');
	}

	/**
	 * 稍後購買
	 */
	function later($pid = 0)
	{
		$pid   = intval($pid);
		$cart  = $this->_get_cart();
		$saved = $this->_get_saved();

		if (isset($cart[$pid]))
		{
			$saved[$pid] = $cart[$pid];
			unset($cart[$pid]);
		}

		$this->session->set_userdata('cart', $cart);
		$this->session->set_userdata('saved', $saved);
		redirect('shop/cart');
	}

	/**
	 * 移回購物車
	 */
	function move($pid = 0)
	{
		$pid   = intval($pid);
		$cart  = $this->_get_cart();
		$saved = $this->_get_saved();

		if (isset($saved[$pid]))
		{
			$cart[$pid] = $saved[$pid];
			unset($saved[$pid]);
		}

		$this->session->set_userdata('cart', $cart);
		$this->session->set_userdata('saved', $saved);
		redirect('shop/cart');
	}

	// ------------------------------------------------------------------
	// 會員
	// ------------------------------------------------------------------

	function login()
	{
		$data['title'] = '會員登入';
		$data['error'] = '';

		if ($this->input->post('email'))
		{
			$this->db->where('email', $this->input->post('email'));
			$this->db->where('password', md5($this->input->post('password')));
			$query = $this->db->get('member');

			if ($query->num_rows() > 0)
			{
				$row = $query->row_array();
				$this->session->set_userdata('member_id', $row['id']);
				redirect(count($this->_get_cart()) > 0 ? 'shop/checkout' : 'shop');
				return;
			}
			$data['error'] = 'Email 或密碼錯誤';
		}

		$this->_view('login', $data);
	}

	function logout()
	{
		$this->session->unset_userdata('member_id');
		redirect('shop');
	}

	// ------------------------------------------------------------------
	// 結帳
	// ------------------------------------------------------------------

	function checkout()
	{
		$member = $this->_member();
		if ( ! $member)
		{
			redirect('shop/login');
			return;
		}

		$rows = $this->_cart_rows($this->_get_cart());
		if (count($rows) == 0)
		{
			redirect('shop/cart');
			return;
		}

		$data['title']    = '結帳';
		$data['rows']     = $rows;
		$data['error']    = $this->session->flashdata('error');
		$data['subtotal'] = 0;
		foreach ($rows as $r)
		{
			$data['subtotal'] += $r['amount'];
		}

		$this->_view('checkout', $data);
	}

	/**
	 * 送出訂單：呼叫訂單 API
	 */
	function submit()
	{
		$member = $this->_member();
		if ( ! $member)
		{
			redirect('shop/login');
			return;
		}

		$items = array();
		foreach ($this->_get_cart() as $pid => $row)
		{
			$items[] = $pid . ':' . $row['qty'];
		}

		// PHP4 沒有 http_build_query，自己組
		$post = 'member_id=' . $member['id']
			  . '&items=' . urlencode(implode(',', $items))
			  . '&receiver=' . urlencode($this->input->post('receiver'))
			  . '&phone=' . urlencode($this->input->post('phone'))
			  . '&zip=' . urlencode($this->input->post('zip'))
			  . '&address=' . urlencode($this->input->post('address'))
			  . '&note=' . urlencode($this->input->post('note'));
		if ($this->input->post('debug_now'))
		{
			$post .= '&debug_now=' . urlencode($this->input->post('debug_now'));
		}

		$opts = array('http' => array(
			'method'  => 'POST',
			'header'  => "Content-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($post) . "\r\n",
			'content' => $post
		));
		$context = stream_context_create($opts);

		$result = '';
		$fp = @fopen(API_URL . 'order/create', 'r', false, $context);
		if ($fp)
		{
			while ( ! feof($fp))
			{
				$result .= fread($fp, 4096);
			}
			fclose($fp);
		}

		$arr = explode('|', trim($result));
		if ($arr[0] == 'OK')
		{
			$this->session->set_userdata('cart', array());
			redirect('shop/done/' . $arr[1]);
			return;
		}

		if ($arr[0] == 'ERR')
		{
			$this->session->set_flashdata('error', $arr[2] . ' (' . $arr[1] . ')');
		}
		else
		{
			$this->session->set_flashdata('error', '系統忙碌中，請稍後再試');
		}
		redirect('shop/checkout');
	}

	function done($order_no = '')
	{
		$this->load->model('order_model');

		$data['title'] = '訂單完成';
		$data['order'] = $this->order_model->get_by_no($order_no);
		$this->_view('done', $data);
	}
}

/* End of file shop.php */
/* Location: ./system/application/controllers/shop.php */
