"""
計價規則：會員折扣、11 月活動、運費

公式：應付 = 小計 - 會員折扣 - 活動折扣 + 運費
"""

import pytest

from conftest import NOVEMBER_DAY


def breakdown(shop, r):
    assert r.ok, r
    o = shop.order(r.fields[1])
    return (o["subtotal"], o["discount"], o["promo_discount"], o["shipping"], o["total"])


# ---------------------------------------------------------------------------
# 會員折扣
# ---------------------------------------------------------------------------

@pytest.mark.rule("R-PRC-01")
def test_PRC01_vip_15_percent_off(shop):
    """VIP（等級 3）打 85 折：1200 → 折 180"""
    r = shop.create_order(3, "2:1", zip="404")
    assert breakdown(shop, r) == (1200, 180, 0, 0, 1020)


@pytest.mark.rule("R-PRC-02")
def test_PRC02_vip_discount_truncates(shop):
    """VIP 折後金額無條件捨去：1001 × 0.85 = 850.85 → 付 850（折 151），不是四捨五入的 851"""
    r = shop.create_order(3, "9:1", zip="404")
    assert breakdown(shop, r) == (1001, 151, 0, 0, 850)


@pytest.mark.rule("R-PRC-03")
def test_PRC03_gold_under_3000_no_discount(shop):
    """金卡（等級 2）未滿 3000 不打折"""
    r = shop.create_order(2, "6:1,2:1,5:1", zip="220")  # 2960
    assert breakdown(shop, r) == (2960, 0, 0, 0, 2960)


@pytest.mark.rule("R-PRC-03")
def test_PRC04_gold_3000_10_percent_off(shop):
    """金卡滿 3000（含）打 9 折"""
    r = shop.create_order(2, "6:2", zip="220")  # 3000
    assert breakdown(shop, r) == (3000, 300, 0, 0, 2700)


@pytest.mark.rule("R-PRC-04")
def test_PRC05_normal_member_no_discount(shop):
    """一般會員不論金額都不打折"""
    r = shop.create_order(1, "6:4")
    assert breakdown(shop, r) == (6000, 0, 0, 0, 6000)


# ---------------------------------------------------------------------------
# 11 月活動
# ---------------------------------------------------------------------------

@pytest.mark.rule("R-PRM-01")
def test_PRM01_november_over_1000_minus_100(shop):
    r = shop.create_order(1, "6:1", now=NOVEMBER_DAY)
    assert breakdown(shop, r) == (1500, 0, 100, 0, 1400)


@pytest.mark.rule("R-PRM-01")
def test_PRM02_november_under_1000_no_promo(shop):
    r = shop.create_order(1, "1:2", now=NOVEMBER_DAY)
    assert breakdown(shop, r) == (760, 0, 0, 80, 840)


@pytest.mark.rule("R-PRM-02")
def test_PRM03_promo_threshold_uses_amount_after_member_discount(shop):
    """門檻看「會員折扣後」金額：VIP 1001 → 850，未達 1000，不折"""
    r = shop.create_order(3, "9:1", zip="404", now=NOVEMBER_DAY)
    assert breakdown(shop, r) == (1001, 151, 0, 0, 850)


@pytest.mark.rule("R-PRM-02")
def test_PRM04_vip_and_promo_stack(shop):
    """VIP 1200 → 1020，達 1000，再折 100"""
    r = shop.create_order(3, "2:1", zip="404", now=NOVEMBER_DAY)
    assert breakdown(shop, r) == (1200, 180, 100, 0, 920)


@pytest.mark.rule("R-PRM-03")
@pytest.mark.parametrize(
    "now,promo",
    [
        ("2011-10-31 23:59:59", 0),
        ("2011-11-01 00:00:00", 100),
        ("2011-11-30 23:59:59", 100),
        ("2011-12-01 00:00:00", 0),
        ("2012-11-15 12:00:00", 100),  # 每年 11 月都適用
    ],
)
def test_PRM05_promo_period_is_whole_november(shop, now, promo):
    r = shop.create_order(1, "6:1", now=now)
    assert breakdown(shop, r)[2] == promo


# ---------------------------------------------------------------------------
# 運費
# ---------------------------------------------------------------------------

@pytest.mark.rule("R-SHP-01")
@pytest.mark.parametrize(
    "items,subtotal,shipping",
    [
        ("3:2,1:1,5:1,10:1", 990, 80),
        ("8:1,3:1", 1000, 0),
    ],
)
def test_SHP01_mainland_free_shipping_from_1000(shop, items, subtotal, shipping):
    r = shop.create_order(1, items)
    b = breakdown(shop, r)
    assert (b[0], b[3]) == (subtotal, shipping)


@pytest.mark.rule("R-SHP-02")
def test_SHP02_free_shipping_uses_subtotal_before_discount(shop):
    """免運看「折扣前」小計：VIP 1001 折完付 850，仍然免運"""
    r = shop.create_order(3, "9:1", zip="404")
    assert breakdown(shop, r)[3] == 0


@pytest.mark.rule("R-SHP-03")
@pytest.mark.parametrize(
    "member,zip,area",
    [
        (4, "880", "澎湖"),
        (4, "885", "澎湖"),
        (5, "890", "金門"),
        (5, "896", "金門"),
        (6, "209", "馬祖"),
        (6, "212", "馬祖"),
    ],
)
def test_SHP03_outlying_islands_always_250(shop, member, zip, area):
    """離島一律 250，滿千也不免運"""
    r = shop.create_order(member, "6:2", zip=zip)
    assert breakdown(shop, r)[3] == 250


@pytest.mark.rule("R-SHP-03")
@pytest.mark.parametrize("zip", ["879", "886", "889", "897", "208", "213"])
def test_SHP04_island_zip_boundaries(shop, zip):
    """郵遞區號範圍外就是本島"""
    r = shop.create_order(1, "1:1", zip=zip)
    assert breakdown(shop, r)[3] == 80


@pytest.mark.needs_decision("DEC-01")
@pytest.mark.parametrize("zip", ["951", "952"])
def test_SHP05_green_island_orchid_island_charged_as_mainland(shop, zip):
    """綠島（951）、蘭嶼（952）沒有列入離島 → 運費 80（是否為 Bug 待業務確認）"""
    r = shop.create_order(7, "1:1", zip=zip)
    assert breakdown(shop, r)[3] == 80


@pytest.mark.rule("R-SHP-04")
def test_SHP06_shipping_uses_order_zip_not_member_zip(shop):
    """運費看「這筆訂單的收件郵遞區號」，不是會員資料的地址：澎湖會員寄台北"""
    r = shop.create_order(4, "1:1", zip="100")
    assert breakdown(shop, r)[3] == 80


@pytest.mark.rule("R-SHP-05")
def test_SHP07_zip_with_5_digits_uses_first_3(shop):
    r = shop.create_order(4, "1:1", zip="88041")
    assert breakdown(shop, r)[3] == 250


# ---------------------------------------------------------------------------
# 綜合
# ---------------------------------------------------------------------------

@pytest.mark.rule("R-PRC-05")
def test_PRC06_all_rules_combined(shop):
    """金門 VIP、11 月：1200 - 180 - 100 + 250 = 1170"""
    r = shop.create_order(5, "2:1", zip="893", now=NOVEMBER_DAY)
    assert breakdown(shop, r) == (1200, 180, 100, 250, 1170)
    assert r.fields[2] == "1170"
