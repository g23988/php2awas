"""
特性測試（Characterization Tests）共用設定

目的：用黑箱方式「記錄」舊系統現在的行為，改寫後拿同一套測試驗證新系統。

環境變數：
  BASE_URL   API 根網址（預設 http://localhost:8080/index.php）
             新系統的舊 API 相容端點，例如 https://xxxx.execute-api.ap-northeast-1.amazonaws.com/Prod
  TARGET     legacy（預設）或 new
             - legacy：已知 Bug 的測試斷言「舊行為」
             - new   ：已知 Bug 的測試斷言「修正後行為」；legacy_only 測試會略過
  RESET_CMD  每個測試前重設資料的指令（stdin 會收到 schema + seed SQL）
             預設為 docker compose 對舊系統的 MariaDB 執行
  MAIL_LOG   寄信紀錄檔（預設 runtime/mail-out/mail.log）；設為空字串則略過寄信相關斷言
  AUTO_CANCEL_CMD
             觸發「逾期自動取消」的指令（TARGET=new 時使用，例如 aws lambda invoke ...）
             指令的 stdout 最後一行必須是 "auto_cancel done: <筆數>"；未設定則略過排程測試
"""

import email
import http.cookiejar
import os
import re
import subprocess
import urllib.error
import urllib.parse
import urllib.request
from email.header import decode_header
from pathlib import Path

import pytest

ROOT = Path(__file__).resolve().parents[2]

BASE_URL = os.environ.get("BASE_URL", "http://localhost:8080/index.php").rstrip("/")
TARGET = os.environ.get("TARGET", "legacy").lower()
MAIL_LOG = os.environ.get("MAIL_LOG", str(ROOT / "runtime" / "mail-out" / "mail.log"))
SQL_FILES = [ROOT / "legacy_demo" / "sql" / "01_schema.sql", ROOT / "legacy_demo" / "sql" / "02_seed_data.sql"]
DEFAULT_RESET_CMD = (
    f'docker compose -f "{ROOT / "runtime" / "docker-compose.yml"}" '
    "exec -T db mariadb -uroot -pshop@1234"
)
RESET_CMD = os.environ.get("RESET_CMD", DEFAULT_RESET_CMD)
AUTO_CANCEL_CMD = os.environ.get("AUTO_CANCEL_CMD", "")

# 非 11 月的固定時間，避免測試結果受「今天幾月」影響
NORMAL_DAY = "2011-06-15 10:00:00"
NOVEMBER_DAY = "2011-11-10 10:00:00"

PAY_KEY = "Pay2009#Key"


def is_legacy():
    return TARGET == "legacy"


def expect(legacy, fixed):
    """已知 Bug：舊系統斷言 legacy，新系統斷言 fixed"""
    return legacy if is_legacy() else fixed


# ---------------------------------------------------------------------------
# pytest hooks
# ---------------------------------------------------------------------------

def pytest_configure(config):
    config.addinivalue_line("markers", "rule(id): 對應的商業規則編號")
    config.addinivalue_line("markers", "legacy_bug(id): 已知 Bug，新系統必須修正（用 expect() 寫兩種預期）")
    config.addinivalue_line("markers", "security(id): 資安弱點")
    config.addinivalue_line("markers", "needs_decision(id): 行為可疑，需業務決定是否保留；TARGET=new 時略過")
    config.addinivalue_line("markers", "legacy_only: 只針對舊系統（後台頁面、舊排程等），TARGET=new 時略過")
    config.addinivalue_line("markers", "mail: 需要檢查寄信紀錄")
    config.addinivalue_line("markers", "race: 併發測試，結果不固定，預設不執行（-m race 才跑）")


def pytest_collection_modifyitems(config, items):
    for item in items:
        if not is_legacy():
            if item.get_closest_marker("legacy_only"):
                item.add_marker(pytest.mark.skip(reason="legacy_only：新系統不提供此功能"))
            if item.get_closest_marker("needs_decision"):
                item.add_marker(pytest.mark.skip(reason="needs_decision：待業務決定新系統行為"))
        if item.get_closest_marker("mail") and not MAIL_LOG:
            item.add_marker(pytest.mark.skip(reason="未設定 MAIL_LOG"))


def pytest_report_header(config):
    return [f"TARGET={TARGET}  BASE_URL={BASE_URL}", f"MAIL_LOG={MAIL_LOG or '(skip)'}"]


# ---------------------------------------------------------------------------
# 資料重設
# ---------------------------------------------------------------------------

def reset_data():
    sql = b"\n".join(p.read_bytes() for p in SQL_FILES)
    r = subprocess.run(RESET_CMD, shell=True, input=sql, capture_output=True)
    if r.returncode != 0:
        raise RuntimeError("資料重設失敗：\n" + r.stderr.decode("utf-8", "replace"))
    if MAIL_LOG:
        Path(MAIL_LOG).parent.mkdir(parents=True, exist_ok=True)
        Path(MAIL_LOG).write_text("")


@pytest.fixture(autouse=True)
def fresh_data():
    reset_data()
    yield


# ---------------------------------------------------------------------------
# HTTP client（只用標準函式庫）
# ---------------------------------------------------------------------------

class _NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


class Response:
    def __init__(self, status, headers, body):
        self.status = status
        self.headers = headers
        self.text = body
        self.location = headers.get("Location", "")

    # 舊 API 的回傳格式：OK|a|b|c 或 ERR|代碼|訊息
    @property
    def fields(self):
        return self.text.strip().split("|")

    @property
    def ok(self):
        return self.fields[0] in ("OK", "1")

    @property
    def code(self):
        return self.fields[1] if len(self.fields) > 1 else ""

    def __repr__(self):
        return f"<Response {self.status} {self.text[:120]!r}>"


class Client:
    def __init__(self):
        self.jar = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(
            urllib.request.HTTPCookieProcessor(self.jar), _NoRedirect()
        )

    def _send(self, path, data=None):
        url = BASE_URL + "/" + path.lstrip("/")
        body = None
        if data is not None:
            body = urllib.parse.urlencode(data).encode("utf-8")
        req = urllib.request.Request(url, data=body, headers={"User-Agent": "characterization-test"})
        try:
            with self.opener.open(req, timeout=30) as r:
                return Response(r.status, r.headers, r.read().decode("utf-8", "replace"))
        except urllib.error.HTTPError as e:
            return Response(e.code, e.headers, e.read().decode("utf-8", "replace"))

    def get(self, path):
        return self._send(path)

    def post(self, path, **data):
        return self._send(path, data)

    def post_pairs(self, path, pairs):
        """同名欄位（例如 sku[]、qty[]）用 list of tuples 送"""
        return self._send(path, pairs)


@pytest.fixture
def client():
    return Client()


# ---------------------------------------------------------------------------
# 業務操作捷徑
# ---------------------------------------------------------------------------

class Shop:
    """把舊 API 包成語意化的操作，測試讀起來比較像規格"""

    def __init__(self, client):
        self.c = client

    def create_order(self, member_id, items, zip="100", now=NORMAL_DAY, **extra):
        data = {
            "member_id": member_id,
            "items": items,
            "receiver": "測試收件人",
            "phone": "0912-345-678",
            "zip": zip,
            "address": "測試地址 1 號",
            "note": "",
            "debug_now": now,
        }
        data.update(extra)
        return self.c.post("api/order/create", **data)

    def query(self, order_no):
        return self.c.get(f"api/order/query/{order_no}")

    def order(self, order_no):
        """查詢並轉成 dict"""
        r = self.query(order_no)
        assert r.fields[0] == "OK", r
        keys = ["order_no", "status", "subtotal", "discount", "promo_discount", "shipping", "total", "created_at"]
        d = dict(zip(keys, r.fields[1:]))
        for k in ("status", "subtotal", "discount", "promo_discount", "shipping", "total"):
            d[k] = int(d[k])
        return d

    def cancel(self, order_no, member_id):
        return self.c.post("api/order/cancel", order_no=order_no, member_id=member_id)

    def stock(self, product_id):
        r = self.c.get(f"api/product/stock/{product_id}")
        assert r.fields[0] == "OK", r
        return int(r.fields[4])

    def pay(self, order_no, amount, chk="auto"):
        if chk == "auto":
            chk = pay_checksum(order_no, amount)
        return self.c.post("api/payment/notify", order_no=order_no, amount=amount, chk=chk)

    def auto_cancel(self):
        if is_legacy():
            return self.c.get("cron/auto_cancel")
        # 新系統不對外開放排程網址，改用指令觸發（例如 aws lambda invoke）
        if not AUTO_CANCEL_CMD:
            pytest.skip("未設定 AUTO_CANCEL_CMD")
        r = subprocess.run(AUTO_CANCEL_CMD, shell=True, capture_output=True, text=True)
        assert r.returncode == 0, r.stderr
        return Response(200, {}, r.stdout.strip().splitlines()[-1] if r.stdout.strip() else "")


def pay_checksum(order_no, amount):
    import hashlib

    return hashlib.md5(f"{order_no}{amount}{PAY_KEY}".encode()).hexdigest()


@pytest.fixture
def shop(client):
    return Shop(client)


# ---------------------------------------------------------------------------
# 寄信紀錄
# ---------------------------------------------------------------------------

def _decode(value):
    parts = []
    for text, enc in decode_header(value or ""):
        parts.append(text.decode(enc or "utf-8") if isinstance(text, bytes) else text)
    return "".join(parts)


def read_mails():
    if not MAIL_LOG or not Path(MAIL_LOG).exists():
        return []
    raw = Path(MAIL_LOG).read_text(encoding="utf-8", errors="replace")
    mails = []
    for chunk in raw.split("=====MAIL=====\n"):
        if not chunk.strip():
            continue
        msg = email.message_from_string(chunk)
        mails.append(
            {
                "to": msg.get("To", ""),
                "subject": _decode(msg.get("Subject", "")),
                "body": msg.get_payload(),
            }
        )
    return mails


@pytest.fixture
def mails():
    return read_mails


ORDER_NO_RE = re.compile(r"^\d{12}$")
