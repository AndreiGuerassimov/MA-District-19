/**
 * Keyboard and screen-reader checks for the navigation overlay.
 *
 * The plan relies on core's Navigation block for modal semantics, Escape, and
 * focus trapping rather than hand-rolling them. This script verifies that
 * claim against the rendered page instead of taking it on trust.
 *
 *   npm run a11y:nav
 */
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
process.env.PLAYWRIGHT_BROWSERS_PATH ||= path.join(ROOT, '.playwright');
const { chromium } = await import('playwright');

const URL_ = process.env.MA_SITE_URL || 'http://localhost:8888/matoronto/';
const results = [];
const check = (name, pass, detail = '') => results.push({ name, pass, detail });

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
await page.goto(URL_, { waitUntil: 'networkidle' });

const toggle = page.locator('.wp-block-navigation__responsive-container-open').first();

check('hamburger is visible at 390px', await toggle.isVisible());
check('hamburger has an accessible name',
  !!(await toggle.getAttribute('aria-label')) || !!(await toggle.innerText()).trim(),
  await toggle.getAttribute('aria-label') || '');
check('hamburger is a real <button>',
  (await toggle.evaluate((el) => el.tagName)) === 'BUTTON');

// Reach it by keyboard alone.
let hops = 0;
while (hops < 25 && !(await toggle.evaluate((el) => el === document.activeElement))) {
  await page.keyboard.press('Tab');
  hops++;
}
check('hamburger reachable by Tab', hops < 25, `${hops} tab stop(s)`);

await page.keyboard.press('Enter');
await page.waitForTimeout(400);

const overlay = page.locator('.wp-block-navigation__responsive-container.is-menu-open').first();
check('Enter opens the overlay', await overlay.isVisible());

// Core puts the modal semantics on an inner element, not the container.
const dialog = page.locator('.wp-block-navigation__responsive-dialog').first();
check('overlay has aria-modal="true"',
  (await dialog.getAttribute('aria-modal')) === 'true');
check('overlay has role="dialog"',
  (await dialog.getAttribute('role')) === 'dialog',
  await dialog.getAttribute('role') || 'none');

// A button that opens a MODAL dialog uses aria-haspopup, not aria-expanded --
// aria-expanded is the disclosure pattern, where content appears inline and
// focus stays put. Focus moves into this dialog, so haspopup is correct.
check('toggle declares aria-haspopup="dialog"',
  (await toggle.getAttribute('aria-haspopup')) === 'dialog',
  await toggle.getAttribute('aria-haspopup') || 'none');
check('focus moved into the overlay',
  await page.evaluate(() => {
    const o = document.querySelector('.wp-block-navigation__responsive-container.is-menu-open');
    return !!o && o.contains(document.activeElement);
  }));

// Tab well past the number of focusable items; focus must never escape.
let escaped = null;
for (let i = 0; i < 20; i++) {
  await page.keyboard.press('Tab');
  const inside = await page.evaluate(() => {
    const o = document.querySelector('.wp-block-navigation__responsive-container.is-menu-open');
    return !!o && o.contains(document.activeElement);
  });
  if (!inside) { escaped = i + 1; break; }
}
check('focus is trapped (20 forward tabs)', escaped === null,
  escaped ? `escaped after ${escaped} tabs` : 'never escaped');

let escapedBack = null;
for (let i = 0; i < 10; i++) {
  await page.keyboard.press('Shift+Tab');
  const inside = await page.evaluate(() => {
    const o = document.querySelector('.wp-block-navigation__responsive-container.is-menu-open');
    return !!o && o.contains(document.activeElement);
  });
  if (!inside) { escapedBack = i + 1; break; }
}
check('focus is trapped (10 Shift+Tab)', escapedBack === null,
  escapedBack ? `escaped after ${escapedBack}` : 'never escaped');

await page.keyboard.press('Escape');
await page.waitForTimeout(400);
check('Escape closes the overlay',
  !(await page.locator('.wp-block-navigation__responsive-container.is-menu-open').first().isVisible().catch(() => false)));
check('focus returns to the toggle',
  await toggle.evaluate((el) => el === document.activeElement));

// Focus visibility on the first nav link, at desktop width.
const desktop = await browser.newPage({ viewport: { width: 1440, height: 900 } });
await desktop.goto(URL_, { waitUntil: 'networkidle' });
const outline = await desktop.evaluate(() => {
  const a = document.querySelector('.wp-block-navigation-item__content');
  if (!a) return null;
  a.focus();
  const s = getComputedStyle(a);
  return { width: s.outlineWidth, style: s.outlineStyle, color: s.outlineColor };
});
check('nav link has a visible focus outline',
  !!outline && outline.style !== 'none' && parseFloat(outline.width) > 0,
  outline ? `${outline.width} ${outline.style} ${outline.color}` : 'no link found');

await browser.close();

const pad = Math.max(...results.map((r) => r.name.length));
let failed = 0;
for (const r of results) {
  if (!r.pass) failed++;
  console.log(`  ${r.pass ? 'PASS' : 'FAIL'}  ${r.name.padEnd(pad)}  ${r.detail}`);
}
console.log(`\n  ${results.length - failed}/${results.length} passed\n`);
process.exit(failed ? 1 : 0);
