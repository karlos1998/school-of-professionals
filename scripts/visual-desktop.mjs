import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 1728, height: 1117 } });
await page.goto('http://localhost', { waitUntil: 'domcontentloaded' });
await page.waitForSelector('.v-application', { timeout: 15000 });
await page.waitForTimeout(2000);
await page.screenshot({ path: '.tmp/screenshots/desktop.png', fullPage: true });
await browser.close();
