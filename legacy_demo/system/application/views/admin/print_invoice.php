<?
// 金額轉中文大寫 (發票用)  網路上找的
function num2chinese($num)
{
	$digits = array('零', '壹', '貳', '參', '肆', '伍', '陸', '柒', '捌', '玖');
	$units  = array('', '拾', '佰', '仟', '萬', '拾', '佰', '仟', '億');
	$num = intval($num);
	if ($num == 0) return '零元整';

	$str = '';
	$s = strval($num);
	$len = strlen($s);
	$zero = FALSE;
	for ($i = 0; $i < $len; $i++)
	{
		$d = intval(substr($s, $i, 1));
		$u = $len - $i - 1;
		if ($d == 0)
		{
			$zero = TRUE;
			if ($u == 4) $str .= '萬';
		}
		else
		{
			if ($zero) $str .= '零';
			$str .= $digits[$d] . $units[$u];
			$zero = FALSE;
		}
	}
	return $str . '元整';
}

// 營業稅 5% (內含)
$sales = round($order['total'] / 1.05);
$tax   = $order['total'] - $sales;

// 發票日期用付款日，沒付款用今天；民國年
$t = $order['paid_at'] ? strtotime($order['paid_at']) : time();
$roc = date('Y', $t) - 1911;

// 發票號碼：字軌先寫死 AB，號碼用訂單 id 補 0 (財政部配號要記得改!!)
$invoice_no = 'AB-' . sprintf('%08d', $order['id']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>發票 <?=$invoice_no?></title>
<style type="text/css">
body, td { font-family: "新細明體", PMingLiU, serif; font-size: 13px; }
table.inv { border-collapse: collapse; }
table.inv td { border: 1px solid #cc0000; padding: 3px 5px; color: #000000; }
.red { color: #cc0000; }
@media print { .noprint { display: none; } }
</style>
</head>
<body bgcolor="#ffffff">
<div class="noprint" align="right"><input type="button" value="列印" onclick="window.print()"> <input type="button" value="關閉" onclick="window.close()"></div>

<table width="600" align="center" border="0" cellpadding="2" cellspacing="0">
	<tr><td align="center" class="red"><font size="5"><b><?=SHOP_NAME?></b></font><br><font size="4"><b>電子計算機統一發票</b></font>（二聯式）</td></tr>
	<tr><td align="center" class="red">中華民國 <?=$roc?> 年 <?=date('n', $t)?> 月 <?=date('j', $t)?> 日</td></tr>
	<tr><td align="right">發票號碼：<b><?=$invoice_no?></b></td></tr>
</table>

<table width="600" align="center" class="inv">
	<tr><td colspan="2" class="red">買受人</td><td colspan="3"><?=$order['receiver']?></td></tr>
	<tr><td colspan="2" class="red">統一編號</td><td colspan="3">　</td></tr>
	<tr><td colspan="2" class="red">地　　址</td><td colspan="3"><?=$order['zip']?> <?=$order['address']?></td></tr>
	<tr class="red" align="center"><td width="40">項次</td><td>品　　名</td><td width="60">數量</td><td width="80">單價</td><td width="100">金額</td></tr>
<? $i = 1; foreach ($items as $item) { ?>
	<tr>
		<td align="center"><?=$i++?></td>
		<td><?=$item['product_name']?></td>
		<td align="right"><?=$item['qty']?></td>
		<td align="right"><?=number_format($item['price'])?></td>
		<td align="right"><?=number_format($item['amount'])?></td>
	</tr>
<? } ?>
<? if ($order['discount'] + $order['promo_discount'] > 0) { ?>
	<tr><td align="center"><?=$i++?></td><td>折扣</td><td align="right">1</td><td align="right">-<?=number_format($order['discount'] + $order['promo_discount'])?></td><td align="right">-<?=number_format($order['discount'] + $order['promo_discount'])?></td></tr>
<? } ?>
<? if ($order['shipping'] > 0) { ?>
	<tr><td align="center"><?=$i++?></td><td>運費</td><td align="right">1</td><td align="right"><?=number_format($order['shipping'])?></td><td align="right"><?=number_format($order['shipping'])?></td></tr>
<? } ?>
	<tr><td colspan="4" align="right" class="red">銷售額合計</td><td align="right"><?=number_format($sales)?></td></tr>
	<tr><td colspan="4" align="right" class="red">營業稅（5%）</td><td align="right"><?=number_format($tax)?></td></tr>
	<tr><td colspan="4" align="right" class="red"><b>總　　計</b></td><td align="right"><b><?=number_format($order['total'])?></b></td></tr>
	<tr><td colspan="2" class="red">總計新臺幣<br>（中文大寫）</td><td colspan="3"><b><?=num2chinese($order['total'])?></b></td></tr>
</table>

<table width="600" align="center" border="0" cellpadding="2" cellspacing="0">
	<tr><td class="red"><br>賣方：<?=SHOP_NAME?>　統一編號：00000000（範例）<br>地址：範例市範例區範例路 1 號</td></tr>
	<tr><td align="center"><font size="2" color="#999999"><br>— 本畫面為列印預覽，所有資料皆為虛構 —</font></td></tr>
</table>
</body>
</html>
