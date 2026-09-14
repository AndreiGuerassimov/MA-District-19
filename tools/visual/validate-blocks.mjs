/**
 * Block validation, as the editor does it — without logging in.
 *
 *   npm run validate:blocks -- 49 14        (post IDs)
 *   npm run validate:blocks -- patterns     (every theme pattern)
 *   npm run validate:blocks -- some.html    (a file of block markup)
 *
 * Loads WordPress's own block-library scripts (resolved through WP_Scripts, so
 * the dependency order and inline bootstrap data match wp-admin) into a
 * headless page, registers the core blocks, parses the saved markup and
 * reports every block the editor would flag as "unexpected or invalid
 * content", with the markup the block's save() expected instead.
 *
 * Theme blocks without a JS save (dynamic blocks such as
 * ma-toronto/next-meeting) are registered as stubs and not validated.
 */
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
process.env.PLAYWRIGHT_BROWSERS_PATH ||= path.join(ROOT, '.playwright');
const { chromium } = await import('playwright');
const SITE = (process.env.MA_SITE_URL || 'http://localhost:8888/matoronto/').replace(/\/$/, '');

const wp = (code) => execFileSync('wp', ['eval', code, `--path=${ROOT}`], { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] })
  .split('\n').filter(l => !l.startsWith('Deprecated') && !l.startsWith('PHP Deprecated')).join('\n');

// Script tags for wp-block-library and everything it depends on, in order.
const scripts = JSON.parse(wp(`
  $s = wp_scripts(); $s->all_deps( array( 'wp-block-library', 'wp-format-library' ) );
  $out = array();
  foreach ( $s->to_do as $h ) {
    $r = $s->registered[ $h ];
    $src = $r->src ? ( preg_match( '#^https?://#', $r->src ) ? $r->src : site_url( $r->src ) ) : '';
    $out[] = array( 'h' => $h, 'src' => $src, 'before' => implode( "\\n", (array) $s->get_data( $h, 'before' ) ), 'after' => implode( "\\n", (array) $s->get_data( $h, 'after' ) ), 'data' => (string) $s->get_data( $h, 'data' ) );
  }
  echo wp_json_encode( $out );
`));

const args = process.argv.slice(2);
const docs = [];
if (args.includes('patterns')) {
  const list = JSON.parse(wp(`
    $out = array();
    foreach ( WP_Block_Patterns_Registry::get_instance()->get_all_registered() as $p ) {
      if ( str_starts_with( $p['name'], 'ma-toronto/' ) ) { $out[] = array( 'name' => $p['name'], 'content' => $p['content'] ); }
    }
    echo wp_json_encode( $out );
  `));
  for (const p of list) docs.push({ label: p.name, content: p.content });
}
for (const file of args.filter(a => a.endsWith('.html'))) {
  docs.push({ label: file, content: (await import('node:fs')).readFileSync(file, 'utf8') });
}
for (const id of args.filter(a => /^\d+$/.test(a))) {
  docs.push({ label: `post ${id}`, content: wp(`echo get_post_field( 'post_content', ${id} );`) });
}
if (!docs.length) {
  console.error('Usage: npm run validate:blocks -- <post IDs...> | patterns');
  process.exit(2);
}

const browser = await chromium.launch();
const page = await browser.newPage();
await page.goto(`${SITE}/wp-includes/images/blank.gif`); // same origin, no page scripts
for (const s of scripts) {
  if (s.data) await page.addScriptTag({ content: s.data });
  if (s.before) await page.addScriptTag({ content: s.before });
  if (s.src) await page.addScriptTag({ url: s.src });
  if (s.after) await page.addScriptTag({ content: s.after });
}

const pageErrors = [];
page.on('pageerror', e => pageErrors.push(e.message));
let failed = 0;
const results = await page.evaluate((docs) => {
  const { blocks, blockLibrary } = window.wp;
  blockLibrary.registerCoreBlocks();
  window.__coreCount = blocks.getBlockTypes().length;
  const report = [];
  for (const doc of docs) {
    // Stub unknown (theme/plugin) blocks so they don't read as invalid.
    for (const m of doc.content.matchAll(/<!-- wp:([a-z0-9-]+\/[a-z0-9-]+)/g)) {
      if (!blocks.getBlockType(m[1])) blocks.registerBlockType(m[1], { title: m[1], category: 'widgets', attributes: {}, save: () => null });
    }
    const invalid = [];
    let count = 0;
    const walk = (list) => list.forEach((b) => {
      if (b.name) count++;
      // parse() no longer guarantees isValid is computed, so validate explicitly.
      const [valid, issues] = b.name && blocks.validateBlock ? blocks.validateBlock(b) : [b.isValid, b.validationIssues];
      if (b.name && !valid) {
        let expected = '';
        try { expected = blocks.getSaveContent(b.name, b.attributes, b.innerBlocks); } catch (e) { expected = String(e); }
        invalid.push({ name: b.name, className: b.attributes?.className || '', issue: `saved:    ${b.originalContent.slice(0, 300)}\n          expected: ${expected.slice(0, 300)}` });
      }
      walk(b.innerBlocks || []);
    });
    walk(blocks.parse(doc.content));
    report.push({ label: doc.label, count, invalid });
  }
  return report;
}, docs);

const coreCount = await page.evaluate(() => window.__coreCount);
if (coreCount < 50 || pageErrors.length) {
  console.error('Core blocks did not load:', coreCount, pageErrors.slice(0, 3));
  process.exit(2);
}
for (const r of results) {
  const ok = r.invalid.length === 0;
  if (!ok) failed++;
  console.log(`  ${ok ? 'PASS' : 'FAIL'}  ${r.label}  (${r.count} blocks)`);
  for (const i of r.invalid) console.log(`        ✗ ${i.name}${i.className ? ` .${i.className}` : ''}\n          ${i.issue}`);
}
await browser.close();
console.log(`\n  ${results.length - failed}/${results.length} valid\n`);
process.exit(failed ? 1 : 0);
