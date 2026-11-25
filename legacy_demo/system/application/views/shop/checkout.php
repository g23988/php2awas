<span class="pageTitle">結帳</span><br><br>
<? if ($error) { ?><div class="err">訂單建立失敗：<?=$error?></div><br><? } ?>
<form method="post" action="<?=site_url('shop/submit')?>" onsubmit="this.submitBtn.disabled=true; this.submitBtn.value='處理中，請勿重複點選...'; return true;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="55%" valign="top">
			<table width="100%" border="0" cellspacing="0" cellpadding="5" style="border:1px solid #cccccc">
				<tr><td colspan="2" class="boxHead">1. 收件資料</td></tr>
				<tr><td width="80" align="right">收件人：</td><td><input type="text" name="receiver" value="<?=$member['name']?>" size="20"></td></tr>
				<tr><td align="right">電話：</td><td><input type="text" name="phone" value="<?=$member['phone']?>" size="20"></td></tr>
				<tr><td align="right">郵遞區號：</td><td><input type="text" name="zip" value="<?=$member['zip']?>" size="5" maxlength="5"></td></tr>
				<tr><td align="right">地址：</td><td><input type="text" name="address" value="<?=$member['address']?>" size="40"></td></tr>
				<tr><td align="right" valign="top">備註：</td><td><textarea name="note" cols="38" rows="3"></textarea></td></tr>
				<tr><td colspan="2" class="boxHead">2. 付款方式</td></tr>
				<tr><td></td><td><input type="radio" checked> ATM 轉帳（訂單成立後 3 日內完成匯款）</td></tr>
			</table>
		</td>
		<td width="10"></td>
		<td valign="top">
			<table width="100%" border="0" cellspacing="0" cellpadding="5" style="border:1px solid #cccccc">
				<tr><td colspan="2" class="boxHead">訂單內容</td></tr>
<? foreach ($rows as $r) { ?>
				<tr><td class="small"><?=$r['name']?> x <?=$r['qty']?></td><td align="right" class="small">$<?=number_format($r['amount'])?></td></tr>
<? } ?>
				<tr><td><b>商品小計</b></td><td align="right"><span class="price">$<?=number_format($subtotal)?></span></td></tr>
				<tr><td colspan="2" class="tiny gray">※ 會員折扣、活動折扣與運費將於訂單成立時計算</td></tr>
			</table>
			<br>
			<div align="right"><input type="submit" name="submitBtn" class="checkoutBtn" value="確認送出訂單"></div>
		</td>
	</tr>
</table>
</form>
