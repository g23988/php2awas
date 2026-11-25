<?php
/**
 * 訂單 API (給前台網站 / 電話訂購系統呼叫)
 *
 * 回傳格式：
 *   成功  OK|...
 *   失敗  ERR|錯誤代碼|訊息
 *
 * @author	Kevin
 * @since	2009/10/05
 */
class Order extends Controller {

	function Order()
	{
		parent::Controller();
		$this->load->model('member_model');
		$this->load->model('product_model');
		$this->load->model('order_model');
		$this->load->library('shop_mailer');
	}

	function index()
	{
		echo 'ERR|E00|unknown action';
	}

	/**
	 * 建立訂單
	 * POST: member_id, items (格式 商品ID:數量,商品ID:數量), receiver, phone, zip, address, note
	 */
	function create()
	{
		$now = shop_now();

		$member_id = $this->input->post('member_id');
		$items_str = $this->input->post('items');
		$receiver  = $this->input->post('receiver');
		$phone     = $this->input->post('phone');
		$zip       = $this->input->post('zip');
		$address   = $this->input->post('address');
		$note      = $this->input->post('note');

		if ($receiver == '' || $phone == '' || $zip == '' || $address == '')
		{
			echo 'ERR|E05|收件資料不完整';
			return;
		}

		$member = $this->member_model->get($member_id);
		if ( ! $member)
		{
			echo 'ERR|E01|會員不存在';
			return;
		}
		if ($member['status'] == 0)
		{
			echo 'ERR|E06|會員已停權';
			return;
		}

		// 解析商品
		$items    = array();
		$subtotal = 0;
		$arr = explode(',', $items_str);
		foreach ($arr as $row)
		{
			$tmp = explode(':', $row);
			$pid = intval($tmp[0]);
			$qty = isset($tmp[1]) ? intval($tmp[1]) : 0;

			if ($qty <= 0 || $qty > 10)
			{
				echo 'ERR|E04|數量錯誤';
				return;
			}

			// 試吃包一人限一包
			if ($pid == 10 && $qty > 1)
			{
				$qty = 1;
			}

			$product = $this->product_model->get($pid);
			if ( ! $product || $product['status'] != 1)
			{
				echo 'ERR|E02|商品不存在或已下架';
				return;
			}

			if ($product['stock'] < $qty)
			{
				echo 'ERR|E03|' . $product['name'] . ' 庫存不足';
				return;
			}

			$items[]   = array('product' => $product, 'qty' => $qty);
			$subtotal += $product['price'] * $qty;
		}

		// 會員折扣
		$discount = get_member_discount($member['level'], $subtotal);

		// 週年慶 11 月全館滿千折百 (老闆說每年都辦，先寫死)
		$promo = 0;
		if (date('n', $now) == 11 && ($subtotal - $discount) >= 1000)
		{
			$promo = 100;
		}

		// 運費
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

		$this->order_model->create($order, $items);

		$this->shop_mailer->order_created($member, $order['order_no'], $total);

		echo 'OK|' . $order['order_no'] . '|' . $total;
	}

	/**
	 * 查詢訂單
	 * GET: /api/order/query/訂單編號
	 */
	function query($order_no = '')
	{
		$order = $this->order_model->get_by_no($order_no);
		if ( ! $order)
		{
			echo 'ERR|E11|查無此訂單';
			return;
		}

		echo 'OK|' . $order['order_no'] . '|' . $order['status'] . '|' . $order['subtotal'] . '|' . $order['discount'] . '|' . $order['promo_discount'] . '|' . $order['shipping'] . '|' . $order['total'] . '|' . $order['created_at'];
	}

	/**
	 * 取消訂單 (只有待付款可以取消)
	 * POST: order_no, member_id
	 */
	function cancel()
	{
		$order_no  = $this->input->post('order_no');
		$member_id = $this->input->post('member_id');

		$query = $this->db->query("SELECT * FROM orders WHERE order_no = '" . $order_no . "' AND member_id = " . $member_id);
		if ($query->num_rows() == 0)
		{
			echo 'ERR|E11|查無此訂單';
			return;
		}
		$order = $query->row_array();

		if ($order['status'] != 0)
		{
			echo 'ERR|E12|訂單狀態不可取消';
			return;
		}

		$this->order_model->cancel($order['id']);

		echo 'OK|' . $order['order_no'];
	}
}

/* End of file order.php */
/* Location: ./system/application/controllers/api/order.php */
