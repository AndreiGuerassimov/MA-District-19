/**
 * Checks for the homepage hero's "Next meeting" card (blocks/next-meeting).
 *
 *   npm run check:next-meeting
 *
 * Fakes the browser clock to cover: an upcoming meeting, "Happening now" in
 * the first 15 minutes, moving on after that, a stale page (server rendered
 * hours earlier, as from a page cache) corrected in the browser, and the
 * server-rendered card standing alone without JavaScript.
 *
 * Expected values come from the card's own embedded schedule, so the checks do
 * not depend on particular meetings.
 */
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
process.env.PLAYWRIGHT_BROWSERS_PATH ||= path.join(ROOT, '.playwright');
const { chromium } = await import('playwright');

const HOME = process.env.MA_SITE_URL || 'http://localhost:8888/matoronto/';
const results = [];
const check = (name, pass, detail = '') => results.push({ name, pass, detail });
const browser = await chromium.launch();

const read = (p) => p.evaluate(() => {
  const root = document.querySelector('[data-ma-next-meeting]');
  const card = root?.querySelector('.ma-next-meeting__card');
  return root ? {
    config: JSON.parse(root.dataset.maNextMeeting),
    lead: root.querySelector('.ma-next-meeting__lead').textContent,
    text: card.textContent.replace(/\s+/g, ' ').trim(),
    href: card.getAttribute('href'),
    live: card.classList.contains('is-live'),
    dotHidden: root.querySelector('.ma-next-meeting__dot').getAttribute('aria-hidden') === 'true',
  } : null;
});

// Toronto time -> ISO with the right offset for September (EDT).
const at = (dayOffsetFromMonday, minutes) => {
  const d = new Date(Date.UTC(2026, 8, 14 + dayOffsetFromMonday, 0, 0)); // Mon 14 Sep 2026
  return new Date(d.getTime() + (minutes + 240) * 60000); // +4h = EDT -> UTC
};
const withClock = async (when, js = true) => {
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 }, javaScriptEnabled: js });
  const p = await ctx.newPage();
  if (when) await p.clock.setFixedTime(when);
  await p.goto(HOME, { waitUntil: 'networkidle' });
  const r = await read(p);
  await ctx.close();
  return r;
};

const base = await withClock(null);
check('card renders on the homepage', !!base, base?.text);
if (base) {
  const { schedule, liveFor } = base.config;
  check('schedule embedded, public fields only', schedule.length > 0 && schedule.every(m => Object.keys(m).sort().join() === 'd,name,place,s,url'), `${schedule.length} meetings`);
  check('card links to a meeting page', /\/meetings\/[^/]+\/$/.test(base.href), base.href);
  check('status dot is decorative', base.dotHidden);

  // Use the first meeting of the week that has a gap of 15+ min before the next.
  const m = schedule.find((x, i) => (schedule[i + 1]?.d ?? 99) !== x.d || schedule[i + 1].s - x.s > liveFor) || schedule[0];
  const mondayOffset = (m.d + 6) % 7;

  const before = await withClock(at(mondayOffset, m.s - 60));
  check('an hour before: "Next meeting … — name"', before.lead.startsWith(before.config.labels.next) && before.lead.endsWith(`— ${m.name}`) && !before.live, before.lead);

  const started = await withClock(at(mondayOffset, m.s + 5));
  check('5 minutes in: "Happening now" and pulsing', started.lead === `${started.config.labels.live} ${m.name}` && started.live, started.lead);

  const later = await withClock(at(mondayOffset, m.s + liveFor));
  check(`${liveFor} minutes in: moves on`, !later.lead.startsWith(later.config.labels.live) && !later.live, later.lead);

  // 11:59 PM the night before a day's first meeting: nothing can come between.
  const first = schedule.find(x => x.s >= 1); // schedule is sorted by day, then time
  const tomorrow = await withClock(at((first.d + 6) % 7 - 1, 23 * 60 + 59));
  check('11:59 PM the night before: "Tomorrow"', tomorrow.lead === `${tomorrow.config.labels.next} ${tomorrow.config.labels.tomorrow} ${tomorrow.lead.split(' ').slice(3, 5).join(' ')} — ${first.name}`, tomorrow.lead);

  // Stale page: server rendered "now", browser clock three days on.
  const stale = await withClock(new Date(Date.now() + 3 * 24 * 3600 * 1000));
  const nojs = await withClock(null, false);
  check('JavaScript off: server-rendered card stands', !!nojs && nojs.lead.length > 0, nojs?.lead);
  check('stale cached page corrected in the browser', stale.lead !== nojs.lead || stale.config.schedule.length === 1, `${nojs.lead}  →  ${stale.lead}`);
}

// Target size and overflow at phone width.
const phone = await browser.newPage({ viewport: { width: 320, height: 800 } });
await phone.goto(HOME, { waitUntil: 'networkidle' });
const box = await phone.$eval('.ma-next-meeting__card', el => { const r = el.getBoundingClientRect(); return { h: r.height, over: document.documentElement.scrollWidth > innerWidth, right: r.right, vw: innerWidth }; });
check('fits at 320px, no overflow', !box.over && box.right <= box.vw, JSON.stringify(box));
await browser.close();

const pad = Math.max(...results.map(r => r.name.length));
let failed = 0;
for (const r of results) {
  if (!r.pass) failed++;
  console.log(`  ${r.pass ? 'PASS' : 'FAIL'}  ${r.name.padEnd(pad)}  ${r.detail}`);
}
console.log(`\n  ${results.length - failed}/${results.length} passed\n`);
process.exit(failed ? 1 : 0);
