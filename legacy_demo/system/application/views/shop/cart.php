<?
// 會員價顯示 (VIP 85 折、金卡滿 3000 九折)
$level = $member ? $member['level'] : 0;
?>
<form name="cartForm" method="post" action="<?=site_url('shop/update')?>">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td valign="top">
			<span class="pageTitle">購物車</span>
<? if ( ! $member) { ?>
			<span class="small">已經是會員？<a href="<?=site_url('shop/login')?>">登入</a></span>
<? } ?>
			<br><br>
			<input type="button" class="updateBtn" value="繼續購物" onclick="location.href='<?=site_url('shop')?>'">
		</td>
		<td align="right" valign="top">
<? if (count($rows) > 0) { ?>
			<b>小計 = <span class="price">$<?=number_format($subtotal)?></span></b><br>
			<span class="small">要修改數量嗎？</span> <input type="submit" class="updateBtn" value="更新"><br><br>
			<input type="button" class="checkoutBtn" value="前往結帳 &raquo;" onclick="location.href='<?=site_url('shop/checkout')?>'">
<? } ?>
		</td>
	</tr>
</table>
<br>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="cartHead">
		<td colspan="2">購物車商品--立即購買</td>
		<td width="110">價格：</td>
		<td width="50">數量：</td>
	</tr>
<? if (count($rows) == 0) { ?>
	<tr class="cartRow"><td colspan="4" align="center"><br>您的購物車是空的。<a href="<?=site_url('shop')?>">去逛逛</a><br><br></td></tr>
<? } ?>
<? foreach ($rows as $r) { ?>
	<tr class="cartRow">
		<td class="leftCol">
			加入於<br><?=date('Y年n月j日', strtotime($r['added_at']))?><br>
			<input type="button" class="smallBtn" value="稍後購買" onclick="location.href='<?=site_url('shop/later/' . $r['id'])?>'"><br>
			<input type="button" class="smallBtn" value="刪除" onclick="location.href='<?=site_url('shop/remove/' . $r['id'])?>'">
		</td>
		<td>
			<a href="<?=site_url('shop')?>" class="itemTitle"><?=$r['name']?></a> - <?=SHOP_NAME?>；<b><?=$r['sku']?></b><br>
			<span class="small">狀態：新品</span><br>
<?	if ($r['stock'] >= $r['qty']) { ?>
			<span class="instock">有現貨</span><? if ($r['stock'] < 10) echo ' <span class="lowstock small">（只剩 ' . $r['stock'] . ' 件）</span>'; ?>
<?	} else { ?>
			<span class="lowstock">庫存不足（剩 <?=$r['stock']?> 件）</span>
<?	} ?>
			<br><br>
<?	if ($subtotal >= 1000) { ?>
			<span class="small green">&#9992; 本訂單符合<b>免運費</b>資格</span><br>
<?	} ?>
			<input type="checkbox" disabled> <span class="small" style="color:#cc6600"><b>加購禮盒包裝／賀卡</b></span> <span class="tiny">(<a href="javascript:alert('即將開放')">了解更多</a>)</span>
		</td>
		<td>
			<span class="price">$<?=number_format($r['amount'])?></span><br>
<?	if ($level == 3) { ?>
			<span class="save">VIP 可省：$<?=number_format(round($r['amount'] * 0.15))?> (15%)</span>
<?	} elseif ($level == 2) { ?>
			<span class="save tiny">金卡滿 $3,000 享 9 折</span>
<?	} ?>
		</td>
		<td><input type="text" name="qty[<?=$r['id']?>]" value="<?=$r['qty']?>" size="2" maxlength="2"></td>
	</tr>
<? } ?>
</table>
</form>

<? if (count($saved) > 0) { ?>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="cartHead"><td colspan="3">稍後購買的商品（這些商品不在結帳範圍內）</td></tr>
<?	foreach ($saved as $r) { ?>
	<tr class="cartRow">
		<td class="leftCol">加入於<br><?=date('Y年n月j日', strtotime($r['added_at']))?></td>
		<td><a href="<?=site_url('shop')?>" class="itemTitle"><?=$r['name']?></a><br>
			<input type="button" class="smallBtn" value="移回購物車" onclick="location.href='<?=site_url('shop/move/' . $r['id'])?>'"></td>
		<td width="160"><span class="price">$<?=number_format($r['price'])?></span></td>
	</tr>
<?	} ?>
</table>
<? } ?>
