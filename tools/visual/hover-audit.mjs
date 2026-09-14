/**
 * Hover audit: finds boxes that change border/background on hover but are not
 * links. Only elements that are, or are wholly covered by, a link should have
 * a hover state; static content boxes should not (decided 14 Sep 2026).
 *
 *   npm run audit:hover
 *
 * Reads every :hover rule from the loaded stylesheets, matches it against the
 * page, and classifies each matching element. Exits non-zero if any static box
 * has one. Descendant hover rules (".x:hover .y") and :has() rules scoped to a
 * control are not flagged.
 */
process.env.PLAYWRIGHT_BROWSERS_PATH ||= '/Users/andre/Sites/matoronto/.playwright';
const { chromium } = await import('playwright');
const pages = ['', 'how-it-works/', 'meetings/', 'meetings/never-alone-1/', 'meetings/a-new-hope/', 'faq/', 'literature/', 'contact/', 'how-it-works/the-twelve-steps/', 'how-it-works/the-twelve-questions/'];
const b = await chromium.launch();
const seen = new Map();
for (const path of pages) {
  const p = await b.newPage({ viewport: { width: 1280, height: 900 } });
  await p.goto(`http://localhost:8888/matoronto/${path}`, { waitUntil: 'networkidle' });
  const found = await p.evaluate(() => {
    const out = [];
    const visit = (rules) => {
      for (const r of rules) {
        if (r.cssRules && !r.selectorText) { visit(r.cssRules); continue; }
        if (!r.selectorText || !r.selectorText.includes(':hover')) continue;
        const st = r.style;
        const visual = ['border-color','border-top-color','border-left-color','background-color','box-shadow','outline','outline-color','background'].some(k => st.getPropertyValue(k));
        if (!visual) continue;
        for (const sel of r.selectorText.split(',')) {
          if (!sel.includes(':hover')) continue;
          const [target, rest] = sel.split(':hover');
          if (rest && rest.trim()) continue; // descendant styling (e.g. ".x:hover .y") — not the box itself
          let els = [];
          try { els = [...document.querySelectorAll(target.trim())]; } catch { continue; }
          for (const el of els) {
            const isLink = el.matches('a[href], button, summary, input, [role=button]');
            const stretched = [...el.querySelectorAll('a[href]')].some(a => getComputedStyle(a, '::after').position === 'absolute');
            const kind = isLink ? 'is link/button' : stretched ? 'whole-box link (stretched)' : el.querySelector('a[href], button') ? 'CONTAINS a link but box is not one' : 'STATIC — no link';
            out.push({ rule: sel.trim(), kind, cls: (el.className?.baseVal ?? el.className).toString().slice(0, 70), text: el.textContent.replace(/\s+/g, ' ').trim().slice(0, 50) });
          }
        }
      }
    };
    for (const s of document.styleSheets) { try { visit(s.cssRules); } catch {} }
    return out;
  });
  for (const f of found) {
    if (f.kind.startsWith('is link') ) continue;
    const key = f.rule + '|' + f.kind;
    if (!seen.has(key)) seen.set(key, { ...f, pages: new Set() });
    seen.get(key).pages.add('/' + path);
  }
  await p.close();
}
await b.close();
let bad = 0;
for (const v of seen.values()) {
  const ok = v.kind.startsWith('whole-box');
  if (!ok) bad++;
  console.log(`  ${ok ? 'ok  ' : 'FAIL'}  ${v.kind.padEnd(36)} ${v.rule}\n        e.g. "${v.text}" on ${[...v.pages].join(' ')}`);
}
console.log(bad ? `\n  ${bad} hover rule(s) on non-link boxes\n` : '\n  No hover states on static boxes.\n');
process.exit(bad ? 1 : 0);
