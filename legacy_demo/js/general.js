/*
  鮮農直送網 共用 javascript
  rowOverEffect / rowOutEffect 參考 osCommerce
  2009/10 Kevin
*/

function rowOverEffect(object) {
  if (object.className == 'dataTableRow') object.className = 'dataTableRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'dataTableRowOver') object.className = 'dataTableRow';
}

function popupWindow(url, name, w, h) {
  var left = (screen.width - w) / 2;
  var top = (screen.height - h) / 2;
  window.open(url, name, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=' + w + ',height=' + h + ',left=' + left + ',top=' + top);
}

function confirmDelete(url) {
  if (confirm('確定要刪除嗎？刪除後無法復原！')) {
    document.location.href = url;
  }
}

function goStatus(sel, base) {
  document.location.href = base + sel.options[sel.selectedIndex].value;
}

// XMLHttpRequest (IE6 要用 ActiveX)
function createXHR() {
  if (window.XMLHttpRequest) {
    return new XMLHttpRequest();
  }
  try { return new ActiveXObject('Msxml2.XMLHTTP'); } catch (e) {}
  try { return new ActiveXObject('Microsoft.XMLHTTP'); } catch (e) {}
  alert('您的瀏覽器不支援 AJAX，請使用 IE 6.0 以上版本');
  return null;
}

function formatNumber(num) {
  num = num + '';
  var re = /(\d+)(\d{3})/;
  while (re.test(num)) {
    num = num.replace(re, '$1,$2');
  }
  return num;
}
