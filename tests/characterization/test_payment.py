"""
金流付款通知  POST api/payment/notify
金流商規定：成功回 1|OK，失敗回 0|原因
"""

import pytest

from conftest import expect

PENDING_VIP_ORDER = "201106290002"  # 會員 3（VIP），總計 1020


@pytest.mark.rule("R-PAY-01")
def test_PAY01_valid_notify_marks_paid(shop):
    r = shop.pay(PENDING_VIP_ORDER, "1020")
    assert r.text == "1|OK"
    assert shop.order(PENDING_VIP_ORDER)["status"] == 1


@pytest.mark.mail
@pytest.mark.rule("R-PNT-01")
def test_PAY02_vip_gets_double_points(shop, mails):
    """點數 = floor(總計 / 100)，VIP 雙倍：1020 → 10 × 2 = 20"""
    shop.pay(PENDING_VIP_ORDER, "1020")
    sent = mails()
    assert len(sent) == 1
    assert "付款完成通知" in sent[0]["subject"]
    assert "本次獲得紅利點數：20 點" in sent[0]["body"]


@pytest.mark.mail
@pytest.mark.rule("R-PNT-02")
def test_PAY03_points_include_shipping(shop, mails):
    """點數用「含運費」總計：380 + 80 = 460 → 4 點（不含運費會是 3 點）"""
    r = shop.create_order(1, "1:1")
    order_no, total = r.fields[1], r.fields[2]
    assert total == "460"
    shop.pay(order_no, total)
    assert "本次獲得紅利點數：4 點" in mails()[-1]["body"]


@pytest.mark.rule("R-PAY-02")
def test_PAY04_wrong_checksum(shop):
    r = shop.pay(PENDING_VIP_ORDER, "1020", chk="0" * 32)
    assert r.text == "0|CHK_ERR"
    assert shop.order(PENDING_VIP_ORDER)["status"] == 0


@pytest.mark.security("SEC-05")
@pytest.mark.legacy_bug("BUG-06")
def test_PAY05_empty_checksum_is_accepted(shop):
    """
    chk 留空就跳過驗證（程式註解：測試環境金流不會帶 chk）
    舊系統：任何人都能偽造付款成功
    """
    r = shop.pay(PENDING_VIP_ORDER, "1020", chk="")
    assert r.text == expect("1|OK", "0|CHK_ERR")


@pytest.mark.rule("R-PAY-03")
def test_PAY06_amount_mismatch(shop):
    r = shop.pay(PENDING_VIP_ORDER, "1000")
    assert r.text == "0|AMOUNT_ERR"
    assert shop.order(PENDING_VIP_ORDER)["status"] == 0


@pytest.mark.needs_decision("DEC-03")
@pytest.mark.parametrize("amount", ["1020.00", "1.02e3", " 1020"])
def test_PAY07_amount_compared_as_number(shop, amount):
    """
    金額用 PHP 寬鬆比較（!=）：兩邊都是「數字字串」時以數值比較
    → "1020.00"、"1.02e3"、" 1020" 都被當成 1020（新系統要不要接受，待決定）
    """
    r = shop.pay(PENDING_VIP_ORDER, amount)
    assert r.text == "1|OK"


@pytest.mark.rule("R-PAY-03")
def test_PAY07b_non_numeric_amount_rejected(shop):
    """"1020abc" 不是數字字串，會改用字串比較 → 不相等"""
    r = shop.pay(PENDING_VIP_ORDER, "1020abc")
    assert r.text == "0|AMOUNT_ERR"


@pytest.mark.mail
@pytest.mark.rule("R-PAY-04")
def test_PAY08_duplicate_notify_is_idempotent(shop, mails):
    """金流商重送：一樣回 1|OK，但不重複加點數、不重複寄信"""
    assert shop.pay(PENDING_VIP_ORDER, "1020").text == "1|OK"
    assert shop.pay(PENDING_VIP_ORDER, "1020").text == "1|OK"
    assert len(mails()) == 1


@pytest.mark.needs_decision("DEC-02")
def test_PAY09_notify_for_cancelled_order_silently_ok(shop):
    """已取消的訂單收到付款通知：回 1|OK，但訂單維持已取消（錢收了、訂單沒恢復）"""
    shop.cancel(PENDING_VIP_ORDER, 3)
    r = shop.pay(PENDING_VIP_ORDER, "1020")
    assert r.text == "1|OK"
    assert shop.order(PENDING_VIP_ORDER)["status"] == 9


@pytest.mark.rule("R-PAY-05")
def test_PAY10_order_not_found(shop):
    r = shop.pay("000000000000", "100")
    assert r.text == "0|NO_ORDER"
