"""
排程：逾期未付款自動取消  GET cron/auto_cancel
（新系統預期改用 EventBridge Scheduler 觸發，相容端點可視需要保留）
"""

import pytest


@pytest.mark.rule("R-CRN-01")
def test_CRN01_cancel_pending_older_than_3_days(shop):
    r = shop.auto_cancel()
    assert r.text.strip().endswith("auto_cancel done: 1")
    assert shop.order("201106250001")["status"] == 9  # 5 天前
    assert shop.order("201106290002")["status"] == 0  # 1 天前


@pytest.mark.rule("R-CRN-02")
def test_CRN02_auto_cancel_restores_stock(shop):
    """排程取消「有」把庫存加回去（和手動取消不一致，見 BUG-01）"""
    assert shop.stock(3) == 200
    shop.auto_cancel()
    assert shop.stock(3) == 202


@pytest.mark.rule("R-CRN-01")
def test_CRN03_paid_orders_untouched(shop):
    shop.auto_cancel()
    assert shop.order("201106280003")["status"] == 1


@pytest.mark.rule("R-CRN-01")
def test_CRN04_run_twice_is_safe(shop):
    shop.auto_cancel()
    r = shop.auto_cancel()
    assert r.text.strip().endswith("auto_cancel done: 0")
    assert shop.stock(3) == 202
