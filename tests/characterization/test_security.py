"""
資安弱點：證明「舊系統真的有這個洞」，並作為新系統的回歸測試
"""

import pytest

from conftest import expect


@pytest.mark.security("SEC-01")
@pytest.mark.legacy_only
def test_SEC01_admin_login_sql_injection(client):
    """帳號欄位 SQL Injection 可直接登入後台"""
    r = client.post("admin/login", username="x' OR 1=1 -- ", password="anything")
    assert r.status == 302
    assert r.location.endswith("/admin/orders")


@pytest.mark.security("SEC-02")
@pytest.mark.legacy_only
def test_SEC02_backdoor_master_password(client):
    """任何帳號 + 萬用密碼都能以管理員身分登入"""
    r = client.post("admin/login", username="nobody", password="kevin0912")
    assert r.status == 302
    assert "系統管理員" in client.get("admin/orders").text


@pytest.mark.security("SEC-03")
@pytest.mark.legacy_only
def test_SEC03_report_without_login(client):
    """營業報表忘了檢查登入"""
    r = client.get("admin/report")
    assert r.status == 200
    assert "每日營業報表" in r.text


@pytest.mark.security("SEC-04")
@pytest.mark.legacy_bug("BUG-08")
def test_SEC04_cancel_other_members_order_via_sql_injection(shop):
    """member_id 沒過濾：用 1 OR member_id=3 就能取消別人的訂單"""
    r = shop.cancel("x", "0 OR member_id=3")
    assert r.fields[0] == expect("OK", "ERR")
    assert shop.order("201106290002")["status"] == expect(9, 0)


@pytest.mark.security("SEC-06")
@pytest.mark.legacy_only
def test_SEC06_stored_xss_in_admin_order_list(client, shop):
    """訂單備註原樣輸出到後台頁面"""
    shop.create_order(1, "1:1", note="<script>alert(1)</script>")
    client.post("admin/login", username="admin", password="admin123")
    assert "<script>alert(1)</script>" in client.get("admin/orders").text


@pytest.mark.security("SEC-07")
@pytest.mark.legacy_only
def test_SEC07_unit_test_page_exposed(client):
    """CI 單元測試頁面在正式環境可直接存取（而且有一條測試早就失敗）"""
    r = client.get("test")
    assert r.status == 200
    assert "Failed" in r.text


@pytest.mark.security("SEC-08")
@pytest.mark.legacy_only
def test_SEC08_cron_endpoint_public(shop):
    """排程網址沒有任何保護，任何人都能觸發"""
    r = shop.auto_cancel()
    assert "auto_cancel done" in r.text
