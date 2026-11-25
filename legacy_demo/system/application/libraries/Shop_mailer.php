<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 寄信用
 * CI 的 Email library 在主機上一直寄不出去，所以自己包 mail()
 *
 * @author	Kevin
 */
class Shop_mailer {

	var $from      = SHOP_MAIL;
	var $from_name = SHOP_NAME;

	function Shop_mailer()
	{
	}

	function send($to, $subject, $body)
	{
		$headers  = "From: =?UTF-8?B?" . base64_encode($this->from_name) . "?= <" . $this->from . ">\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
		$headers .= "Content-Transfer-Encoding: 8bit\r\n";

		$subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";

		@mail($to, $subject, $body, $headers);
	}

	function order_created($member, $order_no, $total)
	{
		$body  = $member['name'] . " 您好：\n\n";
		$body .= "感謝您在" . SHOP_NAME . "訂購，您的訂單已成立。\n\n";
		$body .= "訂單編號：" . $order_no . "\n";
		$body .= "應付金額：" . $total . " 元\n\n";
		$body .= "請於 3 日內完成付款，逾期訂單將自動取消。\n";

		$this->send($member['email'], '[' . SHOP_NAME . '] 訂單成立通知 ' . $order_no, $body);
	}

	function order_paid($member, $order, $point)
	{
		$body  = $member['name'] . " 您好：\n\n";
		$body .= "我們已收到您的付款。\n\n";
		$body .= "訂單編號：" . $order['order_no'] . "\n";
		$body .= "付款金額：" . $order['total'] . " 元\n";
		$body .= "本次獲得紅利點數：" . $point . " 點\n";

		$this->send($member['email'], '[' . SHOP_NAME . '] 付款完成通知 ' . $order['order_no'], $body);
	}

	function order_shipped($member, $order)
	{
		$body  = $member['name'] . " 您好：\n\n";
		$body .= "您的訂單 " . $order['order_no'] . " 已經出貨，請留意收件。\n";

		$this->send($member['email'], '[' . SHOP_NAME . '] 出貨通知 ' . $order['order_no'], $body);
	}
}

/* End of file Shop_mailer.php */
/* Location: ./system/application/libraries/Shop_mailer.php */
