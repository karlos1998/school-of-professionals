import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();

page.on('console', (msg) => console.log('[console]', msg.type(), msg.text()));
page.on('pageerror', (err) => console.log('[pageerror]', err.message));
page.on('requestfailed', (req) => console.log('[failed]', req.url(), req.failure()?.errorText));

await page.goto('http://localhost', { waitUntil: 'networkidle' });
await page.waitForTimeout(2000);

console.log('title=', await page.title());
await browser.close();
