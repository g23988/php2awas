// 舊系統截圖：node shoot.js <輸出資料夾>
// 需要舊系統在 http://localhost:8080 執行中（cd runtime && docker compose up -d）
const puppeteer = require('puppeteer-core');
const path = require('path');

const OUT = process.argv[2] || path.join(__dirname, '..', '..', 'docs', 'screenshots');
const B = 'http://localhost:8080/index.php';
const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

(async () => {
  const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, args: ['--lang=zh-TW'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1024, height: 768, deviceScaleFactor: 1 });
  page.on('dialog', d => d.accept());

  const shot = async (name, full = true) => {
    await new Promise(r => setTimeout(r, 300));
    await page.screenshot({ path: path.join(OUT, name), fullPage: full });
    console.log('saved', name);
  };
  const go = url => page.goto(B + url, { waitUntil: 'networkidle0' });

  // ---------------- 前台 ----------------
  await go('/shop');
  await shot('01_shop_home.png');

  await go('/shop/login');
  await page.type('input[name=email]', 'chihhao.lin@example.com');
  await page.type('input[name=password]', '123456');
  await Promise.all([page.waitForNavigation(), page.click('input[type=submit]')]);
  for (const id of [9, 1, 3, 5]) await go('/shop/add/' + id);
  await go('/shop/later/5');
  await go('/shop/cart');
  await shot('02_shop_cart.png');

  await go('/shop/checkout');
  await shot('03_shop_checkout.png');
  await Promise.all([page.waitForNavigation(), page.click('input[name=submitBtn]')]);
  await shot('04_shop_done.png');

  // ---------------- 後台 ----------------
  await go('/admin/login');
  await shot('05_admin_login.png', false);
  await page.type('input[name=username]', 'admin');
  await page.type('input[name=password]', 'admin123');
  await Promise.all([page.waitForNavigation(), page.click('input[type=submit]')]);

  await go('/admin/orders/index/all/3');
  await shot('06_admin_orders.png');

  await go('/admin/customers/index/3');
  await shot('07_admin_customers.png');

  // 電話訂購（仿 ERP）
  await go('/admin/phone_order');
  await page.type('#member_id', '5');
  await page.evaluate(() => lookupMember());
  await page.waitForFunction(() => document.getElementById('member_name').innerHTML !== '');
  const lines = [['P002', '2'], ['P004', '1'], ['P009', '1']];
  for (let i = 0; i < lines.length; i++) {
    await page.evaluate((n, sku, qty) => {
      document.getElementById('sku' + n).value = sku;
      document.getElementById('qty' + n).value = qty;
      lookupProduct(n);
    }, i + 1, lines[i][0], lines[i][1]);
  }
  await page.type('input[name=po_no]', 'PO-20111115-07');
  await shot('08_admin_phone_order.png', false);

  await go('/admin/orders/invoice/4');
  await shot('09_admin_invoice.png');

  await go('/admin/orders/receipt/4');
  await shot('10_admin_receipt.png');

  const today = new Date(Date.now() + 8 * 3600 * 1000).toISOString().slice(0, 10);
  await go('/admin/report/daily/' + today);
  await shot('11_admin_report.png');

  await go('/test');
  await shot('12_ci_unit_test.png');

  await browser.close();
})();
