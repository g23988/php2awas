<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr><td class="pageHeading"><?=$title?> - <?=$order['order_no']?></td></tr>
	<tr><td><img src="<?=base_url()?>images/pixel_trans.gif" width="1" height="10" alt=""></td></tr>
	<tr>
		<td>
			<form method="post" action="<?=site_url('admin/orders/edit/' . $order['id'])?>">
			<table border="0" cellspacing="1" cellpadding="2" bgcolor="#c9c9c9">
				<tr><td class="fieldKey">訂單序號</td><td class="fieldValue" bgcolor="#ffffff"><?=$order['order_no']?></td></tr>
				<tr><td class="fieldKey">收件人</td><td class="fieldValue" bgcolor="#ffffff"><input type="text" name="receiver" value="<?=$order['receiver']?>" size="20"></td></tr>
				<tr><td class="fieldKey">電話</td><td class="fieldValue" bgcolor="#ffffff"><input type="text" name="phone" value="<?=$order['phone']?>" size="20"></td></tr>
				<tr><td class="fieldKey">郵遞區號</td><td class="fieldValue" bgcolor="#ffffff"><input type="text" name="zip" value="<?=$order['zip']?>" size="5" maxlength="5"></td></tr>
				<tr><td class="fieldKey">地址</td><td class="fieldValue" bgcolor="#ffffff"><input type="text" name="address" value="<?=$order['address']?>" size="50"></td></tr>
				<tr><td class="fieldKey">備註</td><td class="fieldValue" bgcolor="#ffffff"><textarea name="note" cols="50" rows="3"><?=$order['note']?></textarea></td></tr>
				<tr>
					<td class="fieldKey">狀態</td>
					<td class="fieldValue" bgcolor="#ffffff">
						<select name="status">
<? foreach (array(0, 1, 2, 9) as $s) { ?>
							<option value="<?=$s?>"<? if ($order['status'] == $s) echo ' selected'; ?>><?=order_status_text($s)?></option>
<? } ?>
						</select>
					</td>
				</tr>
				<tr><td class="fieldKey">訂單總額</td><td class="fieldValue" bgcolor="#ffffff">$<?=number_format($order['total'])?> <span class="smallText">(含運費 $<?=$order['shipping']?>)</span></td></tr>
			</table>
			<br>
			<input type="submit" value=" 儲存 " class="btn">
			<input type="button" value=" 取消 " class="btn" onclick="history.back()">
			</form>
		</td>
	</tr>
</table>
