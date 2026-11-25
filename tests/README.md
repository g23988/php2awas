# 特性測試（Characterization Tests）

用黑箱方式記錄舊系統**現在的行為**，改寫成 AWS Serverless 後，拿同一套測試驗收新系統。

- 人看的測試案例、規則總表、Bug 清單：[TEST_CASES.md](TEST_CASES.md)
- 自動化測試：[characterization/](characterization/)（只需要 pytest，HTTP 用標準函式庫）

## 對舊系統執行

```bash
cd runtime && docker compose up -d --build   # 先啟動舊系統
cd ../tests/characterization
pip install -r requirements.txt
pytest -v                 # 全部（不含併發）
pytest -m race -s         # 併發超賣測試（結果不固定，最多重試 10 輪）
pytest -m legacy_bug -v   # 只看已知 Bug
```

每個測試開始前都會用 `docker compose exec db mariadb` 重新匯入 `legacy_demo/sql/*.sql`。

## 對新系統執行

```bash
TARGET=new \
BASE_URL=https://xxxx.execute-api.ap-northeast-1.amazonaws.com/Prod \
RESET_CMD="python ../../new_system/scripts/seed.py --reset" \
AUTO_CANCEL_CMD="aws lambda invoke --function-name <AutoCancelFn> /dev/stdout" \
MAIL_LOG= \
pytest -v
```

| 環境變數 | 說明 |
|---|---|
| `TARGET` | `legacy`（預設）或 `new`。設為 `new` 時，已知 Bug 的案例改用「修正後」的預期；`legacy_only` 和 `needs_decision` 的案例會略過 |
| `BASE_URL` | 舊 API 相容端點的根網址，測試會在後面接 `api/order/create` 這類路徑 |
| `RESET_CMD` | 每個測試前執行的資料重設指令。stdin 會收到舊系統的 SQL，新系統可以忽略，改載入自己的種子資料，但內容必須與 `02_seed_data.sql` 相同，包含「5 天前」「1 天前」這類相對時間 |
| `AUTO_CANCEL_CMD` | 觸發逾期自動取消的指令，stdout 最後一行必須是 `auto_cancel done: <筆數>`。沒設定時排程測試會略過 |
| `MAIL_LOG` | 寄信紀錄檔。新系統改用 SES，設成空字串就會略過寄信相關的斷言 |

**新系統必須支援的測試掛勾**

- **測試時鐘**：建單 API 要接受 `debug_now` 參數，而且**只能在測試環境**生效（舊系統在正式環境也生效，這是 SEC-10）
- **舊 API 相容**：路徑、`application/x-www-form-urlencoded` 輸入、`OK|...` / `ERR|代碼|訊息` 的純文字輸出

## 標記（marker）

| 標記 | 意義 |
|---|---|
| `rule("R-xxx")` | 對應 TEST_CASES.md 的商業規則 |
| `legacy_bug("BUG-xx")` | 已知 Bug，用 `expect(舊行為, 新行為)` 寫兩種預期 |
| `security("SEC-xx")` | 資安弱點 |
| `needs_decision("DEC-xx")` | 待業務決定 |
| `legacy_only` | 只有舊系統有的功能（前台購物網站、後台、電話訂購） |
| `mail` | 需要讀取寄信紀錄 |
| `race` | 併發測試，預設不執行 |

## 目前結果（舊系統）

```
TARGET=legacy  →  138 passed（另有 1 個 race 預設不跑）
TARGET=new     →  5 failed（正好是 5 個 API 層級的已知 Bug）, 75 passed, 58 skipped（前台、後台等舊系統限定的案例）
```

用 `TARGET=new` 對舊系統跑，紅燈的就是新系統必須修好的項目。
