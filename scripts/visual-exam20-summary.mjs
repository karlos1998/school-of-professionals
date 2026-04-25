import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 430, height: 932 } });

await page.goto('http://localhost/egzaminy/wit/maszyny-drogowe/tryb/exam20', { waitUntil: 'networkidle' });

for (let i = 0; i < 20; i += 1) {
  const firstRadio = page.locator('.v-radio').first();
  await firstRadio.click();
  if (i < 19) {
    await page.getByRole('button', { name: 'Nastepne' }).click();
  }
}

await page.waitForTimeout(800);
await page.screenshot({ path: '.tmp/screenshots/exam20-summary-mobile.png', fullPage: true });
await browser.close();
