import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 1512, height: 982 } });

await page.goto('http://localhost', { waitUntil: 'networkidle' });
await page.waitForTimeout(1200);
await page.screenshot({ path: '.tmp/screenshots/flow-welcome.png', fullPage: true });

await page.goto('http://localhost/egzaminy/wit', { waitUntil: 'networkidle' });
await page.waitForTimeout(1200);
await page.screenshot({ path: '.tmp/screenshots/flow-wit-list.png', fullPage: true });

await page.goto('http://localhost/egzaminy/udt', { waitUntil: 'networkidle' });
await page.waitForTimeout(1200);
await page.locator('.test-card').first().click();
await page.waitForTimeout(600);
await page.screenshot({ path: '.tmp/screenshots/flow-udt-modal.png', fullPage: true });

await browser.close();
