/**
 * Per-section fidelity.
 *
 * The whole-page diff conflates two very different things: whether a section
 * looks right, and whether it sits at the same vertical position as the
 * prototype. Ours drift downward because of deliberate changes (six cards
 * rather than five, the deferred "next meeting" card), so a global number
 * punishes every section below the first difference.
 *
 * This crops each band out of both pages and compares them independently, which
 * answers the question that actually matters: does this section look right?
 *
 *   npm run visual:sections
 */
import { PNG } from 'pngjs';
import pixelmatch from 'pixelmatch';
import path from 'node:path';
import fs from 'node:fs/promises';
import { fileURLToPath, pathToFileURL } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
process.env.PLAYWRIGHT_BROWSERS_PATH ||= path.join(ROOT, '.playwright');
const { chromium } = await import('playwright');

const OUT = path.join(ROOT, 'tools/visual/output/sections');
const SITE = process.env.MA_SITE_URL || 'http://localhost:8888/matoronto/';
const DESIGN = pathToFileURL(path.join(ROOT, 'design/Home-standalone.html')).href;
const WIDTH = 1280;

/** Walks down the left edge recording where the background colour changes. */
async function bandEdges(page) {
  return page.evaluate(() => {
    const h = document.documentElement.scrollHeight;
    const at = (y) => {
      let el = document.elementFromPoint(4, y - scrollY), c = 'rgba(0, 0, 0, 0)';
      while (el && c === 'rgba(0, 0, 0, 0)') { c = getComputedStyle(el).backgroundColor; el = el.parentElement; }
      return c;
    };
    const edges = []; let prev = null;
    for (let y = 0; y < h; y += 2) {
      window.scrollTo(0, Math.max(0, y - 300));
      const c = at(y);
      if (c !== prev) { edges.push(y); prev = c; }
    }
    window.scrollTo(0, 0);
    return { edges, total: h };
  });
}

function crop(png, top, bottom) {
  const height = Math.max(1, bottom - top);
  const out = new PNG({ width: png.width, height });
  PNG.bitblt(png, out, 0, top, png.width, height, 0, 0);
  return out;
}

function compare(a, b) {
  const width = Math.max(a.width, b.width);
  const height = Math.max(a.height, b.height);
  const pad = (src) => {
    if (src.width === width && src.height === height) return src;
    const dst = new PNG({ width, height });
    dst.data.fill(0);
    PNG.bitblt(src, dst, 0, 0, Math.min(src.width, width), Math.min(src.height, height), 0, 0);
    return dst;
  };
  const out = new PNG({ width, height });
  const changed = pixelmatch(pad(a).data, pad(b).data, out.data, width, height,
    { threshold: 0.12, includeAA: false, alpha: 0.25 });
  return { pct: (changed / (width * height)) * 100, out, height };
}

await fs.mkdir(OUT, { recursive: true });
const browser = await chromium.launch();

const sitePage = await browser.newPage({ viewport: { width: WIDTH, height: 900 } });
await sitePage.goto(SITE, { waitUntil: 'networkidle' });
const siteBands = await bandEdges(sitePage);
const siteShot = PNG.sync.read(await sitePage.screenshot({ fullPage: true }));

const designPage = await browser.newPage({ viewport: { width: WIDTH, height: 900 } });
await designPage.goto(DESIGN, { waitUntil: 'networkidle' });
await designPage.waitForTimeout(800);
const designBands = await bandEdges(designPage);
const designShot = PNG.sync.read(await designPage.screenshot({ fullPage: true }));

await browser.close();

// Both pages share the same band sequence: header/hero (cream), cards
// (surface), about (forest), quote (cream), footer (deep green).
const names = ['header + hero', 'pathway cards', 'about band', 'quote slider', 'footer'];
const bounds = (b, total) => {
  const e = b.filter((y, i, arr) => i === 0 || y !== arr[i - 1]);
  return [...e, total];
};
const s = bounds(siteBands.edges, siteBands.total);
const d = bounds(designBands.edges, designBands.total);

console.log(`\nPer-section fidelity at ${WIDTH}px (vertical drift removed)\n`);
console.log('section            ours    design   height Δ   differing');
console.log('-'.repeat(62));

for (let i = 0; i < names.length; i++) {
  if (s[i] === undefined || d[i] === undefined) break;
  const a = crop(siteShot, s[i], s[i + 1] ?? siteBands.total);
  const b = crop(designShot, d[i], d[i + 1] ?? designBands.total);
  const { pct, out } = compare(a, b);
  await fs.writeFile(path.join(OUT, `${i}-${names[i].replace(/\W+/g, '-')}.png`), PNG.sync.write(out));
  console.log(
    `${names[i].padEnd(17)} ${String(a.height).padStart(5)}px ${String(b.height).padStart(8)}px ` +
    `${String(a.height - b.height).padStart(8)}   ${pct.toFixed(1).padStart(6)}%`
  );
}
console.log(`\ncrops written to tools/visual/output/sections/\n`);
