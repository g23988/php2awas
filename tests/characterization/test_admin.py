"""
後台管理（舊系統限定）
新系統若要改寫後台，這些測試可作為功能清單參考
"""

import pytest

pytestmark = pytest.mark.legacy_only


def login(client, username="admin", password="admin123"):
    return client.post("admin/login", username=username, password=password)


@pytest.mark.rule("R-ADM-01")
def test_ADM01_login_success(client):
    r = login(client)
    assert r.status == 302
    assert r.location.endswith("/admin/orders")
    page = client.get("admin/orders")
    assert "您好，系統管理員" in page.text


@pytest.mark.rule("R-ADM-01")
def test_ADM02_login_wrong_password(client):
    r = login(client, password="wrong")
    assert r.status == 200
    assert "帳號或密碼錯誤" in r.text


@pytest.mark.rule("R-ADM-02")
@pytest.mark.parametrize("path", ["admin/orders", "admin/orders/detail/1", "admin/products"])
def test_ADM03_requires_login(client, path):
    r = client.get(path)
    assert r.status == 302
    assert r.location.endswith("/admin/login")


@pytest.mark.rule("R-ADM-03")
def test_ADM04_order_list_and_search(client):
    login(client)
    page = client.get("admin/orders")
    for order_no in ("200911150023", "201106250001", "201106260005"):
        assert order_no in page.text
    found = client.post("admin/orders/index/all", keyword="張雅婷")
    assert "201106260005" in found.text
    assert "200911150023" not in found.text


@pytest.mark.rule("R-ADM-03")
def test_ADM05_order_list_filter_by_status(client):
    login(client)
    page = client.get("admin/orders/index/9")
    assert "201106270004" in page.text
    assert "201106250001" not in page.text


@pytest.mark.mail
@pytest.mark.rule("R-ADM-04")
def test_ADM06_ship_paid_order(client, shop, mails):
    login(client)
    r = client.get("admin/orders/ship/4")
    assert r.status == 302
    assert shop.order("201106280003")["status"] == 2
    assert "出貨通知" in mails()[-1]["subject"]


@pytest.mark.rule("R-ADM-04")
def test_ADM07_cannot_ship_unpaid_order(client, shop):
    login(client)
    client.get("admin/orders/ship/3")
    assert shop.order("201106290002")["status"] == 0


@pytest.mark.rule("R-ADM-05")
def test_ADM08_update_stock(client, shop):
    login(client)
    r = client.post("admin/products/update", id="5", stock="30")
    assert r.status == 302
    assert shop.stock(5) == 30


@pytest.mark.rule("R-RPT-01")
def test_ADM09_daily_report_calculation(client, shop):
    """
    報表計算寫在 view 裡：
      業績 = 總計 - 運費；未稅 = round(業績 / 1.05)；達成率 = 業績 / 50000
    """
    login(client)
    r = shop.create_order(1, "1:1")  # 460（含運 80）
    shop.pay(r.fields[1], "460")
    shop.pay("201106290002", "1020")
    import datetime

    today = datetime.date.today().isoformat()
    page = client.get(f"admin/report/daily/{today}").text
    assert "合計 (2 筆)" in page
    assert "1,480" in page  # 訂單金額合計
    assert "1,400" in page  # 業績 = 1480 - 80
    assert "1,333" in page  # 未稅：round(380/1.05)=362 + round(1020/1.05)=971
    assert "2.8%" in page  # 1400 / 50000


@pytest.mark.rule("R-ADM-06")
def test_ADM10_list_selects_first_row_and_shows_info_box(client):
    """osCommerce 樣式：預設選第一筆，右側資訊框有 編輯／刪除／收據列印／發票列印"""
    login(client)
    page = client.get("admin/orders/index/all/3").text
    assert "[201106290002]" in page
    for label in ("編輯", "刪除", "收據列印", "發票列印", "訂單明細"):
        assert f'class="btnLink">{label}</a>' in page


@pytest.mark.rule("R-ADM-07")
def test_ADM11_edit_order(client, shop):
    login(client)
    r = client.post(
        "admin/orders/edit/3",
        receiver="林小豪", phone="0900", zip="404", address="新地址", note="改過", status="0",
    )
    assert r.status == 302
    page = client.get("admin/orders/index/all/3").text
    assert "訂單 201106290002 已更新" in page
    assert "林小豪" in page


@pytest.mark.rule("R-ADM-07")
def test_ADM12_edit_zip_does_not_recalculate_shipping(client, shop):
    """後台把收件地改成離島，運費和總額不會重算（還是 0 元運費）"""
    login(client)
    client.post(
        "admin/orders/edit/3",
        receiver="林志豪", phone="0912", zip="880", address="澎湖縣馬公市", note="", status="0",
    )
    o = shop.order("201106290002")
    assert (o["shipping"], o["total"]) == (0, 1020)


@pytest.mark.rule("R-ADM-08")
def test_ADM13_delete_order_does_not_restore_stock(client, shop):
    """刪除訂單是直接 DELETE，沒有加回庫存、也沒有留紀錄"""
    login(client)
    client.get("admin/orders/delete/3")
    assert shop.query("201106290002").code == "E11"
    assert shop.stock(2) == 50


@pytest.mark.rule("R-ADM-09")
def test_ADM14_receipt_print(client):
    login(client)
    page = client.get("admin/orders/receipt/4").text
    assert "出貨單／收據" in page
    assert "201106280003" in page
    assert "阿里山咖啡豆 1lb" in page
    assert "window.print()" in page


@pytest.mark.rule("R-ADM-09")
def test_ADM15_invoice_print_calculation(client):
    """
    發票計算寫在 view 裡：銷售額 = round(總計 / 1.05)、稅額 = 總計 - 銷售額
    發票號碼 = 寫死的字軌 AB + 訂單 id 補 0；金額轉中文大寫
    """
    login(client)
    page = client.get("admin/orders/invoice/4").text
    assert "AB-00000004" in page
    assert ">764<" in page  # round(802 / 1.05)
    assert ">38<" in page  # 802 - 764
    assert "捌佰零貳元整" in page


@pytest.mark.rule("R-ADM-10")
def test_ADM16_customer_list(client):
    login(client)
    page = client.get("admin/customers/index/3").text
    assert "chihhao.lin@example.com" in page
    assert "(停權)" in page  # 會員 8
    assert "訂單數：2 筆" in page  # 會員 3 的兩筆訂單
