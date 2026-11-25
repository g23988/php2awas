<span class="pageTitle">訂單完成</span><br><br>
<? if ( ! $order) { ?>
<div class="err">查無此訂單</div>
<? } else { ?>
<div class="ok">
	<b>感謝您的訂購！您的訂單已成立。</b><br><br>
	訂單編號：<b><?=$order['order_no']?></b><br>
	商品小計：$<?=number_format($order['subtotal'])?><br>
<?	if ($order['discount'] > 0) { ?>　會員折扣：-$<?=number_format($order['discount'])?><br><? } ?>
<?	if ($order['promo_discount'] > 0) { ?>　活動折扣：-$<?=number_format($order['promo_discount'])?><br><? } ?>
	運　　費：$<?=number_format($order['shipping'])?><br>
	<b>應付金額：<span class="price">$<?=number_format($order['total'])?></span></b><br><br>
	請於 3 日內轉帳至：<b><?=ATM_ACCOUNT?></b><br>
	<span class="small gray">逾期未付款，訂單將自動取消。訂單確認信已寄到您的信箱。</span>
</div>
<br>
<a href="<?=site_url('shop')?>">&laquo; 回首頁繼續購物</a>
<? } ?>
