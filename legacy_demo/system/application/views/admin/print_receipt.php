<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>出貨單 <?=$order['order_no']?></title>
<style type="text/css">
body, td { font-family: "新細明體", PMingLiU, serif; font-size: 13px; }
.title { font-size: 22px; font-weight: bold; letter-spacing: 8px; }
table.items { border-collapse: collapse; }
table.items td { border: 1px solid #000000; padding: 3px 5px; }
@media print { .noprint { display: none; } }
</style>
</head>
<body bgcolor="#ffffff">
<div class="noprint" align="right"><input type="button" value="列印" onclick="window.print()"> <input type="button" value="關閉" onclick="window.close()"></div>

<table width="620" align="center" border="0" cellpadding="2" cellspacing="0">
	<tr><td align="center" colspan="2"><font size="4"><b><?=SHOP_NAME?></b></font><br><span class="title">出貨單／收據</span><br><br></td></tr>
	<tr>
		<td width="50%">訂單編號：<?=$order['order_no']?></td>
		<td align="right">訂購日期：<?=substr($order['created_at'], 0, 10)?></td>
	</tr>
	<tr>
		<td>訂購人：<?=$member['name']?> (<?=member_level_text($member['level'])?>)</td>
		<td align="right">付款日期：<?=$order['paid_at'] ? substr($order['paid_at'], 0, 10) : '<font color="red">尚未付款</font>'?></td>
	</tr>
	<tr><td colspan="2">收件人：<?=$order['receiver']?>　電話：<?=$order['phone']?></td></tr>
	<tr><td colspan="2">收件地址：<?=$order['zip']?> <?=$order['address']?><? if (is_island($order['zip'])) echo '（離島）'; ?><br><br></td></tr>
</table>

<table width="620" align="center" class="items">
	<tr bgcolor="#eeeeee"><td align="center">項次</td><td align="center">品名</td><td align="center">數量</td><td align="center">單價</td><td align="center">金額</td></tr>
<? $i = 1; foreach ($items as $item) { ?>
	<tr>
		<td align="center"><?=$i++?></td>
		<td><?=$item['product_name']?></td>
		<td align="right"><?=$item['qty']?></td>
		<td align="right"><?=number_format($item['price'])?></td>
		<td align="right"><?=number_format($item['amount'])?></td>
	</tr>
<? } ?>
	<tr><td colspan="4" align="right">商品小計</td><td align="right"><?=number_format($order['subtotal'])?></td></tr>
	<tr><td colspan="4" align="right">會員折扣</td><td align="right">-<?=number_format($order['discount'])?></td></tr>
<? if ($order['promo_discount'] > 0) { ?>
	<tr><td colspan="4" align="right">活動折扣</td><td align="right">-<?=number_format($order['promo_discount'])?></td></tr>
<? } ?>
	<tr><td colspan="4" align="right">運費</td><td align="right"><?=number_format($order['shipping'])?></td></tr>
	<tr><td colspan="4" align="right"><b>合計</b></td><td align="right"><b><?=number_format($order['total'])?></b></td></tr>
</table>

<table width="620" align="center" border="0" cellpadding="2" cellspacing="0">
	<tr><td><br>備註：<?=nl2br($order['note'])?></td></tr>
	<tr>
		<td><br><br>
			<table width="100%" border="0"><tr>
				<td width="50%">出貨人員：＿＿＿＿＿＿＿＿</td>
				<td>收件人簽收：＿＿＿＿＿＿＿＿</td>
			</tr></table>
			<br>
			<font size="2" color="#666666">※ 商品到貨 7 日內享有鑑賞期，如有問題請洽客服 <?=SHOP_MAIL?></font>
		</td>
	</tr>
</table>
</body>
</html>
