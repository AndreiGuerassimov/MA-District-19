/**
 * Responsive audit.
 *
 * The prototypes contain no responsive CSS at all, so every breakpoint below
 * 1280px is our own design rather than a translation. This checks the things
 * that actually break at narrow widths.
 *
 *   npm run audit:responsive
 */
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
process.env.PLAYWRIGHT_BROWSERS_PATH ||= path.join(ROOT, '.playwright');
const { chromium } = await import('playwright');

const URL_ = process.env.MA_SITE_URL || 'http://localhost:8888/matoronto/';
const WIDTHS = [1920, 1440, 1280, 1100, 1024, 900, 768, 600, 480, 390, 320];

const browser = await chromium.launch();
let problems = 0;

console.log('\n width  overflow  min font  small targets  clipped  notes');
console.log('-'.repeat(72));

for (const width of WIDTHS) {
  const page = await browser.newPage({ viewport: { width, height: 900 } });
  await page.goto(URL_, { waitUntil: 'networkidle' });
  await page.waitForTimeout(200);

  const r = await page.evaluate((vw) => {
    const doc = document.documentElement;
    const overflow = Math.max(0, doc.scrollWidth - vw);

    // Anything sticking out past the viewport edge. Content inside a
    // horizontal scroll container (the quote track) is deliberately offscreen,
    // so it is not clipping.
    const inScroller = (el) => {
      for (let p = el.parentElement; p; p = p.parentElement) {
        const ox = getComputedStyle(p).overflowX;
        if (ox === 'auto' || ox === 'scroll' || ox === 'hidden') return true;
      }
      return false;
    };
    const clipped = [...document.querySelectorAll('body *')]
      .filter((el) => {
        const b = el.getBoundingClientRect();
        return b.width > 0 && b.right > vw + 1 && !inScroller(el);
      })
      .slice(0, 3)
      .map((el) => `${el.tagName.toLowerCase()}.${String(el.className).split(' ')[0]}`);

    // Smallest rendered text, ignoring empty nodes.
    const sizes = [...document.querySelectorAll('p, a, li, h1, h2, h3, span, button')]
      .filter((el) => el.textContent.trim() && el.getBoundingClientRect().width > 0)
      .map((el) => parseFloat(getComputedStyle(el).fontSize));
    const minFont = sizes.length ? Math.min(...sizes) : null;

    // Interactive targets under the 24x24 minimum (WCAG 2.5.8).
    //
    // Two exemptions, both legitimate:
    // - a link whose ::after is absolutely positioned over an ancestor (the
    //   stretched-link pattern) has that ancestor as its real target;
    // - a link hidden until focused, like the skip link, is full size when it
    //   matters.
    const stretched = (el) => {
      const a = getComputedStyle(el, '::after');
      return a.position === 'absolute' && a.content !== 'none' && a.inset === '0px';
    };
    const small = [...document.querySelectorAll('a, button')]
      .filter((el) => {
        const b = el.getBoundingClientRect();
        if (b.width === 0) return false;
        if (b.width <= 1 && b.height <= 1) return false;
        if (stretched(el)) return false;
        return b.width < 24 || b.height < 24;
      })
      .map((el) => `${el.textContent.trim().slice(0, 18)} ${Math.round(el.getBoundingClientRect().width)}x${Math.round(el.getBoundingClientRect().height)}`);

    return { overflow, clipped, minFont, small };
  }, width);

  const flag = r.overflow > 0 || r.small.length > 0 || (r.minFont && r.minFont < 11);
  if (flag) problems++;

  console.log(
    `${String(width).padStart(5)}  ${String(r.overflow ? r.overflow + 'px' : 'none').padStart(8)}` +
    `  ${String(r.minFont ? r.minFont.toFixed(0) + 'px' : '-').padStart(8)}` +
    `  ${String(r.small.length).padStart(13)}  ${String(r.clipped.length).padStart(7)}  ${r.small.slice(0, 2).concat(r.clipped).join(', ')}`
  );
  await page.close();
}

await browser.close();
console.log(`\n${problems === 0 ? 'No layout problems found.' : problems + ' width(s) flagged.'}\n`);
