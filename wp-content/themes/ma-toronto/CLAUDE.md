# MA Toronto — block theme

Custom block theme rebuilding matoronto.org from static prototypes.
Full plan: `docs/build-plan.md` at the repo root. Read it before non-trivial work.

**Stack:** WordPress 7.1 · PHP 8.3 (server; CLI is 8.5) · http://localhost:8888/matoronto
**Phase 1 (homepage) is complete.** Other pages in `design/` are the next phase.

## Hard rules

1. **Homepage content lives in the database, not the theme.** The Home page (ID 49)
   holds the assembled sections; `templates/front-page.html` just renders
   `post-content`. `patterns/section-*.php` are the source for *inserting* a
   section, not the live copy — changing a section's structure means updating
   page 49 as well as the pattern. Everything else still avoids DB writes:
   no `wp option update`, no post/term creation. Content migration for the
   remaining pages happens separately via WP-CLI/WXR.
2. **Read prototypes from disk, a section at a time** — `sed -n`, `grep`, or `Read` with
   offset/limit. Never load a whole prototype into context. `design/Home-standalone.html`
   is 3MB. Even the small `.dc.html` files have very long lines.
3. **theme.json over CSS.** If theme.json can express it, it goes there so it stays
   editable in the Site Editor. Hand-written CSS only where theme.json genuinely can't.
4. **No page builders, no CSS frameworks, no slider libraries.** Core blocks + theme.json
   + hand-written CSS. Flag any third-party dependency before adding it.
5. **Twenty Twenty-Five is reference-only.** Read it to see how a well-built block theme
   solves something. Never copy its design.
6. **`design/` must never ship.** It lives at the WP root, outside this theme, so it can't
   ship with the theme — but keep it in `.distignore` and the deploy excludes anyway.
   Same for `database/*.sql`.

## Design tokens

Derived from `design/*.dc.html`. Values below are **final** — normalised and
contrast-corrected. Don't reintroduce raw prototype values.

### Colour

| slug | hex | use |
|---|---|---|
| `base` | `#F8F5EC` | page canvas |
| `base-alt` | `#F3EEDF` | hero gradient end stop |
| `surface` | `#FFFDF6` | raised surface / card bands |
| `ink` | `#21372C` | darkest text |
| `primary` | `#2F5D47` | brand green, links, buttons |
| `primary-dark` | `#1F3E31` | footer band |
| `accent` | `#B0521F` | **all accent TEXT + button backgrounds** |
| `accent-bright` | `#CE5F35` | **non-text only** — rules, underlines, dots |
| `body` | `#44584C` | body copy on light |
| `muted` | `#5B6E61` | secondary copy on light |
| `on-dark` | `#C8D4C4` | copy on green |
| `on-dark-muted` | `#8FA98D` | micro-labels on green |
| `sand` | `#F0D9A8` | highlight copy on green |
| `success` | `#3F8A5F` | status dot |
| `on-accent` | `#FFF7EE` | label on accent |

Reserved for later pages: `amber #E9A13B`, `primary-tint #E1EEDF`, `accent-tint #F7E4D6`.

**Why `accent` is not `#CE5F35`:** measured 3.63:1 on `base` — fails AA for the 12–13px
text it's used on, and 3.73:1 under the header CTA's white label. `#B0521F` is the
designer's own darker terracotta (already used on Contact/Literature/Meetings) and
measures 4.73:1 on base, 5.07:1 on surface, 4.86:1 behind `on-accent`. `#CE5F35` is
retained as `accent-bright` for non-text use, where it passes the 3:1 bar.
(JSON has no comments, so this file and `docs/build-plan.md` are where the original
prototype values and the measurements are recorded.)

**Collapsed near-duplicates** — do not resurrect: `#EFEBDD`→`base`, `#F0EAD9`→`surface`,
`#24463A`→`primary-dark`, `#7A8B7E`→`muted` (also a contrast fix), `#A9BCA6`→`on-dark`
(also a contrast fix). `#E4DDC9`/`#EBE5D4`/`#7A7360` were image-placeholder scaffolding
in the prototypes, never tokens.

**Every text/background pair in the table above is measured ≥4.5:1.** Re-measure before
introducing any new pair. `/private/tmp/.../scratchpad/contrast.py` has the helper.

### Typography

Lora (display/headings, weights 400/500/600) · Karla (UI/body, weights 400/600/700).
Self-hosted in `assets/fonts/`, declared via theme.json `fontFace`. Do not load from
Google Fonts. Weights outside those lists are not bundled.

| slug | px | slug | px |
|---|---|---|---|
| `tiny` | 11 | `x-large` | 22 |
| `xx-small` | 12 | `xx-large` | 30 |
| `x-small` | 13 | `quote` | 34 |
| `small` | 14 | `display` | 46 |
| `medium` | 16 | `display-lg` | 52 |
| `large` | 18 | | |

Collapsed from 22 prototype sizes; the fractional halves (12.5/13.5/14.5/15.5/16.5/17.5)
were tool drift. Max shift 1px.

Line heights: `tight 1.15` (display) · `heading 1.35` · `body 1.65` · `list 2.1` (footer).
Letter-spacing: one token, `caps 0.25em`, replacing five px values. Uppercase
micro-labels only.

### Spacing, radii, shadows, layout

Spacing scale: `10:8px  20:12px  30:16px  40:20px  50:24px  60:32px  70:40px  80:48px
90:56px  100:64px`. `80` is the page gutter; `90`/`100` are section padding.

Radii: `sm 9px` (inputs) · `md 12px` (cards) · `lg 16px` · `pill 999px` · `round 50%`.

Shadows — all ink-based, no pure black:
`sm 0 1px 2px rgba(33,55,44,.15)` · `md 0 8px 24px rgba(33,55,44,.14)` ·
`lg 0 12px 32px rgba(33,55,44,.12)`. Hairline border: `rgba(33,55,44,.1)`.

Layout: `contentSize 820px` · `wideSize 1184px` · root padding `48px`.

**Sections stretch to the window; they are not capped.** By decision, full-width
sections use flow layout with explicit 48px side padding — matching the prototype,
which has no max-width either. `wideSize` therefore applies only inside
*constrained* layouts (the about band's 820px copy column, the quote slider's
900px); it does not cap the page. Do not "fix" this by switching sections to
constrained layout.

Breakpoints via theme.json `settings.viewport` (WP 7.1): `mobile 600px`, `tablet 1100px`.
1100 because 8 nav items + wordmark + CTA stop fitting around 1150px.

## Structure and naming

```
theme.json          tokens (the single source of truth)
styles/blocks/      named block styles     01-eyebrow.json
styles/sections/    named section styles   01-green-band.json
patterns/           one file per section   section-hero.php
parts/              header, footer, navigation-overlay
templates/          front-page.html (thin assembly, ~10 lines)
assets/css/         per-block CSS, enqueued via wp_enqueue_block_style()
assets/fonts/       self-hosted Lora + Karla woff2
functions.php       registrations only — no markup, no styling
```

**Layering rule: a layer may only reference the layer above it.** No pattern hardcodes a
hex value; no template contains layout logic. Violations are bugs.

| thing | convention |
|---|---|
| pattern slug / file | `ma-toronto/section-*` / `section-hero.php` |
| pattern category | `ma-toronto` |
| block style | `is-style-*` |
| CSS class | `ma-` prefix, BEM-ish — `.ma-card__cta` |
| theme.json slug | semantic, never literal — `accent`, not `terracotta` |

## Editor safety

Every section's outer group carries **`templateLock: "contentOnly"`**. Editors retype text
and swap images; they cannot move, delete, insert, or restyle. Preserve this on every new
section — it's the primary guard against accidental layout breakage.

It's a UI-level guard, not a capability check: an admin in the Code Editor can still edit
markup. Recovery path is Site Editor → "Clear customizations", which resets to the
git-tracked theme file.

## Accessibility (non-negotiable, overrides prototype fidelity)

The prototypes have **no focus styles, no responsive rules, and several AA contrast
failures**. Fidelity to them stops where correctness starts.

- One `<h1>` per page. Card titles are `<h3>` under a real (or visually-hidden) `<h2>`.
- Real landmarks via `tagName`. Core's block-theme skip link stays enabled.
- Visible `:focus-visible` on every interactive element — 2px offset outline, never
  colour-only.
- Decorative `→` arrows and status dots get `aria-hidden="true"`.
- Whole-card links use a stretched-link pseudo-element so the accessible name comes from
  the heading, not a wrapper anchor.
- Honour `prefers-reduced-motion`.
- Mobile nav: use core Navigation's overlay. Core already ships Escape-to-close,
  Tab/Shift-Tab focus cycling, and `aria-modal`. **Do not hand-roll a focus trap.**

## Navigation overlay — two things core forces

**A custom overlay part is all-or-nothing.** When `core/navigation` names an
overlay template part, core hides everything in the panel except that part's
blocks:

    .disable-default-overlay.is-menu-open .…-container-content
      > :not(.wp-block-navigation__overlay-container) { display: none }

So the parent nav's items are rendered but hidden, and the overlay must carry
its **own** navigation block. That is why `parts/navigation-overlay.html`
duplicates the six links from `parts/header.html`.

**This duplication is temporary and must be kept in step.** At content
migration, both navigation blocks switch to the same menu via
`{"ref":<wp_navigation ID>}`, which collapses them to one source of truth.
Until then, editing the menu means editing both files.

**Core also owns the overlay's behaviour** — focus trap, Escape, `aria-modal`,
`role="dialog"`, focus restore. Verified by `npm run a11y:nav` (14 checks).
Never hand-roll any of it.

## Deferred — do not build without scoping first

- **Quotes slider** (`design/Home.dc.html` §5) — static pull-quote placeholder for now.
- **Hero "Next meeting" card** — needs the Meetings data model; belongs to that phase.

## Block markup gotchas (both cost real time — do not relearn)

**Never hand-write render-time attributes into pattern markup.** Block validation
compares saved HTML against what the block's `save()` would regenerate, so any
extra attribute makes the editor show *"Block contains unexpected or invalid
content"*. `fetchpriority`, `decoding`, `loading` and `aria-*` on a block's
wrapper element all fall into this trap — `core/image`'s `save()` never emits
`fetchpriority` at all. Add them at render with a `render_block` filter and
`WP_HTML_Tag_Processor` instead; `functions.php` has three examples.

Attributes *inside* rich text (a `<span aria-hidden>` within a paragraph) are
content and round-trip fine. It is only the wrapper element that must match.

When a block does need matching classes, read the real `save()` rather than
guessing — it is in `wp-includes/js/dist/block-library.min.js`. That is how the
hero image's missing `has-custom-border` class was found: `core/image` adds it
whenever a border style exists.

**Full-width sections need `blockGap: 0` on their container.** Flow layout
inserts a 24px gap between children, which shows as a stripe of page background
between bands. `templates/front-page.html` sets it on both the `main` group and
`post-content`; `footer.css` zeroes the margin above the footer template part.

## Verification

`npm run visual` (from the repo root) screenshots the rendered homepage and the prototype
at three widths and diffs them.

| width | meaning |
|---|---|
| **1280** | **The fidelity check.** The prototype's native design width, where our 1184px content box matches its content box exactly. Drive this number down. |
| 1440 | Expected to differ — the prototype has no max-width and keeps stretching; we cap at 1184px. |
| 390 | Self-regression only — the prototypes have no responsive CSS, so there is nothing faithful to compare against. |

Baseline on the empty theme was 44.7% at 1280px; the built homepage is 17.3%.
Output in `tools/visual/output/`. Browser lives in `.playwright/`;
`npm run visual:install` fetches it. Both gitignored.

**A single whole-page number is misleading** — it conflates "does this section
look right" with "is it at the same vertical position". Use `npm run
visual:sections`, which crops each band from both pages and compares them
independently. Every section currently sits within 1-8%.

| command | what it checks |
|---|---|
| `npm run visual` | whole-page diff at 1280 / 1440 / 390 |
| `npm run visual:sections` | per-section fidelity, drift removed |
| `npm run breakpoints` | where the nav overlay takes over (expect 1100px) |
| `npm run a11y:nav` | overlay focus trap, Escape, ARIA (14 checks) |
| `npm run a11y:quote` | slider semantics, keyboard, no-JS fallback (18 checks) |
| `npm run audit:responsive` | overflow, clipping, target sizes at 11 widths |

Run all six before calling anything done.
