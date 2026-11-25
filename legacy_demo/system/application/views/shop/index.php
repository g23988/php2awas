<? if ($msg) { ?><div class="ok"><?=$msg?></div><br><? } ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="boxHead">全部商品</td>
		<td class="boxHead" align="right"><span class="small">
<? if (date('n') == 11) { ?>
			<font color="#cc0000">★ 11 月週年慶：全館滿千折百！</font>
<? } else { ?>
			<font color="#cc0000">★ 全館滿 $1,000 免運費（離島除外）</font>
<? } ?>
		</span></td>
	</tr>
</table>
<br>
<table width="100%" border="0" cellspacing="8" cellpadding="0">
<? $i = 0; foreach ($products as $p) { ?>
<?	if ($i % 3 == 0) echo "\t<tr>\n"; ?>
		<td width="33%" valign="top" class="prodBox">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr><td align="center">
					<table class="noimg" border="0" cellspacing="0" cellpadding="0"><tr><td align="center">暫無圖片<br><?=$p['sku']?></td></tr></table>
				</td></tr>
				<tr><td><br><a href="<?=site_url('shop/add/' . $p['id'])?>"><b><?=$p['name']?></b></a></td></tr>
				<tr><td>售價：<span class="price">$<?=number_format($p['price'])?></span></td></tr>
				<tr><td class="small">
<?	if ($p['stock'] <= 0) { ?><span class="gray">已售完</span>
<?	} elseif ($p['stock'] < 10) { ?><span class="lowstock">只剩 <?=$p['stock']?> 件，欲購從速！</span>
<?	} else { ?><span class="instock">有現貨</span>
<?	} ?>
<?	if ($p['id'] == 10) echo '<br><span class="gray">（每筆限購 1 包）</span>'; ?>
				</td></tr>
				<tr><td align="right"><br>
<?	if ($p['stock'] > 0) { ?>
					<input type="button" class="buyBtn" value="放入購物車" onclick="location.href='<?=site_url('shop/add/' . $p['id'])?>'">
<?	} ?>
				</td></tr>
			</table>
		</td>
<?	$i++; if ($i % 3 == 0) echo "\t</tr>\n"; ?>
<? } ?>
<? if ($i % 3 != 0) echo "\t</tr>\n"; ?>
</table>
