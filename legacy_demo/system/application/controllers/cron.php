<?php
/**
 * 排程
 * crontab: 0 3 * * * wget -q -O /dev/null http://www.shop.example.com/index.php/cron/auto_cancel
 *
 * 用 CI 的 db 跑大量資料會 timeout，這支直接連資料庫
 *
 * @author	Kevin
 * @since	2010/02/20
 */
class Cron extends Controller {

	function Cron()
	{
		parent::Controller();
	}

	/**
	 * 超過 3 天沒付款的訂單自動取消，並把庫存加回去
	 */
	function auto_cancel()
	{
		set_time_limit(0);

		$link = @mysql_connect('db01.shop.local', 'root', 'shop@1234');
		mysql_select_db('shopdb', $link);
		mysql_query("SET NAMES utf8", $link);

		$limit = date('Y-m-d H:i:s', time() - 3 * 86400);

		$rs = mysql_query("SELECT id, order_no FROM orders WHERE status = 0 AND created_at < '" . $limit . "'", $link);

		$count = 0;
		while ($row = mysql_fetch_assoc($rs))
		{
			$items = mysql_query("SELECT product_id, qty FROM order_item WHERE order_id = " . $row['id'], $link);
			while ($item = mysql_fetch_assoc($items))
			{
				mysql_query("UPDATE product SET stock = stock + " . $item['qty'] . " WHERE id = " . $item['product_id'], $link);
			}

			mysql_query("UPDATE orders SET status = 9, canceled_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'], $link);
			$count++;
		}

		mysql_close($link);

		echo 'auto_cancel done: ' . $count;
	}
}

/* End of file cron.php */
/* Location: ./system/application/controllers/cron.php */
