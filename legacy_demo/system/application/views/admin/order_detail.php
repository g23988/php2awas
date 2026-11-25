<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr><td class="pageHeading"><?=$title?></td></tr>
</table>
<img src="<?=base_url()?>images/pixel_trans.gif" width="1" height="10" alt=""><br>
<table border="0" cellspacing="0" cellpadding="2" class="list" width="600">
	<tr><th width="120" align="left">訂單編號</th><td><?=$order['order_no']?></td></tr>
	<tr><th align="left">狀態</th><td><?=order_status_text($order['status'])?></td></tr>
	<tr><th align="left">會員</th><td><?=$member['name']?> (<?=member_level_text($member['level'])?>) <?=$member['email']?></td></tr>
	<tr><th align="left">收件人</th><td><?=$order['receiver']?> / <?=$order['phone']?></td></tr>
	<tr><th align="left">地址</th><td><?=$order['zip']?> <?=$order['address']?><? if (is_island($order['zip'])) echo ' <font color="blue">(離島)</font>'; ?></td></tr>
	<tr><th align="left">備註</th><td><?=nl2br($order['note'])?></td></tr>
	<tr><th align="left">下單時間</th><td><?=$order['created_at']?></td></tr>
	<tr><th align="left">付款時間</th><td><?=$order['paid_at']?></td></tr>
	<tr><th align="left">出貨時間</th><td><?=$order['shipped_at']?></td></tr>
</table>
<br>

<table border="0" cellspacing="0" cellpadding="2" class="list" width="600">
	<tr><th>商品</th><th>單價</th><th>數量</th><th>小計</th></tr>
<? foreach ($items as $item) { ?>
	<tr>
		<td><?=$item['product_name']?></td>
		<td align="right"><?=number_format($item['price'])?></td>
		<td align="right"><?=$item['qty']?></td>
		<td align="right"><?=number_format($item['amount'])?></td>
	</tr>
<? } ?>
	<tr><td colspan="3" align="right">商品小計</td><td align="right"><?=number_format($order['subtotal'])?></td></tr>
	<tr><td colspan="3" align="right">會員折扣</td><td align="right">-<?=number_format($order['discount'])?></td></tr>
	<tr><td colspan="3" align="right">活動折扣</td><td align="right">-<?=number_format($order['promo_discount'])?></td></tr>
	<tr><td colspan="3" align="right">運費</td><td align="right"><?=number_format($order['shipping'])?></td></tr>
	<tr><td colspan="3" align="right"><b>總計</b></td><td align="right"><b><?=number_format($order['total'])?></b></td></tr>
</table>
<br>

<? if ($order['status'] == 1) { ?>
	<a href="<?=site_url('admin/orders/ship/' . $order['id'])?>" onclick="return confirmShip();"><b>[ 出貨 ]</b></a>
<? } ?>
&nbsp; <a href="<?=site_url('admin/orders')?>">回列表</a>
