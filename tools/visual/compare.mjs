/**
 * Visual fidelity harness for the MA Toronto block theme.
 *
 * Screenshots the rendered WordPress homepage and the design prototype at the
 * same widths, then diffs them pixel-by-pixel.
 *
 *   node tools/visual/compare.mjs                 # capture both, diff
 *   node tools/visual/compare.mjs --only=site     # capture the site only
 *   node tools/visual/compare.mjs --only=design   # capture the prototype only
 *   node tools/visual/compare.mjs --width=1440    # single width
 *
 * IMPORTANT: 1440px is the real fidelity check. The 390px pass is a
 * self-regression baseline only -- the prototypes contain no responsive CSS
 * (no @media, no clamp, no minmax), so at 390px the prototype renders a
 * squashed desktop layout that we are deliberately NOT reproducing.
 */
import { PNG } from 'pngjs';
import pixelmatch from 'pixelmatch';
import { fileURLToPath, pathToFileURL } from 'node:url';
import path from 'node:path';
import fs from 'node:fs/promises';
import { existsSync } from 'node:fs';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');

// Keep the browser download inside the repo (and out of git) rather than in the
// shared ~/Library cache, so this harness is self-contained. This must be set
// before playwright is loaded -- hence the dynamic import below, since static
// ESM imports are hoisted above any statement in this file.
process.env.PLAYWRIGHT_BROWSERS_PATH ||= path.join(ROOT, '.playwright');
const { chromium } = await import('playwright');
const OUT = path.join(ROOT, 'tools/visual/output');

const SITE_URL = process.env.MA_SITE_URL || 'http://localhost:8888/matoronto/';
// The bundled standalone build renders without needing ./support.js resolved
// relative to the page; fall back to the .dc.html source if it is absent.
const DESIGN_FILE = ['design/Home-standalone.html', 'design/Home.dc.html']
  .map((p) => path.join(ROOT, p))
  .find((p) => existsSync(p));

const WIDTHS = [
  // 1280 is the prototype's native design width, and the only width where a
  // like-for-like diff is meaningful: our content box (wideSize 1184px) equals
  // the prototype's there (1280 - 2x48px gutter).
  { name: '1280', width: 1280, height: 900, role: 'FIDELITY CHECK - the meaningful one' },
  // The prototype has no max-width, so above 1280 it keeps stretching while we
  // cap at 1184px. A large diff here is expected and correct.
  { name: '1440', width: 1440, height: 900, role: 'expected to differ - prototype has no max-width' },
  // The prototype has no responsive CSS at all; at 390px it renders a squashed
  // desktop layout we are deliberately not reproducing.
  { name: '390', width: 390, height: 844, role: 'self-regression baseline only' },
];

const args = Object.fromEntries(
  process.argv.slice(2).map((a) => {
    const [k, v] = a.replace(/^--/, '').split('=');
    return [k, v ?? true];
  })
);

/** Wait for fonts, images and any client-side rendering to settle. */
async function settle(page) {
  await page.waitForLoadState('networkidle').catch(() => {});
  await page.evaluate(() => document.fonts?.ready).catch(() => {});
  // The prototypes render client-side via support.js; give it a beat to paint.
  await page.waitForTimeout(600);
  await page.evaluate(async () => {
    await Promise.all(
      Array.from(document.images)
        .filter((img) => !img.complete)
        .map((img) => new Promise((res) => { img.onload = img.onerror = res; }))
    );
  });
}

async function capture(browser, target, url, vp) {
  const page = await browser.newPage({
    viewport: { width: vp.width, height: vp.height },
    deviceScaleFactor: 1,
  });
  const file = path.join(OUT, `${target}-${vp.name}.png`);
  try {
    const res = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30_000 });
    if (res && !res.ok() && url.startsWith('http')) {
      throw new Error(`HTTP ${res.status()} from ${url}`);
    }
    await settle(page);
    await page.screenshot({ path: file, fullPage: true });
    const { width, height } = PNG.sync.read(await fs.readFile(file));
    console.log(`  ${target.padEnd(6)} ${vp.name.padStart(4)}px  ->  ${path.relative(ROOT, file)}  (${width}x${height})`);
    return file;
  } finally {
    await page.close();
  }
}

/** Diff two PNGs, padding to a common canvas so differing heights still compare. */
async function diff(aPath, bPath, vp) {
  const a = PNG.sync.read(await fs.readFile(aPath));
  const b = PNG.sync.read(await fs.readFile(bPath));
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
  const changed = pixelmatch(
    pad(a).data, pad(b).data, out.data, width, height,
    { threshold: 0.12, includeAA: false, alpha: 0.25 }
  );
  const file = path.join(OUT, `diff-${vp.name}.png`);
  await fs.writeFile(file, PNG.sync.write(out));

  const pct = (changed / (width * height)) * 100;
  const heightNote = a.height !== b.height
    ? `  [heights differ: site ${a.height} vs design ${b.height}]` : '';
  console.log(
    `  ${vp.name.padStart(4)}px  ${pct.toFixed(2).padStart(6)}% differing  ` +
    `(${changed.toLocaleString()} px)  ${vp.role}${heightNote}`
  );
  console.log(`          -> ${path.relative(ROOT, file)}`);
  return pct;
}

(async () => {
  if (!DESIGN_FILE) {
    console.error('No prototype found at design/Home-standalone.html or design/Home.dc.html');
    process.exit(1);
  }
  await fs.mkdir(OUT, { recursive: true });

  const widths = args.width ? WIDTHS.filter((w) => w.name === String(args.width)) : WIDTHS;
  if (!widths.length) {
    console.error(`Unknown --width. Available: ${WIDTHS.map((w) => w.name).join(', ')}`);
    process.exit(1);
  }

  const browser = await chromium.launch();
  const captured = {};
  try {
    console.log(`\nSite:      ${SITE_URL}`);
    console.log(`Prototype: ${path.relative(ROOT, DESIGN_FILE)}\n`);
    console.log('Capturing:');
    for (const vp of widths) {
      captured[vp.name] = {};
      if (args.only !== 'design') {
        captured[vp.name].site = await capture(browser, 'site', SITE_URL, vp);
      }
      if (args.only !== 'site') {
        captured[vp.name].design = await capture(
          browser, 'design', pathToFileURL(DESIGN_FILE).href, vp
        );
      }
    }
  } finally {
    await browser.close();
  }

  if (args.only) {
    console.log('\nCapture only (--only given); no diff produced.\n');
    return;
  }

  console.log('\nDiff:');
  for (const vp of widths) {
    await diff(captured[vp.name].site, captured[vp.name].design, vp);
  }
  console.log(
    '\nRead the diffs like this:\n' +
    '  1280px  the real fidelity check - drive this number down.\n' +
    '  1440px  expected to differ; the prototype stretches, we cap at 1184px.\n' +
    '   390px  self-regression only; the prototype has no mobile layout.\n'
  );
})().catch((err) => {
  console.error('\nFailed:', err.message, '\n');
  process.exit(1);
});
