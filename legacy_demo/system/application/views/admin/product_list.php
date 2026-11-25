<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr><td class="pageHeading"><?=$title?></td></tr>
</table>
<img src="<?=base_url()?>images/pixel_trans.gif" width="1" height="10" alt=""><br>
<? if ($msg) { ?><p class="msg"><?=$msg?></p><? } ?>

<table border="0" cellspacing="0" cellpadding="2" class="list">
	<tr><th>ID</th><th>料號</th><th>商品名稱</th><th>售價</th><th>庫存</th><th>狀態</th><th>修改庫存</th></tr>
<? foreach ($products as $p) { ?>
	<tr<? if ($p['stock'] < 10) echo ' bgcolor="#ffdddd"'; ?>>
		<td><?=$p['id']?></td>
		<td><?=$p['sku']?></td>
		<td><?=$p['name']?></td>
		<td align="right"><?=number_format($p['price'])?></td>
		<td align="right"><?=$p['stock']?></td>
		<td><?=($p['status'] == 1 ? '上架' : '<font color="#999999">下架</font>')?></td>
		<td>
			<form method="post" action="<?=site_url('admin/products/update')?>" style="margin:0">
				<input type="hidden" name="id" value="<?=$p['id']?>">
				<input type="text" name="stock" value="<?=$p['stock']?>" size="5">
				<input type="submit" value="更新">
			</form>
		</td>
	</tr>
<? } ?>
</table>
<p><font color="#cc0000">* 庫存低於 10 以紅色標示</font></p>
