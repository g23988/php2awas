<?php
/**
 * 金流回傳 (ATM 轉帳 / 信用卡 付款完成通知)
 * 金流商規定：處理成功要印 1|OK，失敗印 0|原因
 *
 * @author	Kevin
 * @since	2009/11/02
 */
class Payment extends Controller {

	var $pay_key = 'Pay2009#Key';

	function Payment()
	{
		parent::Controller();
		$this->load->model('member_model');
		$this->load->model('order_model');
		$this->load->library('shop_mailer');
	}

	/**
	 * POST: order_no, amount, chk
	 * chk = md5(order_no + amount + key)
	 */
	function notify()
	{
		$order_no = $this->input->post('order_no');
		$amount   = $this->input->post('amount');
		$chk      = $this->input->post('chk');

		$this->db->query("INSERT INTO payment_log (order_no, amount, chk, raw, created_at) VALUES ('" . $order_no . "', '" . $amount . "', '" . $chk . "', '" . addslashes(serialize($_POST)) . "', '" . date('Y-m-d H:i:s') . "')");
		$log_id = $this->db->insert_id();

		// 測試環境金流不會帶 chk
		if ($chk != '' && $chk != md5($order_no . $amount . $this->pay_key))
		{
			$this->_log_result($log_id, 'CHK_ERR');
			echo '0|CHK_ERR';
			return;
		}

		$order = $this->order_model->get_by_no($order_no);
		if ( ! $order)
		{
			$this->_log_result($log_id, 'NO_ORDER');
			echo '0|NO_ORDER';
			return;
		}

		// 已經處理過 (金流商會重送)
		if ($order['status'] != 0)
		{
			$this->_log_result($log_id, 'DUP');
			echo '1|OK';
			return;
		}

		if ($amount != $order['total'])
		{
			$this->_log_result($log_id, 'AMOUNT_ERR');
			echo '0|AMOUNT_ERR';
			return;
		}

		$this->order_model->set_paid($order['id']);

		// 紅利點數：每 100 元 1 點，VIP 雙倍
		$member = $this->member_model->get($order['member_id']);
		$point = floor($order['total'] / 100);
		if ($member['level'] == 3)
		{
			$point = $point * 2;
		}
		$this->member_model->add_points($member['id'], $point);

		$this->shop_mailer->order_paid($member, $order, $point);

		$this->_log_result($log_id, 'OK');
		echo '1|OK';
	}

	function _log_result($log_id, $result)
	{
		$this->db->query("UPDATE payment_log SET result = '" . $result . "' WHERE id = " . $log_id);
	}
}

/* End of file payment.php */
/* Location: ./system/application/controllers/api/payment.php */
