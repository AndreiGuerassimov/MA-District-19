/**
 * Checks for the meetings list (/meetings/).
 *
 *   npm run a11y:meetings
 *
 * Covers the decisions in docs/meetings-scope.md: server-rendered URL filters,
 * day grouping, meaningful action names, no links to individual meeting pages
 * yet (they redirect), and no plugin front-end assets.
 */
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
process.env.PLAYWRIGHT_BROWSERS_PATH ||= path.join(ROOT, '.playwright');
const { chromium } = await import('playwright');

const BASE = (process.env.MA_SITE_URL || 'http://localhost:8888/matoronto/').replace(/\/$/, '');
const LIST = `${BASE}/meetings/`;
const results = [];
const check = (name, pass, detail = '') => results.push({ name, pass, detail });

const browser = await chromium.launch();

const read = async (page) => page.evaluate(() => {
  const chips = [...document.querySelectorAll('.ma-chip')];
  const meetings = [...document.querySelectorAll('.ma-meeting')];
  const cta = [...document.querySelectorAll('.ma-meeting__cta')];
  return {
    count: document.querySelector('.ma-meetings__count')?.textContent.trim(),
    current: chips.filter(c => c.getAttribute('aria-current') === 'true').map(c => c.textContent.trim()),
    days: [...document.querySelectorAll('.ma-day__name')].map(h => h.textContent.trim()),
    meetings: meetings.length,
    h1: document.querySelectorAll('h1').length,
    dayH2: document.querySelectorAll('.ma-day__name').length,
    meetingH3: document.querySelectorAll('.ma-meeting__name').length,
    times: [...document.querySelectorAll('.ma-meeting__time')].map(t => t.getAttribute('datetime')),
    ctaNames: cta.map(a => a.textContent.replace(/\s+/g, ' ').replace('→', '').trim()),
    ctaHrefs: cta.map(a => a.getAttribute('href')),
    allHrefsInMain: [...document.querySelectorAll('main a')].map(a => a.getAttribute('href')),
    chipSizes: chips.map(c => { const r = c.getBoundingClientRect(); return [Math.round(r.width), Math.round(r.height)]; }),
    pluginAssets: document.querySelectorAll('link[href*="12-step-meeting-list"], script[src*="12-step-meeting-list"], script[src*="leaflet"]').length,
    skipTarget: !!document.getElementById('wp--skip-link--target'),
    overflow: document.documentElement.scrollWidth > innerWidth,
  };
});

// --- All ------------------------------------------------------------------
const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
await page.goto(LIST, { waitUntil: 'networkidle' });
const all = await read(page);

check('list renders meetings', all.meetings > 0, `${all.meetings} meetings`);
check('count matches rendered meetings', all.count?.startsWith(String(all.meetings)), all.count);
check('"All" chip is current by default', all.current.join() === 'All', all.current.join());
const order = ['MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY'];
const idx = all.days.map(d => order.indexOf(d.toUpperCase()));
check('days run Monday to Sunday', idx.every((v, i) => i === 0 || v > idx[i - 1]), all.days.join(' · '));
check('exactly one h1', all.h1 === 1, `${all.h1}`);
check('each day is an h2, each meeting an h3', all.dayH2 === all.days.length && all.meetingH3 === all.meetings, `${all.dayH2} h2 / ${all.meetingH3} h3`);
check('times carry a valid datetime', all.times.every(t => /^\d{2}:\d{2}$/.test(t)), all.times.slice(0, 3).join(', '));
check('every action names its meeting', all.ctaNames.every(n => /TEST|—|\w{3,}/.test(n) && (/^Join .+ on /.test(n) || /^Get directions to /.test(n))),
  all.ctaNames[0] + ' | ' + all.ctaNames.find(n => n.startsWith('Get')));
const onlyAllowed = all.allHrefsInMain.every(h =>
  h.includes('?type=') || h.endsWith('/meetings/') || h.includes('zoom.us') || h.includes('google.com/maps') || h.includes('/contact/'));
check('no links to individual meeting pages', onlyAllowed, `${all.allHrefsInMain.length} links checked`);
check('chips meet 24px target size', all.chipSizes.every(([w, h]) => w >= 24 && h >= 24), JSON.stringify(all.chipSizes[0]));
check('no plugin front-end assets loaded', all.pluginAssets === 0, `${all.pluginAssets}`);
check('skip link target exists', all.skipTarget);
check('no horizontal overflow', !all.overflow);

// --- Filters --------------------------------------------------------------
for (const [type, label] of [['online', 'Online'], ['in-person', 'In person']]) {
  await page.goto(`${LIST}?type=${type}`, { waitUntil: 'networkidle' });
  const f = await read(page);
  check(`?type=${type} marks "${label}" current`, f.current.join() === label, f.current.join());
  check(`?type=${type} narrows the list and count`, f.meetings > 0 && f.meetings < all.meetings && f.count.startsWith(String(f.meetings)), f.count);
}
await page.goto(`${LIST}?type=nonsense`, { waitUntil: 'networkidle' });
const junk = await read(page);
check('unknown filter falls back to All', junk.current.join() === 'All' && junk.meetings === all.meetings, junk.count);

// --- Without JavaScript -----------------------------------------------------
const ctx = await browser.newContext({ javaScriptEnabled: false });
const nojs = await ctx.newPage();
await nojs.goto(`${LIST}?type=online`, { waitUntil: 'domcontentloaded' });
const n = await read(nojs);
check('filters work with JavaScript off', n.current.join() === 'Online' && n.meetings > 0, n.count);
await ctx.close();

await browser.close();

// --- Temporary redirects and sitemap -----------------------------------------
const res = await fetch(`${BASE}/wp-json/wp/v2/types`).catch(() => null);
const single = await fetch(`${LIST}test-never-alone/`, { redirect: 'manual' });
const loc = await fetch(`${BASE}/locations/`, { redirect: 'manual' });
check('meeting page URL redirects (302) to the list', single.status === 302 && (single.headers.get('location') || '').endsWith('/meetings/'),
  `${single.status} → ${single.headers.get('location') || ''}`);
check('locations archive redirects (302) to the list', loc.status === 302, `${loc.status}`);
const sitemap = await fetch(`${BASE}/sitemap_index.xml`).then(r => r.ok ? r.text() : '').catch(() => '');
check('meeting pages absent from Yoast sitemap', sitemap === '' || !/tsml_meeting|tsml_location/.test(sitemap),
  sitemap ? 'sitemap checked' : 'no sitemap served');

const pad = Math.max(...results.map(r => r.name.length));
let failed = 0;
for (const r of results) {
  if (!r.pass) failed++;
  console.log(`  ${r.pass ? 'PASS' : 'FAIL'}  ${r.name.padEnd(pad)}  ${r.detail}`);
}
console.log(`\n  ${results.length - failed}/${results.length} passed\n`);
process.exit(failed ? 1 : 0);
