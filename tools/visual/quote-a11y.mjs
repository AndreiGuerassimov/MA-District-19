/**
 * Checks for the quote slider.
 *
 * Covers the specification in docs/quote-slider-scope.md section 6, plus the
 * claim the whole approach rests on: that the section still works with
 * JavaScript switched off.
 *
 *   npm run a11y:quote
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

// --- No JavaScript ------------------------------------------------------
const noJs = await browser.newContext({ javaScriptEnabled: false });
const bare = await noJs.newPage();
await bare.goto(URL_, { waitUntil: 'domcontentloaded' });
const bareState = await bare.evaluate(() => {
  const track = document.querySelector('.ma-quote__track');
  const slides = track ? track.querySelectorAll('.ma-quote__slide') : [];
  return {
    slides: slides.length,
    scrollable: track ? track.scrollWidth > track.clientWidth + 10 : false,
    deadButtons: document.querySelectorAll('.ma-quote__nav, .ma-quote__dot').length,
    textVisible: [...slides].every(s => s.getBoundingClientRect().width > 0),
  };
});
check('no-JS: all slides present', bareState.slides >= 2, `${bareState.slides} slides`);
check('no-JS: track is swipeable/scrollable', bareState.scrollable);
check('no-JS: no dead controls shipped', bareState.deadButtons === 0, `${bareState.deadButtons} found`);
check('no-JS: every slide has width', bareState.textVisible);
await noJs.close();

// --- With JavaScript ----------------------------------------------------
const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
await page.goto(URL_, { waitUntil: 'networkidle' });
await page.waitForTimeout(300);

const built = await page.evaluate(() => {
  const vp = document.querySelector('.ma-quote__viewport');
  const navs = [...document.querySelectorAll('.ma-quote__nav')];
  const dots = [...document.querySelectorAll('.ma-quote__dot')];
  const slides = [...document.querySelectorAll('.ma-quote__slide')];
  const box = (el) => { const r = el.getBoundingClientRect(); return [Math.round(r.width), Math.round(r.height)]; };
  return {
    hasViewport: !!vp,
    role: vp?.getAttribute('role'),
    roledesc: vp?.getAttribute('aria-roledescription'),
    labelled: !!vp?.getAttribute('aria-labelledby'),
    navCount: navs.length,
    navNames: navs.map(n => n.getAttribute('aria-label')),
    navTags: navs.map(n => n.tagName),
    navSizes: navs.map(box),
    dotCount: dots.length,
    dotNames: dots.map(d => d.getAttribute('aria-label')),
    dotSizes: dots.map(box),
    activeDots: dots.filter(d => d.getAttribute('aria-current') === 'true').length,
    slideRoles: slides.map(s => s.getAttribute('aria-roledescription')),
    slideLabels: slides.map(s => s.getAttribute('aria-label')),
    noLiveRegion: !document.querySelector('.ma-quote [aria-live]'),
  };
});

check('controls are built by JS', built.hasViewport && built.navCount === 2 && built.dotCount >= 2,
  `${built.navCount} nav, ${built.dotCount} dots`);
check('region has carousel semantics',
  built.role === 'group' && built.roledesc === 'carousel' && built.labelled,
  `role=${built.role} roledescription=${built.roledesc}`);
check('slides are labelled n of m',
  built.slideRoles.every(r => r === 'slide') && /^\d+ of \d+$/.test(built.slideLabels[0]),
  built.slideLabels.join(', '));
check('prev/next are real buttons with names',
  built.navTags.every(t => t === 'BUTTON') && built.navNames.every(Boolean),
  built.navNames.join(' / '));
check('prev/next meet 44px target', built.navSizes.every(([w, h]) => w >= 44 && h >= 44),
  JSON.stringify(built.navSizes));
check('dots meet 24px minimum target', built.dotSizes.every(([w, h]) => w >= 24 && h >= 24),
  JSON.stringify(built.dotSizes[0]));
check('dots are individually labelled', built.dotNames.every(n => /Go to quote \d+/.test(n)));
check('exactly one dot is aria-current', built.activeDots === 1, `${built.activeDots}`);
check('no aria-live region (nothing auto-updates)', built.noLiveRegion);

// Movement: next advances, and wraps at the end rather than dead-ending.
const total = built.dotCount;
const activeIndex = () => page.evaluate(() =>
  [...document.querySelectorAll('.ma-quote__dot')].findIndex(d => d.getAttribute('aria-current') === 'true'));

await page.locator('.ma-quote__nav').last().click();
await page.waitForTimeout(600);
check('next advances one slide', await activeIndex() === 1, `index ${await activeIndex()}`);

for (let i = 1; i < total; i++) {
  await page.locator('.ma-quote__nav').last().click();
  await page.waitForTimeout(500);
}
check('next wraps at the end (never dead)', await activeIndex() === 0, `index ${await activeIndex()}`);

await page.locator('.ma-quote__dot').nth(total - 1).click();
await page.waitForTimeout(600);
check('dot jumps to its slide', await activeIndex() === total - 1, `index ${await activeIndex()}`);

// Keyboard
await page.locator('.ma-quote__nav').first().focus();
await page.keyboard.press('ArrowLeft');
await page.waitForTimeout(600);
check('arrow keys move between slides', await activeIndex() === total - 2, `index ${await activeIndex()}`);

const focusRing = await page.evaluate(() => {
  const b = document.querySelector('.ma-quote__nav');
  b.focus();
  const s = getComputedStyle(b);
  return { w: s.outlineWidth, st: s.outlineStyle };
});
check('controls have a visible focus ring',
  focusRing.st !== 'none' && parseFloat(focusRing.w) > 0, `${focusRing.w} ${focusRing.st}`);

await browser.close();

const pad = Math.max(...results.map(r => r.name.length));
let failed = 0;
for (const r of results) {
  if (!r.pass) failed++;
  console.log(`  ${r.pass ? 'PASS' : 'FAIL'}  ${r.name.padEnd(pad)}  ${r.detail}`);
}
console.log(`\n  ${results.length - failed}/${results.length} passed\n`);
process.exit(failed ? 1 : 0);
