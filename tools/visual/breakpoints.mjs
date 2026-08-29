/**
 * Reports where the navigation overlay takes over, by measuring the rendered
 * page rather than reading the CSS.
 *
 * Core hardcodes the overlay threshold at 600px in the Navigation block's
 * stylesheet; this theme moves it to 1100px in assets/css/blocks/
 * core-navigation.css. This script is how we check that actually happened.
 *
 *   npm run breakpoints
 */
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
process.env.PLAYWRIGHT_BROWSERS_PATH ||= path.join(ROOT, '.playwright');
const { chromium } = await import('playwright');

const URL_ = process.env.MA_SITE_URL || 'http://localhost:8888/matoronto/';
const WIDTHS = [1440, 1200, 1150, 1101, 1099, 1000, 800, 601, 599, 390];

const browser = await chromium.launch();
console.log(`\n${URL_}\n`);
console.log(' width   hamburger   inline menu   verdict');
console.log(' '.repeat(2) + '-'.repeat(48));

let expectedFlip = 1100;
for (const width of WIDTHS) {
  const page = await browser.newPage({ viewport: { width, height: 800 } });
  await page.goto(URL_, { waitUntil: 'networkidle' });

  const burger = await page
    .locator('.wp-block-navigation__responsive-container-open')
    .first().isVisible().catch(() => false);
  const inline = await page
    .locator('.wp-block-navigation__responsive-container:not(.is-menu-open) .wp-block-navigation__container')
    .first().isVisible().catch(() => false);

  const wantOverlay = width < expectedFlip;
  const ok = wantOverlay ? burger && !inline : inline && !burger;
  console.log(
    ` ${String(width).padStart(5)}   ${burger ? 'shown' : '  -  '}       ` +
    `${inline ? 'shown' : '  -  '}         ${ok ? 'ok' : 'UNEXPECTED'}`
  );
  await page.close();
}
await browser.close();
console.log(`\n Expected flip at ${expectedFlip}px (settings.viewport.tablet).\n`);
