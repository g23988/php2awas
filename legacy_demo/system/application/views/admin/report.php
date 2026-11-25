<?
// 每日業績目標 (2011 年度)
$target = 50000;

$sum_total    = 0;
$sum_shipping = 0;
$sum_notax    = 0;
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr><td class="pageHeading"><?=$title?></td></tr>
</table>
<img src="<?=base_url()?>images/pixel_trans.gif" width="1" height="10" alt=""><br>
<form method="get" onsubmit="location.href='<?=site_url('admin/report/daily')?>/' + this.d.value; return false;">
	日期：<input type="text" name="d" value="<?=$date?>" size="12"> <input type="submit" value="查詢">
</form>
<br>

<table class="list" width="700">
	<tr><th>訂單編號</th><th>付款時間</th><th>訂單金額</th><th>運費</th><th>業績(不含運費)</th><th>未稅金額</th></tr>
<? foreach ($orders as $o) { ?>
<?
	$sales = $o['total'] - $o['shipping'];
	$notax = round($sales / 1.05);

	$sum_total    += $o['total'];
	$sum_shipping += $o['shipping'];
	$sum_notax    += $notax;
?>
	<tr>
		<td><?=$o['order_no']?></td>
		<td><?=$o['paid_at']?></td>
		<td align="right"><?=number_format($o['total'])?></td>
		<td align="right"><?=number_format($o['shipping'])?></td>
		<td align="right"><?=number_format($sales)?></td>
		<td align="right"><?=number_format($notax)?></td>
	</tr>
<? } ?>
<?
	$sum_sales = $sum_total - $sum_shipping;
	$rate = $target > 0 ? round($sum_sales / $target * 100, 1) : 0;
?>
	<tr bgcolor="#eeeeee">
		<td colspan="2" align="right"><b>合計 (<?=count($orders)?> 筆)</b></td>
		<td align="right"><b><?=number_format($sum_total)?></b></td>
		<td align="right"><b><?=number_format($sum_shipping)?></b></td>
		<td align="right"><b><?=number_format($sum_sales)?></b></td>
		<td align="right"><b><?=number_format($sum_notax)?></b></td>
	</tr>
</table>
<br>
業績目標：<?=number_format($target)?> 元 &nbsp;&nbsp; 達成率：<b><?=$rate?>%</b>
<? if ($rate >= 100) { ?> <font color="red"><b>★ 達標！</b></font><? } ?>
