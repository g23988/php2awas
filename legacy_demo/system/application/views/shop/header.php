<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
<meta name="keywords" content="有機米,高山茶,黑糖,苦茶油,產地直送,網路購物">
<meta name="description" content="<?=SHOP_NAME?> - 產地直送．新鮮到家">
<title><?=SHOP_NAME?> - <?=$title?></title>
<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/shop.css">
<script language="javascript" src="<?=base_url()?>js/general.js"></script>
</head>
<body>
<table width="780" align="center" border="0" cellspacing="0" cellpadding="4" class="topBar">
	<tr>
		<td><a href="<?=site_url('shop')?>" style="text-decoration:none"><span class="logo"><?=SHOP_NAME?></span></a><br><span class="slogan">產地直送．新鮮到家　since 2009</span></td>
		<td align="right" valign="bottom" class="topLinks">
<? if ($member) { ?>
			<?=$member['name']?> 您好！<? if ($member['level'] == 3) echo '<font color="#cc0000"><b>[VIP]</b></font>'; elseif ($member['level'] == 2) echo '<font color="#cc9900"><b>[金卡]</b></font>'; ?>
			| <a href="<?=site_url('shop/logout')?>">登出</a>
<? } else { ?>
			<a href="<?=site_url('shop/login')?>">會員登入</a>
<? } ?>
			| <a href="<?=site_url('shop/cart')?>"><b>購物車 (<?=$cart_count?>)</b></a>
			| <a href="mailto:<?=SHOP_MAIL?>">客服中心</a>
		</td>
	</tr>
</table>
<table width="780" align="center" border="0" cellspacing="0" cellpadding="5" class="navBar">
	<tr><td>
		<a href="<?=site_url('shop')?>">首頁</a>|<a href="<?=site_url('shop')?>">全部商品</a>|<a href="<?=site_url('shop')?>">本月特惠</a>|<a href="<?=site_url('shop/cart')?>">購物車</a>
	</td></tr>
</table>
<table width="780" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr><td><img src="<?=base_url()?>images/pixel_trans.gif" width="1" height="10" alt=""></td></tr>
	<tr><td>
