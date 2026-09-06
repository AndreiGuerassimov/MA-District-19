# MA Toronto — Block Theme Build Plan (Phase 1: Homepage)

**Status:** planning only, no code written.
**Scope:** `design/Home.dc.html` plus the two components it imports (`SiteHeader.dc.html`, `SiteFooter.dc.html`). Other pages in `design/` are out of scope for this pass.

**Decisions taken since first draft:**
- **Quotes slider is deferred** — not built this pass, to be scoped separately (§4.5).
- **Standing latitude to depart from the prototype where it improves SEO or accessibility** (§0.1). Changes made under this are logged, not silently applied.
- **Open questions resolved using best judgment** (§8). Each is marked ✅ **ANSWERED** with the original question preserved beneath it, so any call can be reversed.
- **Build architecture settled** (§10): layered, modular, and locked with `templateLock: "contentOnly"` so the layout cannot be rearranged by accident.
- **Hero "Next meeting" card deferred** to the Meetings phase (§4.2) — it needs a real data model first.
- **Recommend updating to WordPress 7.1 before coding** (§0.2).

**Deferred to later phases:** quotes slider (§4.5), "Next meeting" card (§4.2).
**Still genuinely open:** real image and logo assets (§8.5). Does not block the build.

## 0. Environment as found on disk

Several placeholders in the brief resolved differently than assumed. Confirming what is actually here:

| Item | Brief said | Actually |
|---|---|---|
| WordPress | `[version]` | **7.0.3** |
| PHP | `[version]` | **8.3.14** (the web server's runtime — `Apache/2.4.62`, via `X-Powered-By`). Note the CLI is 8.5.5; the *server* version is the one that matters for theme PHP |
| Local stack | `[wp-env / DDEV / Local]` | MAMP-style Apache at **http://localhost:8888/matoronto** (returns 200) |
| Workspace root | `wp-content/themes/` | **Repo root is the whole WP install** (`/Users/andre/Sites/matoronto`), git-tracked from there |
| Theme slug | `[theme-slug]` | **`ma-toronto`** — active, Create Block Theme skeleton |
| `design/` location | "alongside the theme" | **WP install root**, i.e. *outside* `wp-content/themes/ma-toronto/` |

Three consequences worth deciding on early:

1. **`.distignore` cannot exclude `design/` as things stand.** `.distignore` is read by Create Block Theme / `wp dist-archive` and only applies to paths *inside the theme directory*. `design/` sits two levels above it. I will still add a `.distignore` to the theme (covering `node_modules/`, `tests/`, `docs/`, `.playwright/`, and `design/` in case it moves), but excluding `design/` from production actually depends on your deploy path. See §7.
2. WordPress 7.0 ships a **first-class navigation overlay template part area** (`WP_TEMPLATE_PART_AREA_NAVIGATION_OVERLAY`) and a `core/navigation-overlay-close` block. This changes the mobile-nav recommendation substantially — see §4.1.
3. Theme skeleton still has Create Block Theme's default `contentSize: 620px` / `wideSize: 1000px` and a system font stack. All of it gets replaced.

Other plugins present: ACF, Contact Form 7 (+ antispam), Redirection, Yoast, Create Block Theme, and two AI plugins. Blocksy + Blocksy Companion are deactivated as stated.

## 0.2 WordPress version — recommend updating to 7.1 before we start

Two updates are available from this install: **7.0.4** (minor) and **7.1 "Mary Lou"**, released **19 August 2026** — three days ago.

**Recommendation: update to 7.1 now, before any code is written.**

Not for general "stay current" reasons. 7.1 ships three things that map directly onto decisions in this plan, each moving work *out of hand-written CSS and into `theme.json`* — which is the constraint you set:

| 7.1 feature | What it replaces in this plan |
|---|---|
| **`settings.viewport`** — themes can redefine mobile/tablet breakpoints in `theme.json`, and the Navigation block's overlay follows them automatically | §8.3's 1100px nav breakpoint, currently planned as a CSS override of core's ~600px default. Becomes a token |
| **Active nav item styling via `theme.json`** | §4.1's active-item underline, currently planned as hand-written CSS on `.current-menu-item` |
| **Hover / focus / active states in `theme.json`** for Button and Navigation Link | Part of §0.1's focus-visible work and the button hover states, currently planned as CSS |

Also: the font library is fully integrated into `theme.json` in 7.1, which tidies the self-hosted Lora/Karla setup in §7.

**Timing argument.** The cost of upgrading is lowest right now, when there is zero theme code. Upgrading mid-build means re-verifying every template part against changed core block markup and re-baselining every Playwright screenshot. Building on 7.0 and deploying to a 7.1 production is the worst of the options.

**The honest risk.** 7.1 is three days old. ACF, Contact Form 7, and Yoast all have updates pending and their 7.1 compatibility is unverified. Mitigating factors: this is a local environment with no production traffic, and `database/matoronto.sql` exists as a restore point. If 7.1 causes plugin trouble we drop back to 7.0.4 and lose only the conveniences above — **every part of this plan works as written on 7.0**, because the navigation overlay feature it depends on shipped in 7.0.

**Suggested order:** back up the DB → update core to 7.1 → update ACF, CF7, Yoast → load the site and open the Site Editor to confirm both work → then start step 0.

Note this is unrelated to your no-database-writes constraint, which is about content migration. A core update runs schema upgrades by design; it isn't a content write.

**Also worth doing while you're in there:** delete Blocksy and Blocksy Companion rather than leaving them deactivated, so their CSS can't be reintroduced by accident.

## 0.1 Standing latitude: SEO and accessibility over prototype fidelity

Granted. Where the prototype and a correct/accessible result disagree, the accessible result wins and the pixel diff is expected to differ. Everything below is now a decision, not a question — but each one is logged here so nothing is a surprise at review, and any of them can be reverted on request.

**Colour and contrast** (was §8.6, now resolved):
- `#CE5F35` accent is nudged until it meets **4.5:1** on `#F8F5EC` cream at the small sizes it's used at (12–13px eyebrows and card CTAs). Same treatment for `#7A8B7E` subtle. The hue stays; lightness moves as little as the measurement allows. Measured values recorded in `theme.json` comments and in `CLAUDE.md`.
- Large display text keeps the drawn accent value where 3:1 is sufficient, so the hero eyebrow and headings look unchanged.
- The bright accent stays for **non-text** use (pill backgrounds, underlines) where contrast rules differ.

**Document semantics** (invisible, no pixel cost):
- Exactly one `<h1>` per page — the hero headline. Section titles become `<h2>`.
- Pathway card titles are `<div>`s in the prototype; they become **`<h3>`**. Because that section has no heading of its own, an `<h2>` is added above the grid so the outline doesn't jump `h1 → h3`. If the design shouldn't show one, it goes in visually-hidden — I'll flag which I used.
- Real landmarks: `<header>`, `<nav>`, `<main>`, `<footer>` via `tagName` on parts and groups. WordPress's block-theme skip link is left enabled.
- Decorative `→` arrows and the status dot get `aria-hidden="true"`, so they aren't announced.

**Interaction:**
- Every interactive element gets a visible `:focus-visible` style. The prototype has **none** — it removes underlines and relies on hover only, so as drawn the design is not keyboard-navigable. After colour contrast, this is the largest accessibility gap.
- Focus styles use a 2px offset outline in primary green, not a colour-only change.
- `prefers-reduced-motion` honoured on any transition we add.

**SEO / performance:**
- Fonts self-hosted (§7) — removes a third-party request and improves LCP.
- Hero image gets `fetchpriority="high"` and explicit `width`/`height` to protect LCP and CLS. Real alt text required (§8.5).
- Below-fold images lazy-loaded.
- Yoast stays authoritative for titles/meta; the theme won't emit competing tags.
- Organisation schema left to Yoast rather than hand-rolled. Meeting/event schema is a separate question (§8.4) tied to the Meetings phase.

**Explicitly *not* changed without asking:** layout, spacing, type scale, or anything that alters the drawn composition. Those stay in §8 as questions.

---

## 1. Homepage section inventory (document order)

`Home.dc.html` is 146 lines of inline-styled markup with a small `DCLogic` script block at the end supplying dynamic values. Sections in order:

| # | Section | Description | One-off or recurring |
|---|---|---|---|
| 1 | **Site header** (`dc-import SiteHeader`) | 82px bar: circular "MA" mark + two-line wordmark ("Marijuana Anonymous" / "TORONTO" eyebrow), 8-item horizontal nav with active-item underline, terracotta CTA pill "Find a Meeting". Full-bleed, 48px gutter, 1px bottom hairline. | **Recurring** — every page |
| 2 | **Hero** | 2-column grid `1.05fr / .95fr`, 56px gap, cream→sand vertical gradient. Left: uppercase eyebrow, `h1` 52px Lora, 18px lede, primary pill button + secondary text link with a 2px underline. Right: 340px image placeholder (radius 14, soft shadow). | **One-off layout.** The overlapping **"Next meeting" card** ⏸️ is *deferred* — see §4.2 |
| 3 | **Pathway cards** | 5-up equal grid, 18px gap, on `#FFFDF6`. Each card is a whole-card `<a>`: Lora 18px title, 14px description, terracotta "… →" CTA pinned to bottom via `margin-top:auto`. Hover raises border to primary green. | **Recurring** — same card shape appears across other prototypes |
| 4 | **About band + counters** | Full-bleed primary-green band. Centred 820px column: `h2` 30px Lora, body paragraph, then a highlighted sand-coloured paragraph. Below: two stat blocks (46px Lora numbers `392+` / `$366,218+` with uppercase labels) separated by a 1px vertical rule, 120px gap. | Band is **one-off**; the stat pair is **recurring** |
| 5 | **Quotes slider** ⏸️ *deferred* | Centred. Eyebrow, then prev/next 44px circular buttons flanking an italic 34px Lora quote, then a row of 4–5 dot buttons. **Interactive:** 5 hard-coded quotes, `setInterval` autoplay every 6s, clicking any control clears the timer. | **Deferred — see §4.5.** Not built this pass |
| 6 | **Site footer** (`dc-import SiteFooter`) | Deep-green band. 4-column grid `1.4fr 1fr 1fr 1fr`, 40px gap: brand lockup + blurb, then Program / Resources / Connect link columns with uppercase micro-labels. Bottom hairline + copyright paragraph. | **Recurring** — every page |

### Dynamic values in the prototype

The `DCLogic` script supplies `meetingsCount`, `savedAmount`, `currentQuote`, `prevQuote`, `nextQuote`, `dots`. The "Next meeting" line in the hero is hard-coded HTML, not a template value, but is obviously meant to be live data. See open questions §8.

### ⚠️ The prototypes contain no responsive design at all

I grepped every file in `design/`: **zero `@media` queries, zero `clamp()`, zero `minmax()`/`auto-fit`** in the page prototypes. Every grid is a fixed column count, every size is a fixed pixel value, and each file declares `$preview: { width: 1280 }`. The two `…Options-print…` files have media queries, but those are print stylesheets for the concept deck, not responsive rules.

This matters for two reasons:

- **The mobile design does not exist and has to be invented** during the build. That is design work, not translation work.
- **The 390px screenshot diff you asked for in step B cannot be a fidelity check.** At 390px the prototype will render as a squashed 5-column grid and a 2-column hero, which is not what we want to build toward. The 1440px diff is meaningful (with the caveat in §8.2 about the missing container cap); the 390px pass is useful only as a *regression* baseline of our own output over time.

Also note `MA Toronto Homepage Options*.dc.html` are earlier concept explorations (Options 1A / 1B / 1C). `Home.dc.html` corresponds to **Option 1A**. I have excluded 1B/1C styling from the token extraction — several of the one-off colours live only there.

---

## 2. Design system extraction

Extracted from `Home.dc.html`, `SiteHeader.dc.html`, `SiteFooter.dc.html`, and scanned across `Contact`, `FAQ`, `Literature`, `Meetings`, `TwelveSteps` for token values only.

### 2.1 Colour

Counts are occurrences across the page prototypes (excluding the Options concept deck).

| Hex | Uses | Role in prototype | Proposed slug | Notes |
|---|---|---|---|---|
| `#2F5D47` | 44 | Primary green — buttons, links, headings on light | `primary` | Anchor colour |
| `#F8F5EC` | 42 | Page canvas cream | `base` | Anchor colour |
| `#21372C` | 27 | Ink / darkest text | `ink` | Anchor colour |
| `#CE5F35` | 25 | Accent terracotta — eyebrows, CTA pill, "→" links | `accent` | Anchor colour |
| `#FFFDF6` | 16 | Raised surface (card bands, cards on cream) | `surface` | |
| `#C8D4C4` | 15 | Body text on dark green | `on-dark` | |
| `#5B6E61` | 11 | Card body text | `muted` | ⚠️ near-dup of `#44584C` |
| `#44584C` | 9 | Lede / body text on cream | `body` | ⚠️ near-dup of `#5B6E61` |
| `#7A8B7E` | 6 | Tertiary / meta text | `subtle` | ⚠️ contrast risk, see 2.6 |
| `#F3EEDF` | 4 | Hero gradient end | `base-2` | Only used as a gradient stop |
| `#8FA98D` | 4 | Footer micro-labels | `on-dark-muted` | |
| `#EFEBDD` | 3 | Text on green band | — | ⚠️ near-dup of `#F8F5EC` |
| `#B0521F` | 3 | Accent dark (hover/pressed) | `accent-dark` | Not used on homepage |
| `#F0D9A8` | 2 | Sand highlight text on green | `sand` | |
| `#E9A13B` | 2 | Amber (tags/badges) | `amber` | Not on homepage |
| `#E1EEDF` | 2 | Pale green surface (badges) | `primary-tint` | Not on homepage |
| `#F7E4D6` | 2 | Pale terracotta surface | `accent-tint` | Not on homepage |
| `#A9BCA6` | 2 | Stat labels on green | — | ⚠️ near-dup of `#8FA98D` |
| `#24463A` | 2 | Dark green surface | — | ⚠️ near-dup of `#1F3E31` |
| `#3F8A5F` | 2 | "Live/next meeting" status dot | `success` | |
| `#7A7360` | 2 | Placeholder-label text | — | Prototype scaffolding, **not a token** |
| `#1F3E31` | 1 | Footer background | `primary-dark` | |
| `#FFF7EE` | 1 | Text on terracotta pill | `on-accent` | ⚠️ 4th near-white cream |
| `#F0EAD9` | 1 | Surface | — | ⚠️ used once, likely collapse |
| `#C77F2C` | 1 | Amber dark | — | Literature only |
| `#E4DDC9` / `#EBE5D4` | 1 ea. | Image-placeholder stripes | — | Prototype scaffolding, **not tokens** |
| `#7A5C8A` `#513A63` `#4A6B8A` `#2F4A63` | 1 ea. | Literature category colours | — | Defer to Literature phase |

**Alpha values** (all derived, should become tokens or `color-mix()`):

| Value | Uses | Role |
|---|---|---|
| `rgba(33,55,44,.1)` | 15 | Standard hairline border (ink @ 10%) |
| `rgba(33,55,44,.12)` | 5 | Header bottom border |
| `rgba(33,55,44,.2)` | 5 | Stronger border |
| `rgba(47,93,71,.4)` `.35` `.25` | 1 ea. | Slider button border, link underline, inactive dot |
| `rgba(0,0,0,.15)` | 1 | Header CTA shadow — ⚠️ **only pure-black alpha in the system** |
| `rgba(239,235,221,.25)` / `rgba(200,212,196,.2)` | 1 ea. | Dividers on dark |

**Colour inconsistencies to decide on:**

- **Five near-identical creams**: `#F8F5EC`, `#FFFDF6`, `#F3EEDF`, `#EFEBDD`, `#F0EAD9`, plus `#FFF7EE`. Recommend keeping three (`base`, `surface`, `base-2`) and mapping the rest.
- **Four dark greens**: `#21372C` (ink), `#1F3E31` (footer), `#24463A` (×2), `#2F5D47` (primary). Recommend collapsing `#24463A` into `#1F3E31`.
- **Two body-text greens** 5 points apart: `#44584C` and `#5B6E61`. Recommend one.
- **Two on-dark muted greens**: `#8FA98D` and `#A9BCA6`. Recommend one.
- `rgba(0,0,0,.15)` should almost certainly be ink-based like every other shadow.

### 2.2 Typography

Families loaded from Google Fonts: **Lora** (400, 500, 600, 700 + italic 400/500) for display/headings, **Karla** (400, 500, 600, 700) for UI/body. `ui-monospace` appears only in prototype placeholder labels.

Weights actually *used*: Lora 400/500/600 (+ italic 500); Karla 400/600/700. **Lora 700, Lora italic 400, and Karla 500 are loaded but never used** — trim on self-host.

**22 distinct font sizes appear across the prototypes.** That is the single largest normalisation decision in this document.

| px | Uses | Where | Proposed scale slot |
|---|---|---|---|
| 52 | 1 | Homepage `h1` | `xxx-large` |
| 48 | 1 | Other-page `h1` | ⚠️ merge with 52 or 46? |
| 46 | 5 | Page `h1` (×4) + stat numbers (×2) | `xx-large` |
| 40 | 1 | One-off heading | ⚠️ one-off |
| 34 | 1 | Quote slider | `x-large` |
| 30 | 1 | Section `h2` | `large` |
| 22 | 3 | Sub-headings (3 different line-heights) | `medium` |
| 20 | 2 | Sub-heading / lede | ⚠️ near-dup of 22 |
| 18 | 9 | Card titles (8) + hero lede | `normal-lg` |
| 17.5 | 3 | Body lede | ⚠️ **fractional** |
| 17 | 4 | Body / labels | ⚠️ near-dup of 17.5 |
| 16.5 | 2 | About-band body | ⚠️ **fractional** |
| 16 | 4 | Button label | `normal` |
| 15.5 | 2 | Body | ⚠️ **fractional** |
| 15 | 2 | Body / labels | ⚠️ near-dup |
| 14.5 | 3 | Nav links, body | ⚠️ **fractional** |
| 14 | 11 | Card body, footer links | `small` |
| 13.5 | 4 | Footer blurb, labels | ⚠️ **fractional** |
| 13 | 14 | Micro-labels, card CTAs | `x-small` |
| 12.5 | 2 | Copyright | ⚠️ **fractional** |
| 12 | 12 | Eyebrows, footer labels | `xx-small` |
| 11 | 3 | Header "TORONTO" eyebrow | `xxx-small` |

The seven fractional half-pixel sizes (`12.5, 13.5, 14.5, 15.5, 16.5, 17.5`) look like design-tool drift rather than intent. **Recommendation:** collapse to a 9-step scale — `11, 12, 13, 14, 16, 18, 22, 30, 46` — with `34` and `52` as two named display sizes. That maps all 22 values with a worst-case shift of 1px. I want your sign-off before it goes into `theme.json`.

**Line heights in use:** `1, 1.1, 1.12, 1.15, 1.2, 1.25, 1.3, 1.35, 1.4, 1.5, 1.6, 1.65, 1.7, 1.75, 2.1`. Recommend four: `1.15` (display), `1.35` (heading), `1.65` (body), `2.1` (footer link lists).

**Letter-spacing:** `3px` (12px eyebrow), `2.4px` (11px), `2px` (12px), `1.5px` (13px), `.5px`. These are all "uppercase micro-label" tracking — recommend one `em`-relative token (~`0.25em`) instead of five px values, which also makes them scale correctly.

### 2.3 Spacing

| Value | Uses | Role |
|---|---|---|
| **48px** | every section | **Horizontal page gutter — perfectly consistent, becomes root padding** |
| 64px | 5 | Large section vertical padding |
| 56px | 5 | Standard section vertical padding |
| 40px / 44px / 30px | few | Section padding variants |
| 22px 20px | 5 | Card padding (pathway cards) |
| 28px 30px, 30px 34px, 36px 38px, 26px, 28px | 1 ea. | ⚠️ Larger card paddings, all slightly different |
| 13px 15px | 4 | Input padding |
| 15px 28px / 14px 28px / 15px 30px | 1 ea. | ⚠️ Primary button padding, three variants |
| 10px 18px / 12px 22px / 10px 20px | 1 ea. | ⚠️ Small button padding, three variants |

**Gaps:** `8, 10 (×8), 12, 14, 16, 18, 20, 24, 26, 28, 40, 56, 120`.

Recommended spacing scale (covers everything within ~2px): **`8, 12, 16, 20, 24, 32, 40, 48, 56, 64`**, plus `120px` treated as a one-off on the stats row (or replaced by `space-between` in a max-width container).

⚠️ Flags: button padding has three near-identical variants; large-card padding has five. Both should normalise to one each.

### 2.4 Radii

| Value | Uses | Proposed |
|---|---|---|
| `999px` | 8 | `pill` |
| `50%` | 9 | `round` (avatars, dots, icon buttons) |
| `12px` | 10 | `md` — the default card radius |
| `14px` | 5 | ⚠️ near-dup of 12 — hero image, some cards |
| `9px` | 4 | `sm` — inputs |
| `10px` | 1 | ⚠️ near-dup of 9 |
| `16px` | 1 | ⚠️ near-dup of 14 |
| `6px` | 1 | Prototype placeholder label only |

**Recommendation:** three radii — `sm: 9px`, `md: 12px`, `lg: 16px` — plus `pill` and `round`.

### 2.5 Shadows

Only four in the entire system:

| Value | Where |
|---|---|
| `0 1px 2px rgba(0,0,0,.15)` | Header CTA pill — ⚠️ only black-based shadow |
| `0 6px 24px rgba(33,55,44,.06)` | Soft card lift |
| `0 8px 24px rgba(33,55,44,.14)` | "Next meeting" floating card |
| `0 12px 32px rgba(33,55,44,.12)` | Hero image |

Recommend three tokens (`sm`, `md`, `lg`) with the header CTA moving to the ink-based `sm`.

### 2.6 Contrast — ✅ resolved, fixing under §0.1

Two combinations look likely to fail WCAG AA for normal text. **Per §0.1 these will be measured and corrected** rather than shipped as drawn:

- **`#CE5F35` accent on `#F8F5EC` cream** — used for the 12px uppercase eyebrows and the 13px card CTAs. My rough estimate is ≈3.9:1, i.e. **below the 4.5:1 required for text under 18.66px bold / 24px regular.**
- **`#7A8B7E` subtle on cream** — used for meta text; estimated below 4.5:1.
- `#A9BCA6` on `#2F5D47` (stat labels) is worth checking too.

These are estimates from the hex values, not measured — the build starts by measuring them properly. The corrected values become the tokens; the drawn values are kept as comments so the change stays traceable.

### 2.7 Breakpoints

**None exist.** We are defining these, not extracting them. Proposed, subject to §8.2:

| Name | Width | Purpose |
|---|---|---|
| `sm` | ≤ 600px | Single column; nav overlay |
| `md` | 601–899px | 2-up card grids |
| `lg` | 900–1199px | 3-up cards, hero still 2-col |
| `xl` | ≥ 1200px | Full 5-up pathway grid, design as drawn |

Note the 5-up pathway grid at the drawn 1280px gives ~200px cards; below ~1100px they become unusable, so the 5→3→2→1 reflow is a real design decision, not a mechanical one.

---

## 3. Utility-class translation

Not applicable in the usual sense — the prototypes use **inline `style` attributes**, not utility classes. There is no framework to strip. Everything becomes `theme.json` tokens plus semantic CSS, as you asked. The `style-hover="…"` attributes are a Design-Compiler-ism with no HTML equivalent; those become real `:hover`/`:focus-visible` rules in the theme stylesheet.

---

## 4. Section → implementation mapping

Principle applied throughout: **`theme.json` first**, then core blocks in a pattern, then a class + hand-written CSS, and only then custom code. I've flagged the two places where core genuinely can't reach.

### 4.1 Site header + mobile navigation — `parts/header.html` (+ overlay part)

**Approach: template part with `core/navigation`, using WordPress 7.0's navigation overlay template part.**

This is the part you asked me to call out specifically, so here it is in full.

**Do not hand-roll the offcanvas menu.** I verified against this install (`wp-includes/js/dist/script-modules/block-library/navigation/view.min.js`) that core's Navigation block ships an Interactivity-API view module that already handles:

- `aria-modal` / role semantics on the open overlay
- **Escape** to close, returning focus to the toggle
- **Tab / Shift+Tab focus cycling** within the overlay (a real focus trap, not just `autofocus`)
- open/close state on the toggle via `aria-expanded`

Re-implementing this by hand would mean shipping our own focus trap, keyboard handler, and inert-background logic, and maintaining it against core changes, to end up in the same place. The accessible behaviour you want is already the default.

What WP 7.0 adds that makes this fully designable — and why the version matters — is:

- `WP_TEMPLATE_PART_AREA_NAVIGATION_OVERLAY`, a dedicated template part area (confirmed at `wp-includes/block-template-utils.php:23`), so the overlay's *contents and layout* are ordinary blocks we design freely.
- `core/navigation-overlay-close`, a stylable close button block.
- When a custom overlay part is used, core adds `disable-default-overlay` and steps back from styling it, letting our CSS own the appearance (`wp-includes/blocks/navigation.php`).
- Core also auto-disables nested overlays inside the overlay part, so we can't accidentally create an inception bug.

**Plan:**

- `parts/header.html` — flex group: logo lockup on the left, `core/navigation` with `overlayMenu: "mobile"` on the right, terracotta CTA pill.
- `parts/navigation-overlay.html` — area `navigation-overlay`. Full-screen cream panel: close button, large-type vertical nav list, CTA pill, contact line. Designed generously, since we have no prototype for it (see §8.1).
- Hamburger/close icons: core's defaults, restyled via CSS to match. If the design calls for a different glyph, core supports a custom icon attribute.
- **Logo lockup**: `core/site-logo` (circular "MA" mark, 44px) + a group of two lines. The "Marijuana Anonymous" / "TORONTO" pair is site title + a static line, not site tagline — the tagline field is better left for SEO. Proposed as fixed markup in the part.
- **Active nav state** (green text + 2px terracotta underline): pure CSS on `.current-menu-item` / `[aria-current="page"]`. No custom code.
- **CTA pill**: `core/button` with a `is-style-pill-accent` block style.

**One caveat to flag:** the 8 nav items + wordmark + CTA pill will not fit comfortably much below ~1150px. The `overlayMenu: "mobile"` breakpoint is core's ~600px. We will likely need to force the overlay earlier via CSS, or reduce the top-level nav to 5 items with the rest in the overlay. That's a design decision — §8.3.

### 4.2 Hero — pattern `ma-toronto/hero-home`

**Approach: block pattern of core blocks + a small amount of hand-written CSS.**

- Outer `core/group`, full width, gradient background **defined in `theme.json` as a named gradient** so it stays editable.
- `core/columns` at 52.5% / 47.5% (matches `1.05fr/.95fr`).
- Left: `core/paragraph` (eyebrow, `is-style-eyebrow`), `core/heading` h1, `core/paragraph` lede, `core/buttons` with a filled pill + a `is-style-underline-link` variant.
- Right: `core/image` with radius + shadow from `theme.json`.

#### ⏸️ "Next meeting" card — DEFERRED to the Meetings phase

Parked at your request. It has to be genuinely dynamic, and it can't be designed sensibly before the meetings data model exists — a card that displays the next meeting is a *view onto the meetings system*, so building it first would mean guessing at the shape of data we haven't defined.

Carried forward for that scoping conversation:
- Where meeting data lives: CPT + ACF, a taxonomy for location/format, or an external MA feed.
- Timezone handling — "Tonight 7:30 PM" is America/Toronto; caching a rendered time is a classic way to serve the wrong one.
- Whether the card is dynamic on the server (correct for SEO, needs cache-awareness) or hydrated client-side (always fresh, invisible to crawlers).
- Whether it recurs on other pages — if so, Block Bindings rather than a repeated pattern (§10.3).
- Empty state: what shows when there is no next meeting, or when the feed fails.

**Effect on Phase 1** — a simplification. The card was the only thing in the hero needing overlap positioning, so the ~10 lines of negative-offset CSS are gone and **the hero becomes pure core blocks**. The hero's right column is now just the image. Phase 1's only hand-written layout CSS is now the stretched-link on the pathway cards.

The content risk I raised is also resolved by deferring: no hardcoded meeting time can go stale, because there won't be one.

### 4.3 Pathway cards — pattern `ma-toronto/pathway-cards`

**Approach: block pattern, core blocks, plus one CSS technique.**

- `core/columns` (5) or a `core/group` with grid layout; each card a `core/group` with border, radius, padding from `theme.json`.
- Title `core/heading` h3, description `core/paragraph`, CTA `core/paragraph` with the arrow.
- `margin-top:auto` on the CTA → `justify-content: space-between` on a flex group, expressible in block layout.

**Where core can't reach:** in the prototype the *entire card* is one `<a>`. Core has no "link wrapper" block, and nesting blocks inside an anchor isn't valid block markup.

**Proposal:** put the real link on the card's heading, then use a **stretched-link pseudo-element** (`.ma-card a::after { position:absolute; inset:0; }`) so the whole card is clickable. ~6 lines of CSS. This is also the more accessible result: screen reader users get one meaningful link with real link text instead of an anchor wrapping three text nodes. No custom block needed.

The 5→3→2→1 reflow is CSS on the pattern's grid.

### 4.4 About band + counters — pattern `ma-toronto/about-band`

**Approach: pure core blocks + `theme.json`. No custom code.**

- Full-width `core/group`, `primary` background, constrained inner width 820px.
- `core/heading` h2, two `core/paragraph`s (second uses the `sand` colour).
- Counters: `core/columns`, two `core/group`s with a big number + uppercase label, `core/separator` (vertical variant) between them — or a CSS border on the second column, which is simpler and matches the 1px rule in the design.

If the two stat figures need to be live rather than typed, see §8.4.

### 4.5 Quotes slider — ⏸️ **DEFERRED, not built this pass**

Parked at your request, to be scoped before anything is written. **No custom block is built in Phase 1**, which means Phase 1 now contains **zero custom blocks** — everything remaining is core blocks, `theme.json`, and about 20 lines of CSS.

**Carried forward for the scoping conversation:**
- Does it need to be a carousel at all, or would a static set of quotes serve the same purpose? Core has no slider block, so "carousel" is the expensive answer and every alternative is cheap.
- If it is a carousel: a small custom block on the **WordPress Interactivity API** (the mechanism core itself uses for Navigation, Search, and Query) is the route I'd recommend — no third-party dependency, no bundler, ships with WP 7.0, degrades to a readable list without JS.
- Who owns the quote list — hard-coded, a block attribute, or editable inner `core/quote` blocks?
- **The prototype's 6-second autoplay with no pause control fails WCAG 2.2.2 (Pause, Stop, Hide).** Any interactive version needs a pause control, `prefers-reduced-motion` handling, `aria-live="polite"`, and labelled dots. That's a real argument for dropping autoplay entirely.

**What fills the slot meanwhile.** The homepage would otherwise have a hole between the green about band and the footer. Unless you say otherwise I'll build a **static pull-quote section** as a pure-core pattern (`patterns/quotes-static.php`) — eyebrow plus one italic Lora quote at the drawn size, same padding and background, no controls. It matches the design's typography and spacing, carries no JS, has no accessibility debt, and is a clean swap-out once the real component is scoped. Say so if you'd rather leave the section out entirely for now.

### 4.6 Site footer — `parts/footer.html`

**Approach: template part, pure core blocks.** `core/columns` at 35/21.7/21.7/21.7, brand lockup + blurb, three link columns. Use `core/navigation` for the link columns only if editors want menu management there; otherwise `core/list` is lighter and easier to keep exactly as drawn. Bottom bar is a `core/separator` + `core/paragraph`. Reflows 4→2→1.

### 4.7 Summary

| Section | theme.json | Pattern | Template part | Custom CSS | Custom block |
|---|---|---|---|---|---|
| Header | ✅ | — | ✅ `header` | small | — |
| Mobile nav overlay | ✅ | — | ✅ `navigation-overlay` | small | **not needed** (core handles it) |
| Hero | ✅ | ✅ | — | — | — |
| ↳ "Next meeting" card | ⏸️ | — | — | — | **deferred — §4.2** |
| Pathway cards | ✅ | ✅ | — | ~6 lines (stretched link) | — |
| About + counters | ✅ | ✅ | — | minimal | — |
| Quotes slider | ⏸️ | ⏸️ static placeholder | — | — | **deferred — §4.5** |
| Footer | ✅ | — | ✅ `footer` | minimal | — |

**Zero custom blocks in Phase 1**, now that the slider is deferred. Everything is core blocks, `theme.json`, and roughly 20 lines of positioning CSS — which also means no build step and no `node_modules` in the shipped theme.

---

## 5. Shared vs. home-specific

**Shared — built now, carried by every later page:**

| File | Why shared |
|---|---|
| `parts/header.html` | Identical on all 8 prototypes |
| `parts/navigation-overlay.html` | Same |
| `parts/footer.html` | Identical on all 8 prototypes |
| `theme.json` | Whole design system |
| `assets/css/*` | Card, button, eyebrow, link primitives all recur |
| `patterns/pathway-cards.php` | Card grid recurs on other pages |

**Home-specific:**

| File | Why |
|---|---|
| `templates/front-page.html` | Composes hero + cards + about + slider |
| `patterns/section-hero.php` | Hero layout is unique to the homepage |
| `patterns/about-band.php` | Green band + counters, homepage only so far |
| `patterns/quotes-static.php` | Placeholder for the deferred slider (§4.5) |

Since the header/footer carry to every page, I'd rather spend proportionally more care there than on the homepage-only sections.

---

## 6. Proposed file structure

All paths relative to `wp-content/themes/ma-toronto/`.

```
ma-toronto/
├── style.css                     # Theme header + minimal root CSS (metadata needs filling in — Version and
│                                 #   Description are currently empty)
├── theme.json                    # §2 design system: palette, gradients, font families + sizes,
│                                 #   spacing scale, radii, shadows, layout, per-block styles
├── functions.php                 # Small. Registers: pattern categories, block styles
│                                 #   (pill-accent, eyebrow, underline-link), wp_enqueue_block_style()
│                                 #   for per-block CSS, and self-hosted fonts
├── .distignore                   # docs/, tests/, node_modules/, .playwright/, design/, *.log
│
├── templates/
│   ├── index.html                # Fallback — replaced (currently CBT boilerplate)
│   └── front-page.html           # ← HOMEPAGE: header part + hero + cards + about + slider + footer part
│
├── parts/
│   ├── header.html               # ← §1.1  Site header
│   ├── navigation-overlay.html   # ← §4.1  Offcanvas mobile menu (WP 7.0 overlay area)
│   └── footer.html               # ← §1.6  Site footer
│
├── patterns/
│   ├── section-hero.php          # ← §1.2  Hero (next-meeting card deferred, §4.2)
│   ├── pathway-cards.php         # ← §1.3  5-up card grid
│   ├── about-band.php            # ← §1.4  Green band + counters
│   └── quotes-static.php         # ← §1.5  Static pull-quote (slider deferred, §4.5)
│
├── assets/
│   ├── css/
│   │   ├── card.css              # Stretched-link + hover border
│   │   ├── header.css            # Nav active state, overlay panel, CTA pill
│   │   └── footer.css
│   ├── fonts/                    # Self-hosted Lora + Karla woff2 (see §7)
│   └── images/                   # Logo mark, hero photo
│
└── styles/                       # theme.json style variations — empty for now,
                                  #   reserved in case a dark or high-contrast variant is wanted
```

**Section → file map**

| Homepage section | Primary file |
|---|---|
| Site header | `parts/header.html` + `assets/css/header.css` |
| Mobile nav | `parts/navigation-overlay.html` |
| Hero | `patterns/section-hero.php` (no custom CSS — card deferred) |
| Pathway cards | `patterns/pathway-cards.php` + `assets/css/card.css` |
| About + counters | `patterns/about-band.php` |
| Quotes section | `patterns/quotes-static.php` (slider deferred) |
| Footer | `parts/footer.html` + `assets/css/footer.css` |
| All tokens | `theme.json` |

`patterns/` uses `.php` (not `.html`) so patterns get header comments for title/category/`inserter` state and can be translated — the standard block theme approach, same as Twenty Twenty-Five.

---

## 7. Dependencies and exclusions

**Third-party dependencies — flagging as requested:**

| Dependency | Purpose | Recommendation |
|---|---|---|
| **Lora + Karla** (Google Fonts) | Typography, already in the prototypes | **Self-host** in `assets/fonts/`, declared via `theme.json` `fontFace`. Both are SIL OFL, so bundling is fine. Self-hosting removes a third-party request (a GDPR/PIPEDA consideration for a site whose visitors are seeking addiction support — I'd treat this as a privacy requirement, not a performance nicety), and makes the Playwright diff deterministic. **Needs your approval to add the files.** |
| **Playwright** | Step B visual diffing | Dev-only. `node_modules/` + browser install gitignored and `.distignore`d. |
| Everything else | — | None. No slider library, no CSS framework, no build step. |

**Existing plugins:** ACF is installed and may matter for the "next meeting" data (§8.4) — worth deciding whether it stays. Blocksy/Blocksy Companion are deactivated; recommend deleting them once the theme is live so their CSS can't be reintroduced.

**Keeping `design/` out of production:**

1. `.distignore` in the theme (listed above) — correct for `wp dist-archive`, but as noted in §0 it **cannot reach `design/` at the WP root**.
2. So `design/` also needs excluding wherever you actually deploy from. If you deploy by rsync/SFTP, add an exclude; if by git, `.gitattributes` `export-ignore` covers archive exports.
3. Simplest alternative: **move `design/` inside the theme** (e.g. `ma-toronto/design/`), where `.distignore` covers it properly. That would also match the brief's assumption. Your call — see §8.10.

Also note `database/matoronto.sql` sits in the repo root and is currently untracked; it should not deploy either, and probably shouldn't be committed.

---

## 8. Open questions — needed before building

Ordered by how much they block work.

### 8.1 Who is allowed to change the words on the homepage? *(blocking)*

Plainest possible version, because my first attempt at this question was jargon:

Think of the homepage like a poster on the wall.

Some parts of the poster I can put on with **sticky notes**. Anyone at MA Toronto can peel a sticky note off and write a new one. They don't need me. They just log in, click the words, and type. The risk is that someone types too much and the poster looks messy.

Other parts I can **glue down**. Nobody can peel those off by accident, so the poster always looks right — but if you ever want those words changed, you have to ask me or another developer to do it.

**My question is: which parts get sticky notes, and which parts get glued down?**

I need to know before I build, because sticky notes and glue are built differently. Changing my mind later means rebuilding that piece.

Here is the list. For each one: sticky note, or glued?

| Part of the homepage | Sticky note (they change it) | Glued (they ask us) |
|---|---|---|
| The big sentence at the top — "You are no longer alone." | ☐ | ☐ |
| The paragraph under it | ☐ | ☐ |
| The "Find a Meeting" button | ☐ | ☐ |
| The "Next meeting: Tonight 7:30 PM…" box | ☐ | ☐ |
| The words on the 5 cards (Meetings, A Solution, …) | ☐ | ☐ |
| **How many cards there are** (5 today — could they add a 6th?) | ☐ | ☐ |
| The green section's paragraphs | ☐ | ☐ |
| The two big numbers (`392+` and `$366,218+`) | ☐ | ☐ |
| The menu at the top of every page | ☐ | ☐ |
| The link lists in the footer | ☐ | ☐ |

### ✅ **ANSWERED — using best judgment**

**Sticky notes for every word. Glue under everything else.** Delivered via `templateLock: "contentOnly"` (§10.3), which is better than either extreme: editors retype any text and swap any image, and the design controls that would let them wreck the layout are not present in the interface at all.

Specific calls:

| Part | Decision |
|---|---|
| Hero headline, paragraph, button label + URL | Editable text |
| "Next meeting" box | Editable text (dynamic deferred — §8.4) |
| The 5 cards' words and links | Editable text |
| **Number of cards** | **Locked at 5.** Adding a 6th breaks the 5-up grid — the exact accident you want to prevent. CSS will tolerate 4 or 6 so we can change it deliberately later |
| Green band paragraphs, the two big numbers | Editable text |
| Top menu, footer links | Fully editable — normal menu management, no layout risk |
| All spacing, colour, alignment, block order | Locked |

### 8.2 Page container — ✅ **ANSWERED (revised)**

**Final decision: sections stretch to the window. No cap.**

The original answer below said cap at 1184px. That was never actually in effect --
full-width sections use flow layout with explicit padding, and `wideSize` only
applies to *constrained* layouts. When this came to light the call was to keep the
stretching behaviour, which also matches the prototype (it has no max-width). At
1920px, content spans 1824px.

Consequence: **1440px is now a valid fidelity check**, not an expected mismatch,
because the prototype and the build behave identically above 1280. The visual
harness treats it as one.

<details><summary>Superseded answer</summary>

**`wideSize: 1184px`, root padding 48px.** The prototype is drawn at 1280px with 48px gutters, so its true content width is exactly **1280 − 96 = 1184px**. Setting `wideSize` to that reproduces the drawn composition precisely and stops it stretching beyond; full-bleed bands (green about band, footer) stay edge-to-edge as designed. **Correction from actually running the harness:** I first wrote that this would make the 1440px diff near-exact. It won't. The prototype has *no* max-width, so at 1440px its content stretches to 1344px while ours caps at 1184px — they can't line up. The meaningful comparison is at **1280px**, the prototype's native design width, where our 1184px content box matches it exactly. The harness now captures 1280px as the fidelity check, with 1440px and 390px kept as our own responsive-regression baselines.

`contentSize: 820px` matches the About band's constrained column. Prose blocks additionally get a `ch`-based measure, as the prototypes already do (52ch / 60ch / 70ch).
</details>

<details><summary>Original question</summary>

The prototypes were drawn at exactly 1280px and **have no max-width on any section** — content is full-bleed with a 48px gutter. On a 1440px or 2560px monitor the hero and 5-up grid will keep stretching. This directly affects your 1440px screenshot diff: the prototype and our build will *both* stretch, but any container cap we add will show as a diff.

Options: (a) cap content at ~1200px centred, gutter grows — my recommendation; (b) cap at 1440px; (c) no cap, stretch forever as drawn. Needs your call before `theme.json` layout is set.
</details>

### 8.3 Navigation breakpoint and item count — ✅ **ANSWERED**

**Overlay engages below 1100px**, above core's ~600px default, because 8 items + wordmark + CTA stop fitting around ~1150px. All items go in the overlay; nothing is hidden from mobile users. The overlay is a **full-screen cream panel** — more forgiving than a drawer at small sizes, and it gives the items room to be large tap targets. No prototype exists for it, so I'm designing it from the established tokens; it's the one screen I'll want you to look at before it's final.

<details><summary>Original question</summary>

8 top-level items + wordmark + CTA stop fitting around ~1150px. Do we (a) switch to the overlay early (~1100px), (b) cut top-level items to 5 with the rest in the overlay only, or (c) allow a smaller nav font between 900–1150px? Also: what does the overlay panel look like — full-screen cream, or a side drawer? No prototype exists for it.
</details>

### 8.4 Dynamic data — ✅ **RESOLVED by deferral**

- **"Next meeting" card** — ⏸️ deferred to the Meetings phase (§4.2). Not built, not faked.
- **The two statistics** (`392+`, `$366,218+`) — built as **editable text**. These change rarely and have no timezone or freshness hazard, so a volunteer retyping them once a year is the proportionate answer. If they turn out to come from MA World Services, they can be bound to a field later without redesigning the section.
- **Meeting/event structured data** — belongs with the Meetings build, not here.

<details><summary>Original question</summary>

- **"Next meeting: Tonight 7:30 PM — Never Alone · CAMH, 100 Stokes St"** — is this pulled from real meeting data? If so, where does that data live on the current site — a CPT, ACF fields, an external MA feed? This decides whether we need a second custom block.
- **`392+` meetings worldwide / `$366,218+` saved** — typed by an editor, or fetched from somewhere?
- Do meetings need structured data (schema.org `Event`) for search? Relevant since Yoast is installed.
</details>

### 8.5 Assets

- The hero image is a grey placeholder labelled "photo: Toronto skyline at dawn (soft, muted)". Is there a real photo?
- The logo is a CSS circle with the letters "MA". Is there a real logo file (SVG preferred), or do we build the mark in CSS as drawn?
- `design/uploads/` holds three inspiration PNGs and a PDF — reference only, or assets to use?

### 8.6 Colour contrast — ✅ **ANSWERED**

Resolved by the standing latitude in §0.1. Fixing to AA; drawn values preserved in comments.

### 8.7 Token normalisation — ✅ **ANSWERED, applying §2 proposals**

Applying all of it: 22 font sizes → 11; six creams → three; one body green; three radii; one letter-spacing token. Worst-case visual shift is 1px on a handful of text elements. Original values recorded as comments in `theme.json` so any collapse can be reversed individually.

<details><summary>Original question</summary>

Specifically: collapse 22 font sizes to ~11? Collapse the six near-duplicate creams to three? One body-text green instead of two? Three radii instead of six? One letter-spacing token instead of five?
</details>

### 8.8 The quote slider — ⏸️ **DEFERRED**

Out of this pass. Scoping questions carried forward in §4.5. The only live question is whether the static placeholder pattern is wanted meanwhile — my assumption is yes.

### 8.9 Front page wiring — ✅ **ANSWERED**

Resolved by §10.4. `front-page.html` is top of the template hierarchy for the front page whether or not a static page is set, so it renders with **zero database writes**. Your constraint holds.

<details><summary>Original question</summary>

Using `templates/front-page.html` means it applies automatically when a static front page is set. **Setting the front page is a `wp_options` write**, which your no-database-writes constraint excludes from this pass. Confirm that's handled in the separate content migration, or tell me you want `front-page.html` to work off the blog index instead.
</details>

### 8.10 `design/` location — ✅ **ANSWERED**

**Leaving `design/` where it is.** Being outside the theme, it cannot ship with the theme — the risk was overstated in the first draft. Adding `.distignore` to the theme regardless, plus a documented deploy exclude. No file moves, no churn to the Playwright paths.

Related: `database/matoronto.sql` at the repo root should also never deploy, and probably shouldn't be committed. Adding both to `.gitignore` / deploy excludes.

<details><summary>Original question</summary>

Move `design/` inside the theme so `.distignore` genuinely covers it, or leave it at the repo root and handle exclusion in the deploy path?
</details>

### 8.11 Missing pages — ✅ **ANSWERED**

Building the menu with the **6 items that have designs**, omitting "Our Stories" and "7th Tradition" until those pages exist. Links to `#` are bad for users and for SEO, and the menu is editable, so adding them later is a two-minute job with no code change.

<details><summary>Original question</summary>

Nav links "Our Stories" and "7th Tradition" point to `#` — no prototype exists. Placeholder menu items for now, or omit until those pages are designed?
</details>

---

## 9. What happens after you approve

Per your instruction, before any theme code:

- **A.** Write `wp-content/themes/ma-toronto/CLAUDE.md` — tokens from §2 (as normalised by your answers to §8.7), file structure and naming from §6, the no-database-writes rule, read-prototypes-from-disk-in-sections, and Twenty Twenty-Five as reference-only. Kept short since it loads every session.
- **B.** Set up Playwright: screenshot the rendered homepage at 1440px and 390px, screenshot `design/Home.dc.html` at the same widths, diff them. Script it, confirm it runs against `http://localhost:8888/matoronto`, and gitignore `node_modules/` + the browser install.

Two notes on B, so it isn't a surprise later:
- The **390px comparison is not a fidelity check** (§1) — the prototype has no mobile layout. I'll wire it up as a self-regression baseline and label it as such.
- The prototypes render through `support.js` (a client-side runtime) and load fonts from Google. I'll add a settle/wait step and, once fonts are self-hosted, screenshot against local fonts so the diff isn't network-dependent. `design/Home-standalone.html` (3MB, fully bundled) is the more reliable capture target than `Home.dc.html`, and I'll verify which renders identically.

---

## 10. Build architecture — how this gets assembled

Driven by two requirements you gave: **an editor must not be able to break the layout by accident**, and **modularise as much as possible**.

### 10.1 What "modular" means here, concretely

Not an abstraction for its own sake. Four properties, each of which I can point at a file for:

1. **One concept lives in exactly one place.** A colour is defined once in `theme.json`. Changing the green means editing one line, not grepping for `#2F5D47`.
2. **Sections are swappable units.** Each homepage section is one pattern file. Deleting the About band = deleting one line from the template. Reordering sections = reordering lines.
3. **Appearance is separable from composition.** "Green band on dark" is a named *section style* in its own JSON file, not styling baked into markup. Re-skin without touching structure.
4. **Nothing loads that isn't used.** Per-block CSS registered via `wp_enqueue_block_style()` only ships when that block actually renders.

The practical payoff: several decisions in §8 stop being expensive. Because every section is a self-contained pattern, moving the homepage from a template to a static Page later, or re-theming the palette, doesn't mean rewriting sections.

### 10.2 The layer stack

```
theme.json              <- tokens: colour, type, spacing, radii, shadows, layout
  v referenced by
styles/blocks/*.json    <- named block styles: eyebrow, pill-accent, card
styles/sections/*.json  <- named section styles: green-band, cream-surface
  v applied by name in
patterns/*.php          <- one file per homepage section (the modules)
  v composed by
templates/front-page.html + parts/*.html   <- thin assembly, ~10 lines
  v hardened by
templateLock: contentOnly                  <- the anti-breakage layer
```

Rule enforced throughout: **a layer may only reference the layer above it.** No pattern hardcodes a hex value; no template contains layout logic. Anything violating that is a bug.

### 10.3 Anti-breakage — the actual mechanism

Revision-rollback is the last resort, not the plan. WordPress has real locking, and this install supports all of it (verified in `wp-includes/blocks/blocks-json.php` and `wp-includes/block-bindings/`).

**Primary: `templateLock: "contentOnly"`** on each section's outer group. This is the exact behaviour you described wanting:

| Editor can | Editor cannot |
|---|---|
| Click any text and retype it | Move a block |
| Swap an image | Delete a block |
| Edit link URLs | Insert a new block |
| - | Change colours, fonts, spacing, alignment |

In `contentOnly` mode the block toolbar's design controls are hidden entirely and List View shows only editable fields. There is no button to press that rearranges the layout - the failure mode you're worried about stops being reachable through the UI.

**Secondary: per-block `lock`** - `{"lock":{"move":true,"remove":true}}` for individual blocks needing protection inside an otherwise open area.

**Held in reserve: Block Bindings / pattern overrides.** Warranted if the same content (the "Next meeting" card) ends up on several pages and must stay in sync from one source. Overkill for the homepage alone; noted so we recognise the moment it's justified.

**One honest limit:** `contentOnly` is enforced by the **editor UI**, not by user capability. An administrator who opens the Code Editor can still edit raw markup. It reliably prevents *accidents*, which is what you asked for - it is not a permissions boundary against someone determined. If you later want a genuine boundary, that's a capability/role change and a different piece of work.

**Recovery, in order of severity:**
1. Undo (Cmd-Z) in the editor.
2. Template revisions in the Site Editor.
3. **"Clear customizations"** - resets the template to the theme file. Because the layout lives in git-tracked theme files rather than the database, this is a genuine restore-to-known-good, not a partial one. This is the strongest argument for template-based over page-based content, and the reason for §10.4.

### 10.4 Where the homepage content lives - decided

**`templates/front-page.html`**, with locked patterns, rather than a static Page.

Reasoning: it keeps the git-tracked theme file authoritative, which makes recovery total; and `front-page.html` sits at the top of the template hierarchy for the front page **whether or not a static page is set** - so it renders immediately with **no database write**, resolving §8.9 within your constraint.

Trade-off, stated plainly: editors reach it via Appearance -> Editor rather than the more familiar Pages screen. If that proves awkward for volunteers, moving the sections into a Page is cheap precisely because they're patterns - the pattern files don't change, only where they're inserted. I'd rather see it working first than guess now.

### 10.5 Build sequence

Each step ends at a checkpoint where you can look at something real.

| Step | What | Checkpoint |
|---|---|---|
| **0** | `CLAUDE.md` (your step A) + Playwright harness (your step B) | Screenshot script runs against localhost |
| **1** | `theme.json` - full token set from §2, normalised per §8.7, contrast-corrected per §0.1 | Site Editor shows the real palette and type scale |
| **2** | `styles/blocks/*.json`, `styles/sections/*.json`, block style registrations | Named styles appear in the editor |
| **3** | `parts/header.html`, `parts/navigation-overlay.html`, `parts/footer.html` | **Keyboard + screen reader pass on the nav before anything else** - it's on every page, so a defect here is a site-wide defect |
| **4** | `patterns/` - hero, pathway-cards, about-band, quotes-static | Each pattern inserted and reviewed in isolation |
| **5** | `templates/front-page.html` assembly + `contentOnly` locks | **You try to break it in the editor.** That's the acceptance test for the locking approach |
| **6** | Per-block CSS modules + responsive reflow (5->3->2->1) | Renders correctly 390 -> 1440 |
| **7** | Verification: 1440px Playwright diff, keyboard traversal, contrast measurement | Diff reviewed, deviations explained |

Step 3 before step 4 is deliberate: header and footer carry to every later page, so they earn the most scrutiny. Step 5's checkpoint is you actively trying to break the layout - if you can do it accidentally, the locking is wrong and I fix it before we go further.

### 10.6 Naming conventions (locked in now, since they're hard to change later)

| Thing | Convention | Example |
|---|---|---|
| Pattern slug | `ma-toronto/section-*` | `ma-toronto/section-hero` |
| Pattern file | kebab-case, matches slug | `patterns/section-hero.php` |
| Pattern category | one custom category | `ma-toronto` |
| Block style | `is-style-*` | `is-style-eyebrow` |
| Section style file | numbered for editor ordering | `styles/sections/01-green-band.json` |
| CSS class | BEM-ish, `ma-` prefixed | `.ma-hero__card` |
| CSS file | matches its block/section | `assets/css/hero.css` |
| theme.json slug | kebab-case, semantic not literal | `primary`, not `green-2f5d47` |

Semantic token names matter for point 1 of §10.1: `accent` survives a rebrand, `terracotta` doesn't.

---

**Follow-ups parked, both to be scoped before they're written:**
- The quotes slider (§4.5).
- The "Next meeting" card (§4.2) — blocked on the Meetings data model, so it naturally belongs to that phase.

**No files have been created or modified other than this plan.** Nothing has been written to the database. Ready for your review.
