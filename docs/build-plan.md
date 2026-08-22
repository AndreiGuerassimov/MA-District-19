# MA Toronto — Block Theme Build Plan (Phase 1: Homepage)

**Status:** planning only, no code written.
**Scope:** `design/Home.dc.html` plus the two components it imports (`SiteHeader.dc.html`, `SiteFooter.dc.html`). Other pages in `design/` are out of scope for this pass.

## 0. Environment as found on disk

Several placeholders in the brief resolved differently than assumed. Confirming what is actually here:

| Item | Brief said | Actually |
|---|---|---|
| WordPress | `[version]` | **7.0.3** |
| PHP | `[version]` | **8.5.5** (CLI) |
| Local stack | `[wp-env / DDEV / Local]` | MAMP-style Apache at **http://localhost:8888/matoronto** (returns 200) |
| Workspace root | `wp-content/themes/` | **Repo root is the whole WP install** (`/Users/andre/Sites/matoronto`), git-tracked from there |
| Theme slug | `[theme-slug]` | **`ma-toronto`** — active, Create Block Theme skeleton |
| `design/` location | "alongside the theme" | **WP install root**, i.e. *outside* `wp-content/themes/ma-toronto/` |

Three consequences worth deciding on early:

1. **`.distignore` cannot exclude `design/` as things stand.** `.distignore` is read by Create Block Theme / `wp dist-archive` and only applies to paths *inside the theme directory*. `design/` sits two levels above it. I will still add a `.distignore` to the theme (covering `node_modules/`, `tests/`, `docs/`, `.playwright/`, and `design/` in case it moves), but excluding `design/` from production actually depends on your deploy path. See §7.
2. WordPress 7.0 ships a **first-class navigation overlay template part area** (`WP_TEMPLATE_PART_AREA_NAVIGATION_OVERLAY`) and a `core/navigation-overlay-close` block. This changes the mobile-nav recommendation substantially — see §4.1.
3. Theme skeleton still has Create Block Theme's default `contentSize: 620px` / `wideSize: 1000px` and a system font stack. All of it gets replaced.

Other plugins present: ACF, Contact Form 7 (+ antispam), Redirection, Yoast, Create Block Theme, and two AI plugins. Blocksy + Blocksy Companion are deactivated as stated.

---

## 1. Homepage section inventory (document order)

`Home.dc.html` is 146 lines of inline-styled markup with a small `DCLogic` script block at the end supplying dynamic values. Sections in order:

| # | Section | Description | One-off or recurring |
|---|---|---|---|
| 1 | **Site header** (`dc-import SiteHeader`) | 82px bar: circular "MA" mark + two-line wordmark ("Marijuana Anonymous" / "TORONTO" eyebrow), 8-item horizontal nav with active-item underline, terracotta CTA pill "Find a Meeting". Full-bleed, 48px gutter, 1px bottom hairline. | **Recurring** — every page |
| 2 | **Hero** | 2-column grid `1.05fr / .95fr`, 56px gap, cream→sand vertical gradient. Left: uppercase eyebrow, `h1` 52px Lora, 18px lede, primary pill button + secondary text link with a 2px underline. Right: 340px image placeholder (radius 14, soft shadow) with an **overlapping "Next meeting" card** absolutely positioned at `left:-18px; bottom:-18px` — green status dot + one line of meeting text. | **One-off layout**; the "Next meeting" card is a *recurring data component* likely wanted on Meetings and elsewhere |
| 3 | **Pathway cards** | 5-up equal grid, 18px gap, on `#FFFDF6`. Each card is a whole-card `<a>`: Lora 18px title, 14px description, terracotta "… →" CTA pinned to bottom via `margin-top:auto`. Hover raises border to primary green. | **Recurring** — same card shape appears across other prototypes |
| 4 | **About band + counters** | Full-bleed primary-green band. Centred 820px column: `h2` 30px Lora, body paragraph, then a highlighted sand-coloured paragraph. Below: two stat blocks (46px Lora numbers `392+` / `$366,218+` with uppercase labels) separated by a 1px vertical rule, 120px gap. | Band is **one-off**; the stat pair is **recurring** |
| 5 | **Quotes slider** | Centred. Eyebrow, then prev/next 44px circular buttons flanking an italic 34px Lora quote, then a row of 4–5 dot buttons. **Interactive:** 5 hard-coded quotes, `setInterval` autoplay every 6s, clicking any control clears the timer. | **One-off** on the homepage; the pattern could recur |
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

### 2.6 Contrast — needs measurement before we commit

Two combinations look likely to fail WCAG AA for normal text and should be measured properly before they go into `theme.json`:

- **`#CE5F35` accent on `#F8F5EC` cream** — used for the 12px uppercase eyebrows and the 13px card CTAs. My rough estimate is ≈3.9:1, i.e. **below the 4.5:1 required for text under 18.66px bold / 24px regular.**
- **`#7A8B7E` subtle on cream** — used for meta text; estimated below 4.5:1.
- `#A9BCA6` on `#2F5D47` (stat labels) is worth checking too.

These are estimates from the hex values, not measured. I'd rather fix the ramp now than retrofit it. Given this is a recovery-support site with a broad audience, I'd treat AA as non-negotiable.

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

**Where core can't reach:** the "Next meeting" card overlaps the image by 18px on two axes and must stay anchored to the image's bottom-left across widths. Core has no negative-offset or overlap primitive, and `theme.json` cannot express it.

**Proposal:** keep it as a `core/group` inside the column with a class (`.ma-hero__card`), and write ~10 lines of CSS for the positioning, including a mobile rule that unsticks it to normal flow below `md`. A classed group + CSS, not a custom block — the markup stays editable in the editor and there is no block registration to maintain.

**Second issue:** the card's content ("Next meeting: Tonight 7:30 PM — Never Alone · CAMH, 100 Stokes St") is clearly meant to be live. See §8.4 — if it needs to be dynamic, this becomes the strongest candidate for a small custom dynamic block, and I'd build it as one rather than fake it.

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

### 4.5 Quotes slider — **custom block** `ma-toronto/quote-slider`

**This is the one place core genuinely cannot reproduce the design.** Being plain about it, as you asked:

There is no carousel/slider block in core. The nearest options and why they don't work:
- `core/gallery` — image-only, wrong semantics.
- Stacking all five quotes — a different design, loses prev/next and dots.
- A third-party slider block — you've ruled out page-builder plugins, and I agree.

**Proposal: a small custom dynamic block using the WordPress Interactivity API** — the same mechanism core uses for the Navigation, Search, and Query blocks. No third-party dependency, no bundler required (the Interactivity API ships with WP 7.0), and it degrades to a readable static list of quotes without JS.

Sketch:
- `blocks/quote-slider/` with `block.json`, `render.php`, `view.js` (script module), `style.css`.
- Quotes stored as an attribute (editable array) or as inner `core/quote` blocks — inner blocks are the better call, since editors then use the normal block editor rather than a custom UI.
- Server-renders all quotes; JS shows one at a time.

**Accessibility notes to build in, and one design flag:** the prototype autoplays every 6 seconds with no pause control. That fails **WCAG 2.2.2 (Pause, Stop, Hide)** — any auto-updating content over 5 seconds needs a mechanism to pause it. I'd add a visible pause/play control, honour `prefers-reduced-motion` by not autoplaying at all, use `aria-live="polite"` on the quote region, and give the dots proper `aria-label`s with current state. Worth a decision: dropping autoplay entirely is simpler and arguably better here.

### 4.6 Site footer — `parts/footer.html`

**Approach: template part, pure core blocks.** `core/columns` at 35/21.7/21.7/21.7, brand lockup + blurb, three link columns. Use `core/navigation` for the link columns only if editors want menu management there; otherwise `core/list` is lighter and easier to keep exactly as drawn. Bottom bar is a `core/separator` + `core/paragraph`. Reflows 4→2→1.

### 4.7 Summary

| Section | theme.json | Pattern | Template part | Custom CSS | Custom block |
|---|---|---|---|---|---|
| Header | ✅ | — | ✅ `header` | small | — |
| Mobile nav overlay | ✅ | — | ✅ `navigation-overlay` | small | **not needed** (core handles it) |
| Hero | ✅ | ✅ | — | ~10 lines (overlap) | only if "next meeting" goes live |
| Pathway cards | ✅ | ✅ | — | ~6 lines (stretched link) | — |
| About + counters | ✅ | ✅ | — | minimal | — |
| Quotes slider | ✅ | — | — | ✅ | **✅ required** |
| Footer | ✅ | — | ✅ `footer` | minimal | — |

**One custom block total.** Everything else is core blocks, `theme.json`, and roughly 20 lines of positioning CSS.

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
| `patterns/hero-home.php` | Hero layout is unique to the homepage |
| `patterns/about-band.php` | Green band + counters, homepage only so far |
| `blocks/quote-slider/` | Homepage only, but built to be reusable |

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
│                                 #   for per-block CSS, the quote-slider block, and self-hosted fonts
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
│   ├── hero-home.php             # ← §1.2  Hero + "Next meeting" card
│   ├── pathway-cards.php         # ← §1.3  5-up card grid
│   └── about-band.php            # ← §1.4  Green band + counters
│
├── blocks/
│   └── quote-slider/             # ← §1.5  Quotes slider (only custom block)
│       ├── block.json
│       ├── render.php
│       ├── view.js               # Interactivity API script module
│       └── style.css
│
├── assets/
│   ├── css/
│   │   ├── hero.css              # Overlap positioning for the "Next meeting" card
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
| Hero | `patterns/hero-home.php` + `assets/css/hero.css` |
| Pathway cards | `patterns/pathway-cards.php` + `assets/css/card.css` |
| About + counters | `patterns/about-band.php` |
| Quotes slider | `blocks/quote-slider/` |
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

### 8.1 Editability — which parts must editors change themselves? *(blocking, as you flagged)*

This determines pattern vs. fixed markup for every section. For each, I need a yes/no:

| Element | If editable | If fixed |
|---|---|---|
| Hero headline / lede / buttons | Pattern in `front-page.html`, editable in place | Locked into the template |
| "Next meeting" card | Editable pattern or dynamic block | Hard-coded |
| Pathway cards — text | Pattern | Template markup |
| Pathway cards — **number of cards** | Must be a pattern with a flexible grid | Fixed 5-up grid, simpler CSS |
| About band copy | Pattern | Template |
| Stat figures (`392+`, `$366,218+`) | Pattern or dynamic | Template |
| Quotes list | Inner blocks in the custom block | Block attribute / hard-coded |
| Footer link columns | `core/navigation` (menu admin) | `core/list` in the part |
| Nav menu items | `core/navigation` (already editable) | — |

My default if you don't want to go item by item: **everything on the homepage becomes editable patterns**, except the quote slider's mechanics and the header/footer structure. Say the word and I'll proceed on that basis.

### 8.2 Page container — what happens above 1280px? *(blocking for layout)*

The prototypes were drawn at exactly 1280px and **have no max-width on any section** — content is full-bleed with a 48px gutter. On a 1440px or 2560px monitor the hero and 5-up grid will keep stretching. This directly affects your 1440px screenshot diff: the prototype and our build will *both* stretch, but any container cap we add will show as a diff.

Options: (a) cap content at ~1200px centred, gutter grows — my recommendation; (b) cap at 1440px; (c) no cap, stretch forever as drawn. Needs your call before `theme.json` layout is set.

### 8.3 Navigation breakpoint and item count

8 top-level items + wordmark + CTA stop fitting around ~1150px. Do we (a) switch to the overlay early (~1100px), (b) cut top-level items to 5 with the rest in the overlay only, or (c) allow a smaller nav font between 900–1150px? Also: what does the overlay panel look like — full-screen cream, or a side drawer? No prototype exists for it.

### 8.4 Dynamic data — how live is the homepage?

- **"Next meeting: Tonight 7:30 PM — Never Alone · CAMH, 100 Stokes St"** — is this pulled from real meeting data? If so, where does that data live on the current site — a CPT, ACF fields, an external MA feed? This decides whether we need a second custom block.
- **`392+` meetings worldwide / `$366,218+` saved** — typed by an editor, or fetched from somewhere?
- Do meetings need structured data (schema.org `Event`) for search? Relevant since Yoast is installed.

### 8.5 Assets

- The hero image is a grey placeholder labelled "photo: Toronto skyline at dawn (soft, muted)". Is there a real photo?
- The logo is a CSS circle with the letters "MA". Is there a real logo file (SVG preferred), or do we build the mark in CSS as drawn?
- `design/uploads/` holds three inspiration PNGs and a PDF — reference only, or assets to use?

### 8.6 Colour contrast — §2.6

Do you want me to fix the accent/subtle colours to meet AA (small hue/lightness shifts to `#CE5F35` and `#7A8B7E`), or keep the prototype values exactly and accept the failures? Given the audience I'd push for the fix, but it's a visible design change and it's your call.

### 8.7 Token normalisation sign-off — §2

Specifically: collapse 22 font sizes to ~11? Collapse the six near-duplicate creams to three? One body-text green instead of two? Three radii instead of six? One letter-spacing token instead of five?

### 8.8 The quote slider

Keep 6-second autoplay (with a pause control, per WCAG), or drop autoplay and leave it manual? And should the quote list be editor-managed?

### 8.9 Front page wiring

Using `templates/front-page.html` means it applies automatically when a static front page is set. **Setting the front page is a `wp_options` write**, which your no-database-writes constraint excludes from this pass. Confirm that's handled in the separate content migration, or tell me you want `front-page.html` to work off the blog index instead.

### 8.10 `design/` location — §7

Move `design/` inside the theme so `.distignore` genuinely covers it, or leave it at the repo root and handle exclusion in the deploy path?

### 8.11 Missing pages

Nav links "Our Stories" and "7th Tradition" point to `#` — no prototype exists. Placeholder menu items for now, or omit until those pages are designed?

---

## 9. What happens after you approve

Per your instruction, before any theme code:

- **A.** Write `wp-content/themes/ma-toronto/CLAUDE.md` — tokens from §2 (as normalised by your answers to §8.7), file structure and naming from §6, the no-database-writes rule, read-prototypes-from-disk-in-sections, and Twenty Twenty-Five as reference-only. Kept short since it loads every session.
- **B.** Set up Playwright: screenshot the rendered homepage at 1440px and 390px, screenshot `design/Home.dc.html` at the same widths, diff them. Script it, confirm it runs against `http://localhost:8888/matoronto`, and gitignore `node_modules/` + the browser install.

Two notes on B, so it isn't a surprise later:
- The **390px comparison is not a fidelity check** (§1) — the prototype has no mobile layout. I'll wire it up as a self-regression baseline and label it as such.
- The prototypes render through `support.js` (a client-side runtime) and load fonts from Google. I'll add a settle/wait step and, once fonts are self-hosted, screenshot against local fonts so the diff isn't network-dependent. `design/Home-standalone.html` (3MB, fully bundled) is the more reliable capture target than `Home.dc.html`, and I'll verify which renders identically.

---

**No files have been created or modified other than this plan.** Nothing has been written to the database. Ready for your review.
