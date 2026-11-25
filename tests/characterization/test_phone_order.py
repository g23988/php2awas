"""
後台電話訂購（仿 ERP 畫面，舊系統限定）
建單流程是從 api/order.php 複製貼上的，有些規則沒有一起複製過來
"""

import re

import pytest

from conftest import NORMAL_DAY

pytestmark = pytest.mark.legacy_only


@pytest.fixture
def admin(client):
    client.post("admin/login", username="admin", password="admin123")
    return client


def save(client, member_id="3", lines=(("P002", "1"),), zip="404", **extra):
    data = {
        "member_id": member_id,
        "receiver": "林志豪",
        "phone": "0912-000-003",
        "zip": zip,
        "address": "台中市北區三民路三段50號",
        "pay_term": "ATM轉帳",
        "po_no": "PO-001",
        "note": "",
        "debug_now": NORMAL_DAY,
    }
    data.update(extra)
    pairs = list(data.items())
    for sku, qty in lines:
        pairs += [("sku[]", sku), ("qty[]", qty)]
    return client.post_pairs("admin/phone_order/save", pairs)


def new_order_no(client):
    page = client.get("admin/orders").text
    m = re.search(r"標準訂單 (\d{12}) 已儲存", page)
    return m.group(1) if m else None


@pytest.mark.rule("R-PHO-01")
def test_PHO01_member_lookup_ajax(admin):
    assert admin.get("admin/phone_order/member/3").text == "OK|林志豪|3|404|台中市北區三民路三段50號|0912-000-003|1"
    assert admin.get("admin/phone_order/member/999").text == "ERR|找不到售達方 999"


@pytest.mark.rule("R-PHO-02")
def test_PHO02_save_order_same_pricing_as_api(admin, shop):
    r = save(admin)
    assert r.status == 302
    order_no = new_order_no(admin)
    assert order_no
    o = shop.order(order_no)
    assert (o["subtotal"], o["discount"], o["shipping"], o["total"]) == (1200, 180, 0, 1020)


@pytest.mark.rule("R-PHO-03")
def test_PHO03_erp_fields_saved_into_note(admin, shop):
    save(admin, po_no="PO-20111115-07")
    page = admin.get("admin/orders").text
    assert "[電話訂購] 客服:系統管理員 客戶採購單號:PO-20111115-07 付款:ATM轉帳" in page


@pytest.mark.legacy_bug("BUG-09")
def test_PHO04_sample_pack_limit_missing(admin, shop):
    """
    試吃包一筆限購 1 包的規則沒有複製到電話訂購 → 可以訂 3 包
    （API 建單會被改成 1 包，見 ORD-14）
    """
    save(admin, lines=(("P010", "3"),))
    o = shop.order(new_order_no(admin))
    assert o["subtotal"] == 150
    assert shop.stock(10) == 996


@pytest.mark.rule("R-PHO-04")
@pytest.mark.parametrize(
    "kwargs,message",
    [
        ({"member_id": ""}, "請輸入售達方"),
        ({"member_id": "999"}, "售達方 999 不存在"),
        ({"member_id": "8"}, "售達方 8 已停權，不可建立訂單"),
        ({"receiver": ""}, "送達方資料不完整（出貨頁籤）"),
        ({"lines": (("P007", "1"),)}, "品項 10：料號 P007 不存在或已下架"),
        ({"lines": (("P001", "1"), ("P002", "11"))}, "品項 20：訂購數量必須是 1~10"),
        ({"lines": (("P005", "6"),)}, "品項 10：花蓮剝皮辣椒 庫存不足（剩 5）"),
        ({"lines": (("", ""),)}, "請至少輸入一個品項"),
    ],
)
def test_PHO05_validation_messages_in_status_bar(admin, kwargs, message):
    r = save(admin, **kwargs)
    assert r.status == 200
    assert message in r.text


@pytest.mark.rule("R-PHO-01")
def test_PHO06_requires_login(client):
    r = client.get("admin/phone_order")
    assert r.status == 302 and r.location.endswith("/admin/login")
