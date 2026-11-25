<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 鮮農直送網 共用函式
 *
 * @author	Kevin
 * @since	2009/10/05
 */

// ------------------------------------------------------------------------

/**
 * 取得現在時間（測試時可以用 debug_now 指定）
 */
if ( ! function_exists('shop_now'))
{
	function shop_now()
	{
		if (DEBUG_MODE && isset($_POST['debug_now']) && $_POST['debug_now'] != '')
		{
			return strtotime($_POST['debug_now']);
		}
		return time();
	}
}

// ------------------------------------------------------------------------

/**
 * 會員等級折扣
 * 回傳「折掉的金額」
 *
 * 2010/03/12 Kevin: 金卡要滿 3000 才打折 (業務部 Amy 確認)
 */
if ( ! function_exists('get_member_discount'))
{
	function get_member_discount($level, $subtotal)
	{
		if ($level == 3)
		{
			$pay = intval($subtotal * 0.85);
			return $subtotal - $pay;
		}

		if ($level == 2 && $subtotal >= 3000)
		{
			$pay = intval($subtotal * 0.9);
			return $subtotal - $pay;
		}

		return 0;
	}
}

// ------------------------------------------------------------------------

/**
 * 是否為離島 (澎湖、金門、馬祖)
 */
if ( ! function_exists('is_island'))
{
	function is_island($zip)
	{
		$zip3 = intval(substr(trim($zip), 0, 3));

		// 澎湖 880~885
		if ($zip3 >= 880 && $zip3 <= 885) return TRUE;
		// 金門 890~896
		if ($zip3 >= 890 && $zip3 <= 896) return TRUE;
		// 馬祖 209~212
		if ($zip3 >= 209 && $zip3 <= 212) return TRUE;

		return FALSE;
	}
}

// ------------------------------------------------------------------------

/**
 * 計算運費
 *
 * 2011/01/03 黑貓調漲 本島運費 60 -> 80
 */
if ( ! function_exists('calc_shipping'))
{
	function calc_shipping($zip, $subtotal)
	{
		if (is_island($zip))
		{
			return 250;
		}

		if ($subtotal >= 1000)
		{
			return 0;
		}

		return 80;
	}
}

// ------------------------------------------------------------------------

/**
 * 產生訂單編號  yyyymmdd + 4碼亂數
 */
if ( ! function_exists('make_order_no'))
{
	function make_order_no($now)
	{
		return date('Ymd', $now) . sprintf('%04d', mt_rand(1, 9999));
	}
}

// ------------------------------------------------------------------------

/**
 * 訂單狀態文字
 */
if ( ! function_exists('order_status_text'))
{
	function order_status_text($status)
	{
		switch ($status)
		{
			case 0: return '待付款';
			case 1: return '已付款';
			case 2: return '已出貨';
			case 9: return '已取消';
		}
		return '未知';
	}
}

// ------------------------------------------------------------------------

/**
 * 會員等級文字
 */
if ( ! function_exists('member_level_text'))
{
	function member_level_text($level)
	{
		$arr = array(1 => '一般會員', 2 => '金卡會員', 3 => 'VIP');
		return isset($arr[$level]) ? $arr[$level] : '一般會員';
	}
}

/* End of file shop_helper.php */
/* Location: ./system/application/helpers/shop_helper.php */
