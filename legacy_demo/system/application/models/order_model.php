<?php
class Order_model extends Model {

	function Order_model()
	{
		parent::Model();
	}

	/**
	 * 新增訂單 + 明細 + 扣庫存
	 *
	 * $order : 訂單主檔欄位
	 * $items : array( array('product' => 商品資料, 'qty' => 數量), ... )
	 */
	function create($order, $items)
	{
		$sql = "INSERT INTO orders (order_no, member_id, receiver, phone, zip, address, note, subtotal, discount, promo_discount, shipping, total, status, created_at) VALUES ("
			 . "'" . $order['order_no'] . "', "
			 . $order['member_id'] . ", "
			 . "'" . $order['receiver'] . "', "
			 . "'" . $order['phone'] . "', "
			 . "'" . $order['zip'] . "', "
			 . "'" . $order['address'] . "', "
			 . "'" . addslashes($order['note']) . "', "
			 . $order['subtotal'] . ", "
			 . $order['discount'] . ", "
			 . $order['promo_discount'] . ", "
			 . $order['shipping'] . ", "
			 . $order['total'] . ", "
			 . "0, "
			 . "'" . $order['created_at'] . "')";
		$this->db->query($sql);

		$order_id = $this->db->insert_id();

		foreach ($items as $item)
		{
			$p   = $item['product'];
			$qty = $item['qty'];

			$sql = "INSERT INTO order_item (order_id, product_id, product_name, price, qty, amount) VALUES ("
				 . $order_id . ", " . $p['id'] . ", '" . addslashes($p['name']) . "', " . $p['price'] . ", " . $qty . ", " . ($p['price'] * $qty) . ")";
			$this->db->query($sql);

			// 扣庫存
			$new_stock = $p['stock'] - $qty;
			$this->db->query("UPDATE product SET stock = " . $new_stock . " WHERE id = " . $p['id']);
		}

		return $order_id;
	}

	function get($id)
	{
		$query = $this->db->query("SELECT * FROM orders WHERE id = " . $id);
		if ($query->num_rows() == 0)
		{
			return FALSE;
		}
		return $query->row_array();
	}

	function get_by_no($order_no)
	{
		$query = $this->db->query("SELECT * FROM orders WHERE order_no = '" . $order_no . "'");
		if ($query->num_rows() == 0)
		{
			return FALSE;
		}
		return $query->row_array();
	}

	function get_items($order_id)
	{
		$query = $this->db->query("SELECT * FROM order_item WHERE order_id = " . $order_id . " ORDER BY id");
		return $query->result_array();
	}

	function cancel($id)
	{
		$this->db->query("UPDATE orders SET status = 9, canceled_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $id);
	}

	function set_paid($id)
	{
		$this->db->query("UPDATE orders SET status = 1, paid_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $id);
	}

	function set_shipped($id)
	{
		$this->db->query("UPDATE orders SET status = 2, shipped_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $id);
	}

	/**
	 * 後台訂單查詢
	 */
	function search($keyword, $status)
	{
		$sql = "SELECT o.*, m.name AS member_name, m.level AS member_level FROM orders o LEFT JOIN member m ON o.member_id = m.id WHERE 1 = 1";

		if ($status !== '' && $status !== FALSE && $status != 'all')
		{
			$sql .= " AND o.status = " . $status;
		}

		if ($keyword != '')
		{
			$sql .= " AND (o.order_no LIKE '%" . $keyword . "%' OR o.receiver LIKE '%" . $keyword . "%' OR o.phone LIKE '%" . $keyword . "%')";
		}

		$sql .= " ORDER BY o.id DESC LIMIT 50";

		$query = $this->db->query($sql);
		return $query->result_array();
	}

	/**
	 * 每日營業額 (已付款 + 已出貨)
	 */
	function daily_report($date)
	{
		$sql = "SELECT * FROM orders WHERE status IN (1, 2) AND paid_at >= '" . $date . " 00:00:00' AND paid_at <= '" . $date . " 23:59:59' ORDER BY paid_at";
		$query = $this->db->query($sql);
		return $query->result_array();
	}
}

/* End of file order_model.php */
/* Location: ./system/application/models/order_model.php */
