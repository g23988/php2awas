<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?=SHOP_NAME?> 管理後台</title>
</head>
<body bgcolor="#f0f0f0">
<br><br><br>
<form method="post" action="<?=site_url('admin/login')?>">
<table align="center" width="320" border="0" cellpadding="6" cellspacing="0" bgcolor="#ffffff" style="border:1px solid #669933">
	<tr><td colspan="2" bgcolor="#669933"><font color="#ffffff"><b><?=SHOP_NAME?> 管理後台</b></font></td></tr>
	<? if ($error != '') { ?>
	<tr><td colspan="2"><font color="red"><?=$error?></font></td></tr>
	<? } ?>
	<tr><td width="80">帳號</td><td><input type="text" name="username" size="20"></td></tr>
	<tr><td>密碼</td><td><input type="password" name="password" size="20"></td></tr>
	<tr><td colspan="2" align="center"><input type="submit" value=" 登 入 "></td></tr>
</table>
</form>
</body>
</html>
