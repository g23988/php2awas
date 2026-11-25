# 截圖工具

用本機的 Google Chrome，以 1024x768（當年的「最佳解析度」）自動操作舊系統並截圖，輸出到 `docs/screenshots/`。

```bash
cd runtime && docker compose up -d --build
cd ../tools/screenshots
npm install
npm run shoot
```

- Chrome 路徑寫死為 macOS 的 `/Applications/Google Chrome.app/...`，其他平台請修改 `shoot.js` 的 `CHROME`
- 腳本會實際下單（前台結帳），截圖前建議先重設資料庫，並用 API 多建幾筆訂單，讓列表比較豐富
