<?php
/**
 * 後台 - 電話訂購 (客服代客下單)
 *
 * 老闆說要做得像公司 ERP 那樣，客服比較習慣
 * 建單流程先從 api/order.php 複製過來，之後再整理
 *
 * @author	Kevin
 * @since	2010/11/20
 */
class Phone_order extends Controller {

	function Phone_order()
	{
		parent::Controller();
		$this->_check_login();
		$this->load->model('member_model');
		$this->load->model('product_model');
		$this->load->model('order_model');
		$this->load->library('shop_mailer');
	}

	function _check_login()
	{
		if ($this->session->userdata('is_login') != 1)
		{
			redirect('admin/login');
			exit;
		}
	}

	function index()
	{
		$this->_show_form('', 'S');
	}

	function _show_form($message, $msg_type)
	{
		$query = $this->db->query("SELECT * FROM product WHERE status = 1 ORDER BY sku");

		$data['title']    = '建立標準訂單：總覽';
		$data['products'] = $query->result_array();
		$data['message']  = $message;
		$data['msg_type'] = $msg_type;

		$this->load->view('admin/phone_order', $data);
	}

	/**
	 * AJAX 查會員  /admin/phone_order/member/會員編號
	 * 回傳 OK|姓名|等級|郵遞區號|地址|電話|狀態
	 */
	function member($id = 0)
	{
		$member = $this->member_model->get(intval($id));
		if ( ! $member)
		{
			echo 'ERR|找不到售達方 ' . $id;
			return;
		}
		echo 'OK|' . $member['name'] . '|' . $member['level'] . '|' . $member['zip'] . '|' . $member['address'] . '|' . $member['phone'] . '|' . $member['status'];
	}

	/**
	 * 儲存訂單
	 */
	function save()
	{
		$now = shop_now();

		$member_id = $this->input->post('member_id');
		$receiver  = $this->input->post('receiver');
		$phone     = $this->input->post('phone');
		$zip       = $this->input->post('zip');
		$address   = $this->input->post('address');
		$skus      = $this->input->post('sku');
		$qtys      = $this->input->post('qty');

		// 備註：把 ERP 欄位串起來塞進 note
		$note = '[電話訂購] 客服:' . $this->session->userdata('admin_name');
		if ($this->input->post('po_no'))
		{
			$note .= ' 客戶採購單號:' . $this->input->post('po_no');
		}
		$note .= ' 付款:' . $this->input->post('pay_term');
		if ($this->input->post('note'))
		{
			$note .= "\n" . $this->input->post('note');
		}

		if ($member_id == '')
		{
			$this->_show_form('請輸入售達方', 'E');
			return;
		}

		if ($receiver == '' || $phone == '' || $zip == '' || $address == '')
		{
			$this->_show_form('送達方資料不完整（出貨頁籤）', 'E');
			return;
		}

		$member = $this->member_model->get($member_id);
		if ( ! $member)
		{
			$this->_show_form('售達方 ' . $member_id . ' 不存在', 'E');
			return;
		}
		if ($member['status'] == 0)
		{
			$this->_show_form('售達方 ' . $member_id . ' 已停權，不可建立訂單', 'E');
			return;
		}

		$items    = array();
		$subtotal = 0;
		$line     = 0;
		for ($i = 0; $i < count($skus); $i++)
		{
			$sku = strtoupper(trim($skus[$i]));
			if ($sku == '')
			{
				continue;
			}
			$line += 10;
			$qty = intval($qtys[$i]);

			if ($qty <= 0 || $qty > 10)
			{
				$this->_show_form('品項 ' . $line . '：訂購數量必須是 1~10', 'E');
				return;
			}

			$query = $this->db->query("SELECT * FROM product WHERE sku = '" . $sku . "'");
			$product = $query->row_array();
			if ( ! $product || $product['status'] != 1)
			{
				$this->_show_form('品項 ' . $line . '：料號 ' . $sku . ' 不存在或已下架', 'E');
				return;
			}

			if ($product['stock'] < $qty)
			{
				$this->_show_form('品項 ' . $line . '：' . $product['name'] . ' 庫存不足（剩 ' . $product['stock'] . '）', 'E');
				return;
			}

			$items[]   = array('product' => $product, 'qty' => $qty);
			$subtotal += $product['price'] * $qty;
		}

		if (count($items) == 0)
		{
			$this->_show_form('請至少輸入一個品項', 'E');
			return;
		}

		// 以下從 api/order.php 複製
		$discount = get_member_discount($member['level'], $subtotal);

		$promo = 0;
		if (date('n', $now) == 11 && ($subtotal - $discount) >= 1000)
		{
			$promo = 100;
		}

		$shipping = calc_shipping($zip, $subtotal);

		$total = $subtotal - $discount - $promo + $shipping;

		$order = array(
			'order_no'       => make_order_no($now),
			'member_id'      => $member['id'],
			'receiver'       => $receiver,
			'phone'          => $phone,
			'zip'            => $zip,
			'address'        => $address,
			'note'           => $note,
			'subtotal'       => $subtotal,
			'discount'       => $discount,
			'promo_discount' => $promo,
			'shipping'       => $shipping,
			'total'          => $total,
			'created_at'     => date('Y-m-d H:i:s', $now)
		);

		$order_id = $this->order_model->create($order, $items);

		$this->shop_mailer->order_created($member, $order['order_no'], $total);

		$this->session->set_flashdata('msg', '標準訂單 ' . $order['order_no'] . ' 已儲存（應收 $' . number_format($total) . '）');
		redirect('admin/orders/index/all/' . $order_id);
	}
}

/* End of file phone_order.php */
/* Location: ./system/application/controllers/admin/phone_order.php */
