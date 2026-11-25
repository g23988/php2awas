<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
<title><?=SHOP_NAME?> 管理後台 - <?=$title?></title>
<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/admin.css">
<script language="javascript" src="<?=base_url()?>js/general.js"></script>
<script language="javascript">
function confirmShip() {
	return confirm('確定要出貨嗎？');
}
</script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">

<!-- header //-->
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td class="headerLogo"><?=SHOP_NAME?> <small>Administration</small></td>
		<td class="headerLogo" align="right" valign="bottom"><small>您好，<?=$this->session->userdata('admin_name')?>&nbsp;&nbsp;</small></td>
	</tr>
</table>
<table border="0" width="100%" cellspacing="0" cellpadding="2" class="headerBar">
	<tr>
		<td class="headerBarContent">&nbsp;&nbsp;<a href="<?=site_url('admin/orders')?>" class="headerLink">管理首頁</a> &nbsp;|&nbsp; <a href="<?=site_url('shop')?>" class="headerLink" target="_blank">商店首頁</a></td>
		<td class="headerBarContent" align="right"><?=date('Y/m/d H:i')?> &nbsp;|&nbsp; <a href="<?=site_url('admin/login/logout')?>" class="headerLink">登出</a>&nbsp;&nbsp;</td>
	</tr>
</table>
<!-- header_eof //-->

<table border="0" width="100%" cellspacing="2" cellpadding="2">
	<tr>
		<td width="160" valign="top" class="columnLeft">
<!-- left_navigation //-->
			<img src="<?=base_url()?>images/pixel_trans.gif" width="150" height="1" alt=""><br>
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr><td class="menuBoxHeading">商品目錄</td></tr>
				<tr><td class="menuBoxContent">
					<a href="<?=site_url('admin/products')?>" class="menuBoxContentLink">商品／庫存</a><br>
				</td></tr>
				<tr><td class="menuBoxHeading">客戶</td></tr>
				<tr><td class="menuBoxContent">
					<a href="<?=site_url('admin/customers')?>" class="menuBoxContentLink">客戶管理</a><br>
				</td></tr>
				<tr><td class="menuBoxHeading">訂單</td></tr>
				<tr><td class="menuBoxContent">
					<a href="<?=site_url('admin/orders')?>" class="menuBoxContentLink">訂單管理</a><br>
					<a href="<?=site_url('admin/phone_order')?>" class="menuBoxContentLink">電話訂購 <b>NEW</b></a><br>
				</td></tr>
				<tr><td class="menuBoxHeading">報表</td></tr>
				<tr><td class="menuBoxContent">
					<a href="<?=site_url('admin/report')?>" class="menuBoxContentLink">每日營業報表</a><br>
				</td></tr>
			</table>
<!-- left_navigation_eof //-->
		</td>
		<td width="100%" valign="top">
<!-- body_text //-->
