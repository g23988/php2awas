<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
<title><?=$title?></title>
<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/erp.css">
<script language="javascript" src="<?=base_url()?>js/general.js"></script>
<script language="javascript">
// 商品資料 (料號 => 編號, 名稱, 售價, 庫存)
var products = new Array();
<? foreach ($products as $p) { ?>
products['<?=$p['sku']?>'] = new Array(<?=$p['id']?>, '<?=addslashes($p['name'])?>', <?=$p['price']?>, <?=$p['stock']?>);
<? } ?>

var memberLevel = 0;
var ROWS = 6;

function showTab(n) {
	for (var i = 1; i <= 3; i++) {
		document.getElementById('tab' + i).className = (i == n) ? 'tabOn' : 'tabOff';
		document.getElementById('tabBody' + i).style.display = (i == n) ? 'block' : 'none';
	}
}

function lookupMember() {
	var id = document.getElementById('member_id').value;
	if (id == '') return;
	var xhr = createXHR();
	if (!xhr) return;
	xhr.onreadystatechange = function() {
		if (xhr.readyState == 4 && xhr.status == 200) {
			var arr = xhr.responseText.split('|');
			if (arr[0] == 'OK') {
				memberLevel = parseInt(arr[2]);
				var levelText = (memberLevel == 3) ? 'VIP' : (memberLevel == 2 ? '金卡' : '一般');
				document.getElementById('member_name').innerHTML = arr[1] + ' / ' + arr[3] + ' ' + arr[4] + ' （' + levelText + '）';
				if (arr[6] == '0') {
					setStatus('售達方 ' + id + ' 已停權', 'E');
				} else {
					setStatus('', '');
				}
				// 預設送達方 = 售達方
				if (document.getElementById('receiver').value == '') {
					document.getElementById('receiver').value = arr[1];
					document.getElementById('phone').value = arr[5];
					document.getElementById('zip').value = arr[3];
					document.getElementById('address').value = arr[4];
				}
				document.getElementById('ship_to').innerHTML = document.getElementById('zip').value + ' ' + document.getElementById('address').value;
			} else {
				memberLevel = 0;
				document.getElementById('member_name').innerHTML = '';
				setStatus(arr[1], 'E');
			}
			calcTotal();
		}
	};
	// IE 會 cache AJAX，後面多加時間當參數 (CI 不能用 ?t=，會 404)
	xhr.open('GET', '<?=site_url('admin/phone_order/member')?>/' + id + '/' + new Date().getTime(), true);
	xhr.send(null);
}

function lookupProduct(n) {
	var sku = document.getElementById('sku' + n).value.toUpperCase();
	document.getElementById('sku' + n).value = sku;
	if (sku != '' && products[sku]) {
		document.getElementById('desc' + n).innerHTML = products[sku][1];
		document.getElementById('price' + n).innerHTML = formatNumber(products[sku][2]);
		if (document.getElementById('qty' + n).value == '') {
			document.getElementById('qty' + n).value = '1';
		}
	} else {
		document.getElementById('desc' + n).innerHTML = (sku == '') ? '' : '<font color="red">料號不存在</font>';
		document.getElementById('price' + n).innerHTML = '';
	}
	calcTotal();
}

// 試算淨值 (實際金額以儲存後為準)
function calcTotal() {
	var sub = 0;
	for (var n = 1; n <= ROWS; n++) {
		var sku = document.getElementById('sku' + n).value.toUpperCase();
		var qty = parseInt(document.getElementById('qty' + n).value);
		if (isNaN(qty)) qty = 0;
		if (products[sku]) {
			var amt = products[sku][2] * qty;
			document.getElementById('amount' + n).innerHTML = formatNumber(amt);
			sub += amt;
		} else {
			document.getElementById('amount' + n).innerHTML = '';
		}
	}
	var net = sub;
	if (memberLevel == 3) {
		net = Math.round(sub * 0.85);
	} else if (memberLevel == 2 && sub >= 3000) {
		net = Math.round(sub * 0.9);
	}
	document.getElementById('netvalue').value = formatNumber(net);
}

function setStatus(msg, type) {
	var bar = document.getElementById('statusbar');
	if (msg == '') {
		bar.innerHTML = '&nbsp;';
		bar.className = 'statusBar';
		return;
	}
	bar.innerHTML = (type == 'E' ? '<span class="iconErr">&#10006;</span> ' : '<span class="iconOk">&#10004;</span> ') + msg;
	bar.className = 'statusBar';
}

function doSave() {
	if (document.getElementById('member_id').value == '') {
		setStatus('請輸入售達方', 'E');
		document.getElementById('member_id').focus();
		return false;
	}
	return true;
}
</script>
</head>
<body onload="showTab(1)<? if ($message == '') echo '; document.getElementById(\'member_id\').focus()'; ?>">

<form name="orderForm" method="post" action="<?=site_url('admin/phone_order/save')?>" onsubmit="return doSave()">
<table class="sapWindow" width="100%" border="0" cellspacing="0" cellpadding="0">
	<!-- 選單列 -->
	<tr><td class="menuBar">
		<span><u>銷</u>售文件</span> <span><u>編</u>輯</span> <span><u>跳</u>至</span> <span>其<u>他</u></span>
		<span><u>環</u>境</span> <span><u>系</u>統</span> <span><u>說</u>明</span>
	</td></tr>
	<!-- 系統工具列 -->
	<tr><td class="sysToolbar">
		<button type="submit" class="tb tbOk" title="確認 (Enter)">&#10004;</button>
		<input type="text" class="cmdField" size="18" value="" title="指令欄位">
		<span class="sep"></span>
		<button type="submit" class="tb" title="儲存 (Ctrl+S)">儲存</button>
		<button type="button" class="tb" title="返回 (F3)" onclick="location.href='<?=site_url('admin/orders')?>'">&#9664; 返回</button>
		<button type="button" class="tb" title="結束 (Shift+F3)" onclick="location.href='<?=site_url('admin/orders')?>'">結束</button>
		<button type="reset" class="tb tbCancel" title="取消 (F12)">&#10006; 取消</button>
		<span class="sep"></span>
		<button type="button" class="tb" onclick="window.print()">列印</button>
		<button type="button" class="tb" onclick="alert('尚未開發')">尋找</button>
		<span class="sep"></span>
		<button type="button" class="tb" onclick="alert('客服專線分機 101')">說明</button>
	</td></tr>
	<!-- 標題列 -->
	<tr><td class="titleBar"><?=$title?></td></tr>
	<!-- 應用程式工具列 -->
	<tr><td class="appToolbar">
		<button type="button" class="tb" onclick="showTab(2)">品項明細</button>
		<button type="button" class="tb" onclick="showTab(1)">表頭</button>
		<span class="sep"></span>
		<button type="button" class="tb" onclick="location.href='<?=site_url('admin/orders/index/0')?>'">&#9638; 訂單清單</button>
	</td></tr>

	<tr><td class="sapBody">
		<!-- 表頭 -->
		<table border="0" cellspacing="0" cellpadding="2" class="hdr">
			<tr>
				<td class="lbl">標準訂單</td>
				<td><input type="text" class="fld fldRO" size="14" value="" readonly></td>
				<td width="40"></td>
				<td class="lbl">淨值</td>
				<td><input type="text" id="netvalue" class="fld fldRO num" size="18" value="0" readonly> <input type="text" class="fld fldRO" size="4" value="TWD" readonly></td>
			</tr>
			<tr>
				<td class="lbl"><u>售達方</u></td>
				<td><input type="text" name="member_id" id="member_id" class="fld fldReq" size="10" value="<?=$this->input->post('member_id')?>" onchange="lookupMember()"></td>
				<td></td>
				<td colspan="2"><span id="member_name" class="lnk"></span></td>
			</tr>
			<tr>
				<td class="lbl"><u>送達方</u></td>
				<td colspan="4"><span id="ship_to" class="lnk"></span> <span class="hint">（請於「出貨」頁籤修改）</span></td>
			</tr>
			<tr>
				<td class="lbl"><u>客戶採購單號</u></td>
				<td><input type="text" name="po_no" class="fld" size="14" value="<?=$this->input->post('po_no')?>"></td>
				<td></td>
				<td class="lbl"><u>採購日期</u></td>
				<td><input type="text" name="po_date" class="fld" size="12" value="<?=date('Y/m/d')?>"> <span class="f4">&#9636;</span></td>
			</tr>
		</table>

		<!-- 頁籤 -->
		<table border="0" cellspacing="0" cellpadding="0" class="tabs">
			<tr>
				<td id="tab1" class="tabOn" onclick="showTab(1)">銷售</td>
				<td id="tab2" class="tabOff" onclick="showTab(2)">品項總覽</td>
				<td id="tab3" class="tabOff" onclick="showTab(3)">出貨</td>
				<td class="tabDis">拒絕原因</td>
				<td class="tabFill">&nbsp;</td>
			</tr>
		</table>
		<div class="tabPanel">
			<div id="tabBody1">
				<table border="0" cellspacing="0" cellpadding="3">
					<tr>
						<td class="lbl">需求交貨日</td>
						<td><input type="text" class="fld" size="2" value="D" readonly> <input type="text" name="req_date" class="fld" size="12" value="<?=date('Y/m/d', time() + 3 * 86400)?>"></td>
						<td width="40"></td>
						<td class="lbl">出貨倉庫</td>
						<td><input type="text" class="fld" size="6" value="TPE1"></td>
					</tr>
					<tr>
						<td class="lbl"><input type="checkbox"> 完整交貨</td>
						<td></td><td></td>
						<td class="lbl">付款條件</td>
						<td>
							<select name="pay_term" class="fld">
								<option value="ATM轉帳">0001 ATM 轉帳（3日內）</option>
								<option value="貨到付款">0002 貨到付款</option>
								<option value="月結30天">0003 月結 30 天</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="lbl">訂單原因</td>
						<td colspan="4">
							<select name="order_reason" class="fld">
								<option>001 電話訂購</option>
								<option>002 傳真訂購</option>
								<option>003 業務代訂</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="lbl">銷售範圍</td>
						<td colspan="4"><input type="text" class="fld fldRO" size="18" value="TW01 / PH / 01" readonly> 台灣 / 電話訂購 / 生鮮食品</td>
					</tr>
				</table>
			</div>
			<div id="tabBody2" style="display:none">
				<table border="0" cellspacing="0" cellpadding="3">
					<tr><td class="hint">在下方「所有品項」輸入料號與數量，按 Enter 即可帶出品名與單價。<br>可用料號：<? foreach ($products as $p) echo $p['sku'] . ' '; ?></td></tr>
				</table>
			</div>
			<div id="tabBody3" style="display:none">
				<table border="0" cellspacing="0" cellpadding="3">
					<tr><td class="lbl">收件人</td><td><input type="text" name="receiver" id="receiver" class="fld fldReq" size="16" value="<?=$this->input->post('receiver')?>"></td></tr>
					<tr><td class="lbl">電話</td><td><input type="text" name="phone" id="phone" class="fld fldReq" size="16" value="<?=$this->input->post('phone')?>"></td></tr>
					<tr><td class="lbl">郵遞區號</td><td><input type="text" name="zip" id="zip" class="fld fldReq" size="5" value="<?=$this->input->post('zip')?>"></td></tr>
					<tr><td class="lbl">地址</td><td><input type="text" name="address" id="address" class="fld fldReq" size="50" value="<?=$this->input->post('address')?>"></td></tr>
					<tr><td class="lbl">備註</td><td><input type="text" name="note" class="fld" size="50" value="<?=$this->input->post('note')?>"></td></tr>
				</table>
			</div>
		</div>

		<!-- 品項 -->
		<div class="gridTitle">所有品項</div>
		<table border="0" cellspacing="1" cellpadding="1" class="grid">
			<tr>
				<th width="20"></th>
				<th width="40">品項</th>
				<th width="110">料號</th>
				<th width="90">訂購數量</th>
				<th width="40">單位</th>
				<th width="200">說明</th>
				<th width="80">單價</th>
				<th width="90">淨值</th>
			</tr>
<? $skus = $this->input->post('sku'); $qtys = $this->input->post('qty'); ?>
<? for ($n = 1; $n <= 6; $n++) { ?>
			<tr>
				<td class="sel"></td>
				<td class="num"><?=$n * 10?></td>
				<td><input type="text" name="sku[]" id="sku<?=$n?>" class="cell" size="12" value="<?=isset($skus[$n - 1]) ? $skus[$n - 1] : ''?>" onchange="lookupProduct(<?=$n?>)"></td>
				<td><input type="text" name="qty[]" id="qty<?=$n?>" class="cell num" size="8" value="<?=isset($qtys[$n - 1]) ? $qtys[$n - 1] : ''?>" onchange="calcTotal()"></td>
				<td class="ro">個</td>
				<td class="ro" id="desc<?=$n?>"></td>
				<td class="ro num" id="price<?=$n?>"></td>
				<td class="ro num" id="amount<?=$n?>"></td>
			</tr>
<? } ?>
		</table>
	</td></tr>

	<!-- 狀態列 -->
	<tr><td>
		<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
			<td id="statusbar" class="statusBar"><? if ($message != '') { ?><span class="<?=$msg_type == 'E' ? 'iconErr' : 'iconOk'?>"><?=$msg_type == 'E' ? '&#10006;' : '&#10004;'?></span> <?=$message?><? } else { ?>&nbsp;<? } ?></td>
			<td class="statusInfo" width="260">SHP (1) 100 &nbsp;|&nbsp; <?=$this->session->userdata('admin_name')?> &nbsp;|&nbsp; INS</td>
		</tr></table>
	</td></tr>
</table>
</form>

<script language="javascript">
<? if ($this->input->post('member_id')) { ?>
lookupMember();
<? } ?>
for (var n = 1; n <= ROWS; n++) {
	if (document.getElementById('sku' + n).value != '') lookupProduct(n);
}
</script>
</body>
</html>
