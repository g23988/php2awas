"""查詢訂單、取消訂單、查詢庫存"""

import pytest

from conftest import expect


# ---------------------------------------------------------------------------
# 查詢訂單  GET api/order/query/{order_no}
# ---------------------------------------------------------------------------

@pytest.mark.rule("R-QRY-01")
def test_QRY01_query_format(shop):
    """OK|訂單編號|狀態|小計|會員折扣|活動折扣|運費|總計|下單時間"""
    r = shop.query("200911150023")
    assert r.text == "OK|200911150023|2|910|0|0|80|990|2009-11-15 10:23:11"


@pytest.mark.rule("R-QRY-01")
def test_QRY02_query_not_found(shop):
    r = shop.query("000000000000")
    assert r.fields == ["ERR", "E11", "查無此訂單"]


# ---------------------------------------------------------------------------
# 取消訂單  POST api/order/cancel
# ---------------------------------------------------------------------------

@pytest.mark.rule("R-CXL-01")
def test_CXL01_cancel_pending_order(shop):
    r = shop.cancel("201106290002", 3)
    assert r.fields == ["OK", "201106290002"]
    assert shop.order("201106290002")["status"] == 9


@pytest.mark.legacy_bug("BUG-01")
def test_CXL02_cancel_restores_stock(shop):
    """
    取消待付款訂單後，庫存要加回去
    舊系統：沒有加回（Bug）；排程自動取消卻有加回，兩邊行為不一致
    """
    assert shop.stock(2) == 50
    shop.cancel("201106290002", 3)
    assert shop.stock(2) == expect(50, 51)


@pytest.mark.rule("R-CXL-02")
@pytest.mark.parametrize("order_no", ["201106280003", "200911150023", "201106270004"])
def test_CXL03_only_pending_can_be_cancelled(shop, order_no):
    """已付款、已出貨、已取消 → E12"""
    member = {"201106280003": 3, "200911150023": 1, "201106270004": 1}[order_no]
    r = shop.cancel(order_no, member)
    assert r.fields[:2] == ["ERR", "E12"]


@pytest.mark.rule("R-CXL-03")
def test_CXL04_cannot_cancel_other_members_order(shop):
    r = shop.cancel("201106290002", 1)
    assert r.fields[:2] == ["ERR", "E11"]
    assert shop.order("201106290002")["status"] == 0


@pytest.mark.rule("R-CXL-03")
def test_CXL05_cancel_not_found(shop):
    r = shop.cancel("000000000000", 1)
    assert r.fields[:2] == ["ERR", "E11"]


@pytest.mark.rule("R-CXL-01")
def test_CXL06_new_order_can_be_cancelled(shop):
    r = shop.create_order(1, "1:2")
    order_no = r.fields[1]
    assert shop.cancel(order_no, 1).ok
    assert shop.order(order_no)["status"] == 9


# ---------------------------------------------------------------------------
# 查詢庫存  GET api/product/stock/{id}
# ---------------------------------------------------------------------------

@pytest.mark.rule("R-PRD-01")
def test_PRD01_stock_format(client):
    """OK|商品ID|名稱|售價|庫存|狀態"""
    r = client.get("api/product/stock/1")
    assert r.text == "OK|1|有機白米 2kg|380|100|1"


@pytest.mark.rule("R-PRD-01")
def test_PRD02_inactive_product_still_queryable(client):
    r = client.get("api/product/stock/7")
    assert r.fields[0] == "OK"
    assert r.fields[5] == "0"


@pytest.mark.rule("R-PRD-01")
def test_PRD03_product_not_found(client):
    r = client.get("api/product/stock/999")
    assert r.fields == ["ERR", "E02", "商品不存在"]
