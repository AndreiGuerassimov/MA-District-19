/**
 * Checks for a meeting's page (/meetings/{slug}/, single-meetings.php).
 *
 *   npm run a11y:meeting
 *
 * Covers the decisions in docs/meeting-pages-scope.md: in-person pages get
 * directions, a map and "other meetings here"; online pages get Join on Zoom
 * and How to join instead; Add to calendar serves a weekly .ics; Share is
 * JavaScript-only and absent without it; no plugin front-end assets.
 *
 * Uses the first in-person and first online meeting found on the list, so it
 * does not depend on particular meeting names.
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
const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });

// Pick one of each kind from the list. Prefer an in-person meeting that shares
// its location with another, so "Other meetings at this location" is tested.
await page.goto(`${LIST}?type=in-person`, { waitUntil: 'networkidle' });
const inPersonUrls = await page.$$eval('.ma-meeting__name a', as => as.map(a => a.href));
await page.goto(`${LIST}?type=online`, { waitUntil: 'networkidle' });
const onlineUrl = await page.$eval('.ma-meeting__name a', a => a.href);

const read = async (p) => p.evaluate(() => {
  const q = (s) => document.querySelector(s);
  const primary = q('.ma-meeting-page__primary-action');
  return {
    h1: [...document.querySelectorAll('h1')].map(h => h.textContent.trim()),
    title: document.title,
    h2: [...document.querySelectorAll('main h2')].map(h => h.textContent.trim()),
    primaryHref: primary?.getAttribute('href') || '',
    primarySvgHidden: primary ? [...primary.querySelectorAll('svg')].every(s => s.getAttribute('aria-hidden') === 'true') : false,
    map: q('.ma-map__frame') ? { title: q('.ma-map__frame').getAttribute('title'), loading: q('.ma-map__frame').getAttribute('loading'), src: q('.ma-map__frame').src } : null,
    infoRows: [...document.querySelectorAll('.ma-info__row')].map(r => [r.querySelector('dt')?.textContent.trim(), r.querySelector('dd')?.textContent.trim()]),
    tel: [...document.querySelectorAll('.ma-info a[href^="tel:"]')].map(a => a.getAttribute('href')),
    ics: q('a[href*="calendar=ics"]')?.href || '',
    shareVisible: !!q('[data-ma-share]') && !q('[data-ma-share]').hidden,
    alsoHere: [...document.querySelectorAll('.ma-also__link')].map(a => a.href),
    back: q('.ma-meeting-page__back a')?.getAttribute('href') || '',
    pluginAssets: document.querySelectorAll('link[href*="12-step-meeting-list"], script[src*="12-step-meeting-list"], script[src*="leaflet"]').length,
    skipTarget: !!document.getElementById('wp--skip-link--target'),
    decorativeArrows: [...document.querySelectorAll('main')].flatMap(m => [...m.querySelectorAll('span')]).filter(s => /^[←→▸]/.test(s.textContent.trim()) && s.textContent.trim().length <= 6).every(s => s.closest('[aria-hidden="true"]')),
  };
});

// --- In person ---------------------------------------------------------------
let inPerson = null;
let inPersonUrl = inPersonUrls[0];
for (const url of inPersonUrls) {
  await page.goto(url, { waitUntil: 'networkidle' });
  const r = await read(page);
  if (!inPerson) inPerson = r;
  if (r.alsoHere.length) { inPerson = r; inPersonUrl = url; break; }
}
await page.goto(inPersonUrl, { waitUntil: 'networkidle' });
const ip = inPerson;
const slug = inPersonUrl.split('/').filter(Boolean).pop();

check('in person: one h1, the meeting name', ip.h1.length === 1 && ip.h1[0].length > 0 && ip.title.includes(ip.h1[0]), ip.h1.join());
check('in person: section headings are h2', ['Meeting information', 'Location', 'First time here?', 'About this meeting'].every(h => ip.h2.includes(h)), ip.h2.join(' · '));
check('in person: directions go to OpenStreetMap', ip.primaryHref.startsWith('https://www.openstreetmap.org/directions'), ip.primaryHref.slice(0, 60));
check('in person: map iframe has a title and lazy-loads', !!ip.map && /^Map showing /.test(ip.map.title) && ip.map.loading === 'lazy', ip.map?.title);
check('in person: map is an OpenStreetMap embed (no API key)', !!ip.map && ip.map.src.startsWith('https://www.openstreetmap.org/export/embed.html'), '');
check('in person: day, type and cost rows present', ['Day & time', 'Type', 'Who can come', 'Cost'].every(l => ip.infoRows.some(([k]) => k === l)), ip.infoRows.map(([k]) => k).join(', '));
check('in person: contact phone is a dialable tel: link', ip.tel.every(h => /^tel:\+?\d{10,}$/.test(h)), ip.tel.join() || 'no phone on this meeting');
check('in person: "other meetings here" link to meeting pages', ip.alsoHere.length === 0 || ip.alsoHere.every(h => /\/meetings\/[^/]+\/$/.test(h) && !h.endsWith(`/${slug}/`)), `${ip.alsoHere.length} listed`);
check('back link goes to the list', ip.back.endsWith('/meetings/'), ip.back);
check('decorative arrows are aria-hidden', ip.decorativeArrows);
check('Share button revealed with JavaScript', ip.shareVisible);
check('no plugin front-end assets loaded', ip.pluginAssets === 0, `${ip.pluginAssets}`);
check('skip link target exists', ip.skipTarget);

// Keyboard: primary action reachable and visibly focused.
await page.focus('.ma-meeting-page__primary-action');
const outline = await page.$eval('.ma-meeting-page__primary-action', el => getComputedStyle(el).outlineStyle);
check('primary action shows a focus outline', outline !== 'none', outline);

// Calendar download.
const icsRes = await fetch(ip.ics);
const ics = await icsRes.text();
check('Add to calendar serves text/calendar', icsRes.ok && (icsRes.headers.get('content-type') || '').startsWith('text/calendar'), icsRes.headers.get('content-type'));
check('.ics repeats weekly in Toronto time', /RRULE:FREQ=WEEKLY;BYDAY=(SU|MO|TU|WE|TH|FR|SA)/.test(ics) && /DTSTART;TZID=America\/Toronto:\d{8}T\d{6}/.test(ics) && /BEGIN:VTIMEZONE/.test(ics), (ics.match(/RRULE:FREQ=WEEKLY[^\r\n]*/) || [''])[0]);
check('.ics lines are folded to 75 octets', ics.split('\r\n').every(l => Buffer.byteLength(l) <= 75), '');

// --- Online ------------------------------------------------------------------
await page.goto(onlineUrl, { waitUntil: 'networkidle' });
const on = await read(page);
const joinName = await page.getByRole('link', { name: /^Join on Zoom$/ }).count();
check('online: primary action is "Join on Zoom" by accessible name', joinName === 1 && /zoom\.us/.test(on.primaryHref), on.primaryHref.slice(0, 40));
check('online: Zoom logo is decorative', on.primarySvgHidden);
check('online: How to join replaces Location', on.h2.includes('How to join') && !on.h2.includes('Location'), on.h2.join(' · '));
check('online: no map, no "other meetings here"', !on.map && on.alsoHere.length === 0 && !on.h2.includes('Other meetings at this location'));

// --- Narrow and no-JS --------------------------------------------------------
for (const url of [inPersonUrl, onlineUrl]) {
  const narrow = await browser.newPage({ viewport: { width: 320, height: 800 } });
  await narrow.goto(url, { waitUntil: 'networkidle' });
  const over = await narrow.evaluate(() => document.documentElement.scrollWidth - innerWidth);
  check(`no horizontal overflow at 320px (${url.split('/').filter(Boolean).pop()})`, over <= 0, `${over}px`);
  await narrow.close();
}
const ctx = await browser.newContext({ javaScriptEnabled: false });
const nojs = await ctx.newPage();
await nojs.goto(inPersonUrl, { waitUntil: 'domcontentloaded' });
const hiddenShare = await nojs.$eval('[data-ma-share]', b => b.hidden);
check('Share button stays hidden without JavaScript', hiddenShare);
await ctx.close();

await browser.close();

// --- Location pages still redirect ---------------------------------------------
const locSingle = await fetch(`${BASE}/locations/online/`, { redirect: 'manual' });
check('a location page redirects (302) to the list', [301, 302].includes(locSingle.status) && (locSingle.headers.get('location') || '').includes('/meetings/'), `${locSingle.status} → ${locSingle.headers.get('location') || ''}`);

const pad = Math.max(...results.map(r => r.name.length));
let failed = 0;
for (const r of results) {
  if (!r.pass) failed++;
  console.log(`  ${r.pass ? 'PASS' : 'FAIL'}  ${r.name.padEnd(pad)}  ${r.detail}`);
}
console.log(`\n  ${results.length - failed}/${results.length} passed\n`);
process.exit(failed ? 1 : 0);
