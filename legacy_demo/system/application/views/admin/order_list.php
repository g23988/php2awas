<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td class="pageHeading"><?=$title?></td>
					<td align="right">
						<form method="post" action="<?=site_url('admin/orders/index/' . $status)?>">
						<table border="0" cellspacing="0" cellpadding="1">
							<tr>
								<td class="smallText" align="right">訂單序號：</td>
								<td><input type="text" name="keyword" value="<?=$keyword?>" size="16"> <input type="submit" value="搜尋" class="btn"></td>
							</tr>
							<tr>
								<td class="smallText" align="right">狀態：</td>
								<td>
									<select name="status" onchange="goStatus(this, '<?=site_url('admin/orders/index')?>/')">
										<option value="all"<? if ($status == 'all') echo ' selected'; ?>>全部訂單</option>
										<option value="0"<? if ($status == '0') echo ' selected'; ?>>待付款</option>
										<option value="1"<? if ($status == '1') echo ' selected'; ?>>已付款</option>
										<option value="2"<? if ($status == '2') echo ' selected'; ?>>已出貨</option>
										<option value="9"<? if ($status == '9') echo ' selected'; ?>>已取消</option>
									</select>
								</td>
							</tr>
						</table>
						</form>
						<span class="smallText"><font color="#999999">(可輸入訂單序號、收件人或電話)</font></span>
					</td>
				</tr>
			</table>
		</td>
	</tr>
<? if ($msg) { ?>
	<tr><td class="messageStackSuccess"><?=$msg?></td></tr>
<? } ?>
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td valign="top">
						<table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr class="dataTableHeadingRow">
								<td class="dataTableHeadingContent">訂單序號</td>
								<td class="dataTableHeadingContent">客戶</td>
								<td class="dataTableHeadingContent">收件人</td>
								<td class="dataTableHeadingContent" align="right">訂單總額</td>
								<td class="dataTableHeadingContent" align="center">下單時間</td>
								<td class="dataTableHeadingContent" align="center">狀態</td>
								<td class="dataTableHeadingContent">備註</td>
								<td class="dataTableHeadingContent" align="right">動作&nbsp;</td>
							</tr>
<? if (count($orders) == 0) { ?>
							<tr class="dataTableRow"><td class="dataTableContent" colspan="8" align="center"><br>查無資料<br><br></td></tr>
<? } ?>
<? foreach ($orders as $o) { ?>
<?	if ($sel && $sel['id'] == $o['id']) { ?>
							<tr class="dataTableRowSelected" onclick="document.location.href='<?=site_url('admin/orders/detail/' . $o['id'])?>'">
<?	} else { ?>
							<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?=site_url('admin/orders/index/' . $status . '/' . $o['id'])?>'">
<?	} ?>
								<td class="dataTableContent"><?=$o['order_no']?></td>
								<td class="dataTableContent"><?=$o['member_name']?><? if ($o['member_level'] == 3) echo ' <font color="red"><b>VIP</b></font>'; ?></td>
								<td class="dataTableContent"><?=$o['receiver']?></td>
								<td class="dataTableContent" align="right"><? if ($o['total'] >= 3000) { ?><b>$<?=number_format($o['total'])?></b><? } else { ?>$<?=number_format($o['total'])?><? } ?></td>
								<td class="dataTableContent" align="center"><?=substr($o['created_at'], 0, 16)?></td>
								<td class="dataTableContent" align="center">
<?	if ($o['status'] == 0) { ?><font color="#ff6600">等待處理</font>
<?	} elseif ($o['status'] == 9) { ?><font color="#999999"><?=order_status_text($o['status'])?></font>
<?	} else { echo order_status_text($o['status']); } ?>
								</td>
								<td class="dataTableContent" style="white-space:normal"><?=$o['note']?></td>
								<td class="dataTableContent" align="right"><? if ($sel && $sel['id'] == $o['id']) { ?><font color="#336633"><b>&#9658;</b></font><? } else { ?><font color="#7187bb">[i]</font><? } ?>&nbsp;</td>
							</tr>
<? } ?>
							<tr>
								<td colspan="8" class="smallText">共 <b><?=count($orders)?></b> 筆訂單 (最多顯示 50 筆)</td>
							</tr>
						</table>
					</td>
<? if ($sel) { ?>
					<td width="25%" valign="top">
						<table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr class="infoBoxHeading"><td class="infoBoxHeading"><b>[<?=$sel['order_no']?>]&nbsp;&nbsp;<?=substr($sel['created_at'], 0, 10)?></b></td></tr>
						</table>
						<table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr><td class="infoBoxContent" align="center">
								<a href="<?=site_url('admin/orders/edit/' . $sel['id'])?>" class="btnLink">編輯</a>
								<a href="javascript:confirmDelete('<?=site_url('admin/orders/delete/' . $sel['id'])?>')" class="btnLink">刪除</a>
							</td></tr>
							<tr><td class="infoBoxContent" align="center">
								<a href="javascript:popupWindow('<?=site_url('admin/orders/receipt/' . $sel['id'])?>', 'receipt', 680, 600)" class="btnLink">收據列印</a>
								<a href="javascript:popupWindow('<?=site_url('admin/orders/invoice/' . $sel['id'])?>', 'invoice', 680, 600)" class="btnLink">發票列印</a>
							</td></tr>
							<tr><td class="infoBoxContent" align="center">
								<a href="<?=site_url('admin/orders/detail/' . $sel['id'])?>" class="btnLink">訂單明細</a>
<?	if ($sel['status'] == 1) { ?>
								<a href="<?=site_url('admin/orders/ship/' . $sel['id'])?>" onclick="return confirmShip();" class="btnLink"><b>出貨</b></a>
<?	} ?>
							</td></tr>
							<tr><td class="infoBoxContent"><br>下單日期：<?=$sel['created_at']?></td></tr>
							<tr><td class="infoBoxContent">付款日期：<?=$sel['paid_at'] ? $sel['paid_at'] : '-'?></td></tr>
							<tr><td class="infoBoxContent">出貨日期：<?=$sel['shipped_at'] ? $sel['shipped_at'] : '-'?></td></tr>
							<tr><td class="infoBoxContent">客戶等級：<?=member_level_text($sel['member_level'])?></td></tr>
							<tr><td class="infoBoxContent">收件地址：<?=$sel['zip']?> <?=$sel['address']?></td></tr>
							<tr><td class="infoBoxContent"><br>商品小計：$<?=number_format($sel['subtotal'])?></td></tr>
							<tr><td class="infoBoxContent">折扣：-$<?=number_format($sel['discount'] + $sel['promo_discount'])?></td></tr>
							<tr><td class="infoBoxContent">運費：$<?=number_format($sel['shipping'])?></td></tr>
							<tr><td class="infoBoxContent"><b>訂單總額：$<?=number_format($sel['total'])?></b><br><br></td></tr>
						</table>
					</td>
<? } ?>
				</tr>
			</table>
		</td>
	</tr>
</table>
