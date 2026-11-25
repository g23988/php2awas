<span class="pageTitle">會員登入</span><br><br>
<? if ($error != '') { ?><div class="err"><?=$error?></div><br><? } ?>
<form method="post" action="<?=site_url('shop/login')?>">
<table border="0" cellspacing="0" cellpadding="6" style="border:1px solid #cccccc" width="400">
	<tr><td colspan="2" class="boxHead">已經是會員</td></tr>
	<tr><td width="90" align="right">Email：</td><td><input type="text" name="email" size="30"></td></tr>
	<tr><td align="right">密碼：</td><td><input type="password" name="password" size="20"></td></tr>
	<tr><td></td><td><input type="submit" class="buyBtn" value=" 登入 "> <span class="tiny"><a href="javascript:alert('請來電客服重設密碼')">忘記密碼？</a></span></td></tr>
</table>
</form>
<br>
<span class="small gray">還不是會員？請來電 (02) 0000-0000 由客服為您建立帳號。</span>
