"""建立訂單：基本流程、驗證規則、扣庫存（POST api/order/create）"""

import pytest

from conftest import NORMAL_DAY, ORDER_NO_RE, expect


@pytest.mark.rule("R-ORD-01")
def test_ORD01_basic_order_response_format(shop):
    """一般會員、本島、未滿千：回傳 OK|訂單編號|應付金額"""
    r = shop.create_order(1, "1:1")
    assert r.fields[0] == "OK"
    assert ORDER_NO_RE.match(r.fields[1])
    assert r.fields[2] == "460"  # 380 + 運費 80


@pytest.mark.rule("R-ORD-02")
def test_ORD02_order_no_is_date_plus_4_digits(shop):
    """訂單編號 = 下單日期 yyyymmdd + 4 碼數字"""
    r = shop.create_order(1, "1:1", now="2011-06-15 23:59:00")
    assert r.fields[1].startswith("20110615")
    assert len(r.fields[1]) == 12


@pytest.mark.rule("R-ORD-03")
def test_ORD03_order_saved_with_breakdown(shop):
    r = shop.create_order(1, "1:2,3:1")
    o = shop.order(r.fields[1])
    assert o["status"] == 0
    assert (o["subtotal"], o["discount"], o["promo_discount"], o["shipping"], o["total"]) == (910, 0, 0, 80, 990)
    assert o["created_at"] == NORMAL_DAY


@pytest.mark.rule("R-STK-01")
def test_ORD04_stock_deducted(shop):
    shop.create_order(1, "1:2,3:3")
    assert shop.stock(1) == 98
    assert shop.stock(3) == 197


@pytest.mark.mail
@pytest.mark.rule("R-NTF-01")
def test_ORD05_order_created_mail(shop, mails):
    r = shop.create_order(1, "1:1")
    sent = mails()
    assert len(sent) == 1
    assert sent[0]["to"] == "xiaoming.wang@example.com"
    assert "訂單成立通知" in sent[0]["subject"]
    assert r.fields[1] in sent[0]["body"]
    assert "460 元" in sent[0]["body"]


@pytest.mark.rule("R-VAL-01")
@pytest.mark.parametrize("missing", ["receiver", "phone", "zip", "address"])
def test_ORD06_missing_shipping_info(shop, missing):
    r = shop.create_order(1, "1:1", **{missing: ""})
    assert r.fields[:2] == ["ERR", "E05"]


@pytest.mark.rule("R-VAL-02")
def test_ORD07_member_not_found(shop):
    r = shop.create_order(999, "1:1")
    assert r.fields[:2] == ["ERR", "E01"]


@pytest.mark.rule("R-VAL-03")
def test_ORD08_member_suspended(shop):
    r = shop.create_order(8, "1:1")
    assert r.fields[:2] == ["ERR", "E06"]


@pytest.mark.rule("R-VAL-04")
@pytest.mark.parametrize("qty", ["0", "-1", "11", "abc"])
def test_ORD09_invalid_quantity(shop, qty):
    r = shop.create_order(1, f"1:{qty}")
    assert r.fields[:2] == ["ERR", "E04"]


@pytest.mark.rule("R-VAL-04")
def test_ORD10_quantity_10_is_allowed(shop):
    r = shop.create_order(1, "3:10")
    assert r.ok


@pytest.mark.rule("R-VAL-05")
@pytest.mark.parametrize("pid", [7, 999])
def test_ORD11_product_unavailable(shop, pid):
    """下架（7）或不存在（999）的商品"""
    r = shop.create_order(1, f"{pid}:1")
    assert r.fields[:2] == ["ERR", "E02"]


@pytest.mark.rule("R-STK-02")
def test_ORD12_insufficient_stock(shop):
    r = shop.create_order(1, "5:6")
    assert r.fields == ["ERR", "E03", "花蓮剝皮辣椒 庫存不足"]
    assert shop.stock(5) == 5


@pytest.mark.rule("R-STK-02")
def test_ORD13_exact_stock_is_allowed(shop):
    r = shop.create_order(1, "5:5")
    assert r.ok
    assert shop.stock(5) == 0


@pytest.mark.rule("R-ORD-04")
def test_ORD14_sample_pack_limited_to_one(shop):
    """試吃包（商品 10）一筆限購 1 包：數量會被默默改成 1，不會報錯"""
    r = shop.create_order(1, "10:3")
    assert r.ok
    o = shop.order(r.fields[1])
    assert o["subtotal"] == 50
    assert shop.stock(10) == 998


@pytest.mark.rule("R-VAL-06")
def test_ORD15_validation_order_shipping_info_first(shop):
    """檢查順序：收件資料 → 會員 → 逐項商品"""
    r = shop.create_order(999, "7:1", receiver="")
    assert r.code == "E05"
    r = shop.create_order(999, "7:1")
    assert r.code == "E01"


@pytest.mark.rule("R-VAL-06")
def test_ORD16_error_in_later_item_creates_nothing(shop):
    """第二項商品有錯時，整筆不成立、第一項也不扣庫存"""
    r = shop.create_order(1, "1:1,5:99")
    assert r.code == "E04"
    r = shop.create_order(1, "1:1,5:6")
    assert r.code == "E03"
    assert shop.stock(1) == 100


@pytest.mark.legacy_bug("BUG-04")
def test_ORD17_duplicate_product_lines(shop):
    """
    同一商品出現兩次（5:3,5:3，庫存 5）
    舊系統：每一行各自拿原始庫存比對 → 成立；扣庫存時用舊值覆寫 → 庫存剩 2（少扣 3）
    新系統：應合併計算，6 > 5 → 庫存不足
    """
    r = shop.create_order(1, "5:3,5:3")
    assert r.fields[0] == expect("OK", "ERR")
    assert shop.stock(5) == expect(2, 5)


@pytest.mark.legacy_bug("BUG-05")
def test_ORD18_apostrophe_in_receiver(shop):
    """
    收件人含單引號（O'Neil）
    舊系統：SQL 字串拼接壞掉 → 資料庫錯誤頁（還會把 SQL 印出來）
    新系統：正常成立
    """
    r = shop.create_order(1, "1:1", receiver="O'Neil")
    if expect(True, False):
        assert r.status == 500
        assert "A Database Error Occurred" in r.text
    else:
        assert r.ok
