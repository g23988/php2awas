<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr><td class="pageHeading"><?=$title?></td></tr>
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td valign="top">
						<table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr class="dataTableHeadingRow">
								<td class="dataTableHeadingContent">編號</td>
								<td class="dataTableHeadingContent">姓名</td>
								<td class="dataTableHeadingContent">Email</td>
								<td class="dataTableHeadingContent">電話</td>
								<td class="dataTableHeadingContent" align="center">等級</td>
								<td class="dataTableHeadingContent" align="right">紅利點數</td>
								<td class="dataTableHeadingContent" align="center">加入日期</td>
								<td class="dataTableHeadingContent" align="right">動作&nbsp;</td>
							</tr>
<? foreach ($customers as $c) { ?>
<?	if ($sel && $sel['id'] == $c['id']) { ?>
							<tr class="dataTableRowSelected">
<?	} else { ?>
							<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?=site_url('admin/customers/index/' . $c['id'])?>'">
<?	} ?>
								<td class="dataTableContent"><?=$c['id']?></td>
								<td class="dataTableContent"><?=$c['name']?><? if ($c['status'] == 0) echo ' <font color="red">(停權)</font>'; ?></td>
								<td class="dataTableContent"><?=$c['email']?></td>
								<td class="dataTableContent"><?=$c['phone']?></td>
								<td class="dataTableContent" align="center"><? if ($c['level'] == 3) { ?><font color="red"><b>VIP</b></font><? } else { echo member_level_text($c['level']); } ?></td>
								<td class="dataTableContent" align="right"><?=number_format($c['points'])?></td>
								<td class="dataTableContent" align="center"><?=substr($c['created_at'], 0, 10)?></td>
								<td class="dataTableContent" align="right"><? if ($sel && $sel['id'] == $c['id']) { ?><font color="#336633"><b>&#9658;</b></font><? } else { ?><font color="#7187bb">[i]</font><? } ?>&nbsp;</td>
							</tr>
<? } ?>
						</table>
					</td>
<? if ($sel) { ?>
					<td width="25%" valign="top">
						<table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr><td class="infoBoxHeading"><b><?=$sel['name']?></b></td></tr>
							<tr><td class="infoBoxContent" align="center">
								<form method="post" action="<?=site_url('admin/orders/index/all')?>">
									<input type="hidden" name="keyword" value="<?=$sel['phone']?>">
									<input type="submit" value="查看訂單" class="btn">
								</form>
							</td></tr>
							<tr><td class="infoBoxContent"><br>等級：<?=member_level_text($sel['level'])?></td></tr>
							<tr><td class="infoBoxContent">紅利點數：<?=number_format($sel['points'])?> 點</td></tr>
							<tr><td class="infoBoxContent">地址：<?=$sel['zip']?> <?=$sel['address']?></td></tr>
							<tr><td class="infoBoxContent"><br>訂單數：<?=$sel['order_count']?> 筆</td></tr>
							<tr><td class="infoBoxContent">累計消費：$<?=number_format($sel['order_amount'])?><br><br></td></tr>
						</table>
					</td>
<? } ?>
				</tr>
			</table>
		</td>
	</tr>
</table>
