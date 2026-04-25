import { chromium, devices } from 'playwright';

const baseUrl = process.env.VISUAL_BASE_URL ?? 'http://localhost';

const run = async () => {
  const browser = await chromium.launch({ headless: true });

  const desktop = await browser.newContext({ viewport: { width: 1728, height: 1117 } });
  const desktopPage = await desktop.newPage();
  await desktopPage.goto(baseUrl, { waitUntil: 'networkidle' });
  await desktopPage.waitForTimeout(1200);
  await desktopPage.screenshot({ path: '.tmp/screenshots/desktop.png', fullPage: true });
  await desktop.close();

  const mobile = await browser.newContext({ ...devices['iPhone 13'] });
  const mobilePage = await mobile.newPage();
  await mobilePage.goto(baseUrl, { waitUntil: 'networkidle' });
  await mobilePage.waitForTimeout(1200);
  await mobilePage.screenshot({ path: '.tmp/screenshots/mobile.png', fullPage: true });
  await mobile.close();

  await browser.close();
};

run();
