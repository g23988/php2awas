"""
前台購物網站（舊系統限定）
購物車存在 session，結帳時前台用 HTTP 呼叫自己的 api/order/create
"""

import re

import pytest

from conftest import NORMAL_DAY

pytestmark = pytest.mark.legacy_only

MEMBERS = {
    1: "xiaoming.wang@example.com",
    3: "chihhao.lin@example.com",
    5: "chienhung.li@example.com",
    8: "chiahao.liu@example.com",
}


def login(client, member_id, password="123456"):
    return client.post("shop/login", email=MEMBERS[member_id], password=password)


def checkout(client, zip="404", **extra):
    data = {
        "receiver": "測試收件人",
        "phone": "0912-345-678",
        "zip": zip,
        "address": "測試地址",
        "note": "",
        "debug_now": NORMAL_DAY,
    }
    data.update(extra)
    return client.post("shop/submit", **data)


@pytest.mark.rule("R-WEB-01")
def test_SHOP01_home_lists_active_products_only(client):
    page = client.get("shop").text
    assert "有機白米 2kg" in page
    assert "宜蘭三星蔥餅" not in page  # 下架
    assert "只剩 5 件" in page  # 低庫存提示
    assert "建議使用 IE 6.0 以上瀏覽器" in page


@pytest.mark.rule("R-WEB-02")
def test_SHOP02_add_to_cart(client):
    r = client.get("shop/add/1")
    assert r.status == 302 and r.location.endswith("/shop/cart")
    client.get("shop/add/1")
    client.get("shop/add/3")
    page = client.get("shop/cart").text
    assert 'name="qty[1]" value="2"' in page
    assert "小計 = <span class=\"price\">$910</span>" in page
    assert "購物車 (3)" in page


@pytest.mark.rule("R-WEB-02")
def test_SHOP03_update_quantity_and_remove_with_zero(client):
    client.get("shop/add/1")
    client.get("shop/add/3")
    client.post("shop/update", **{"qty[1]": "3", "qty[3]": "0"})
    page = client.get("shop/cart").text
    assert 'name="qty[1]" value="3"' in page
    assert "手工黑糖" not in page


@pytest.mark.rule("R-WEB-03")
def test_SHOP04_save_for_later_excluded_from_subtotal(client):
    client.get("shop/add/1")
    client.get("shop/add/2")
    client.get("shop/later/2")
    page = client.get("shop/cart").text
    assert "$380</span>" in page.split("稍後購買的商品")[0]
    assert "稍後購買的商品" in page
    client.get("shop/move/2")
    assert "$1,580" in client.get("shop/cart").text


@pytest.mark.rule("R-WEB-04")
def test_SHOP05_checkout_requires_login(client):
    client.get("shop/add/1")
    r = client.get("shop/checkout")
    assert r.status == 302 and r.location.endswith("/shop/login")


@pytest.mark.rule("R-WEB-04")
def test_SHOP06_member_login(client):
    assert "Email 或密碼錯誤" in login(client, 3, password="wrong").text
    client.get("shop/add/1")
    r = login(client, 3)
    assert r.status == 302 and r.location.endswith("/shop/checkout")
    assert "林志豪 您好" in client.get("shop/checkout").text


@pytest.mark.rule("R-WEB-05")
def test_SHOP07_checkout_creates_order_via_api(client, shop):
    """前台結帳走 API，金額和直接呼叫 API 完全一樣（VIP 1001 → 850）"""
    login(client, 3)
    client.get("shop/add/9")
    r = checkout(client)
    assert r.status == 302 and "/shop/done/" in r.location
    order_no = r.location.rsplit("/", 1)[1]
    done = client.get(f"shop/done/{order_no}").text
    assert "$850" in done
    assert shop.order(order_no)["total"] == 850
    assert "購物車 (0)" in client.get("shop").text


@pytest.mark.rule("R-WEB-06")
def test_SHOP08_vip_saving_shown_in_cart_is_rounded_differently(client):
    """
    購物車顯示「VIP 可省」是逐項 round(金額 × 0.15)：1001 → 顯示省 $150
    實際結帳是 intval(1001 × 0.85) → 折 151（同一條規則寫了兩次，算法不同）
    """
    login(client, 3)
    client.get("shop/add/9")
    assert "VIP 可省：$150 (15%)" in client.get("shop/cart").text


@pytest.mark.rule("R-WEB-07")
def test_SHOP09_free_shipping_banner_ignores_islands(client, shop):
    """購物車滿千就顯示「符合免運費」，沒有判斷離島 → 金門會員實際被收 250"""
    login(client, 5)
    client.get("shop/add/6")
    assert "符合<b>免運費</b>資格" in client.get("shop/cart").text
    r = checkout(client, zip="893")
    order_no = r.location.rsplit("/", 1)[1]
    assert shop.order(order_no)["shipping"] == 250


@pytest.mark.rule("R-WEB-08")
def test_SHOP10_suspended_member_can_login_but_not_order(client):
    """停權會員登入不會擋，到結帳才由 API 回 E06"""
    client.get("shop/add/1")
    r = login(client, 8)
    assert r.status == 302
    r = checkout(client, zip="300")
    assert r.location.endswith("/shop/checkout")
    assert "會員已停權 (E06)" in client.get("shop/checkout").text


@pytest.mark.rule("R-WEB-09")
def test_SHOP11_api_error_message_shown_on_checkout(client):
    """庫存不足等 API 錯誤，前台直接顯示 API 的訊息"""
    login(client, 1)
    client.get("shop/add/5")
    client.post("shop/update", **{"qty[5]": "6"})
    r = checkout(client, zip="100")
    assert r.location.endswith("/shop/checkout")
    page = client.get("shop/checkout").text
    assert re.search(r"花蓮剝皮辣椒 庫存不足 \(E03\)", page)
