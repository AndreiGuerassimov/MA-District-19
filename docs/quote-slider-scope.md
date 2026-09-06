# Quote Slider — Scope

**Status:** scoping only, no code written.
**Replaces:** `patterns/section-quote.php`, the static placeholder built in chunk 9.
**Source of truth:** `design/Home.dc.html`, the section commented `<!-- quotes slider -->`, plus the `DCLogic` block at the foot of that file.

---

## 1. What the prototype actually does

| Aspect | Prototype behaviour |
|---|---|
| Slides | 5 fellowship slogans, plain text, no attribution |
| Controls | Previous / next, 44px circular buttons flanking the quote, `aria-label` "Previous quote" / "Next quote" |
| Indicators | 5 dots, 8px, `aria-label` "Go to quote"; active is `#2F5D47`, inactive `rgba(47,93,71,.25)` |
| Autoplay | `setInterval`, **6 seconds**, starts on mount |
| On interaction | `clearInterval` — any click stops autoplay permanently, with no way to restart |
| Transition | None. The text is swapped instantly |
| Layout | Quote centred, `max-width: 900px`, `min-height: 2.8em` to stop the row jumping between short and long slogans |
| Type | Lora italic 500, 34px/1.4, ink |

The five slogans are in `design/Home.dc.html`. Nothing else on the page depends on this component.

---

## 2. Library or not

The brief suggested Splide. I measured the alternative before recommending, because the deciding number is unusual here.

**The WordPress Interactivity API runtime is already downloaded on every page of this site.** The Navigation block's overlay uses it, so it is `modulepreload`ed on every request:

| File | Raw | Gzipped |
|---|---|---|
| `@wordpress/interactivity` runtime | 40,106 B | **15,218 B — already loaded** |
| `core/navigation` view module | 3,033 B | 1,134 B |

So the marginal cost of building on the Interactivity API is only this component's own module — realistically **1–2 KB gzipped**. Splide would be **~12 KB gzipped of JS plus its stylesheet**, entirely additive, to animate five short text strings.

| | Interactivity API | Splide | Embla |
|---|---|---|---|
| Marginal weight | ~1–2 KB | ~12 KB + CSS | ~7 KB + you build the UI |
| Already on the page | **yes** | no | no |
| Build step required | no | no (if vendored) | no |
| Accessibility | ours to write, ours to control | good, built in | none — you write it |
| Third-party dependency | none | one | one |
| Maintenance | tracks WP core | see below | active |

**On Splide specifically:** it is a good library and accessibility was a founding goal, which is why it beats Swiper here. But reporting in 2026 notes its development activity has slowed markedly since mid-2023, with issues and pull requests accumulating. For a volunteer-run fellowship site that may go long stretches without a developer, taking on a dependency that is drifting is a real cost, and it buys features this component does not use — image handling, lazy loading, virtual slides, effects, breakpoint configs.

**Recommendation: no library.** Not on principle, and not to save 12 KB — because the runtime is already paid for, the feature set of a carousel library is unused here, and hand-writing the ARIA means it is exactly right rather than approximately right.

If this component later grows into an image gallery with touch physics and many slides, revisit and take Splide.

---

## 3. Proposed mechanism: CSS scroll-snap track, JS as enhancement

Rather than a JS-driven show/hide, the slides sit in a horizontally scrolling flex track with CSS scroll-snap.

```
track:  display: flex; overflow-x: auto; scroll-snap-type: x mandatory;
slide:  flex: 0 0 100%; scroll-snap-align: center;
```

What that buys before a single line of JavaScript runs:

- **Swipe works on touch devices** natively, with correct momentum and rubber-banding
- **Keyboard scrolling works** natively
- **With JavaScript disabled or broken, the component still works** — it degrades to a swipeable, readable row of quotes rather than a dead widget showing one slide

JavaScript then adds only what CSS cannot: previous/next buttons, dot indicators and their active state (via `IntersectionObserver`), and autoplay. That is a genuinely small module.

This is the main reason I prefer it to a library: a library replaces native scrolling with synthetic transforms, so the no-JS state is usually a broken one.

---

## 4. Autoplay — needs a decision

**The prototype's autoplay fails WCAG 2.2.2 (Pause, Stop, Hide).** Content that moves or auto-updates for more than five seconds must offer a way to pause it. The prototype's only "pause" is clicking a control, which stops it permanently and is not discoverable.

Three options:

| | Behaviour | Assessment |
|---|---|---|
| **A** | **No autoplay.** Buttons, dots and swipe only | Simplest, fully conformant, and quotes stay readable at the reader's pace. **Recommended.** |
| **B** | Autoplay with a visible pause/play control | Conformant, matches the prototype's intent, adds a control the design does not have a place for |
| **C** | Autoplay as drawn | Not conformant. Would be the only knowing accessibility failure in the build |

If B, then autoplay must additionally: not start when `prefers-reduced-motion: reduce`, pause on hover, pause on focus within the component, and stop permanently once a user operates any control.

I recommend **A**. This is a recovery site — a slogan someone is reading should not slide away from them. C is the one option I would push back on.

---

## 5. Where the quotes live — needs a decision

| | Approach | Editors can change quotes? | Cost |
|---|---|---|---|
| **A** | **Core `core/quote` blocks inside the pattern.** The slider is a pattern plus Interactivity directives — no custom block | **Yes**, in the normal editor | None beyond the pattern |
| **B** | Custom block with InnerBlocks | Yes | Needs an editor-side JS bundle, so a **build step** — the theme currently has none |
| **C** | Quotes hardcoded in the pattern PHP | No, developer only | None |

**Recommendation: A.** It keeps the theme free of a build step and `node_modules`, and the quotes stay ordinary blocks an editor can retype. Slide indices are assigned by JavaScript at runtime rather than baked into markup, so adding or removing a quote in the editor Just Works.

This also means **no custom block is added to Phase 1** — the count stays at zero.

---

## 6. Accessibility specification

Written to the ARIA Authoring Practices carousel pattern, tabs-less variant.

- Region wrapper: `role="group"`, `aria-roledescription="carousel"`, `aria-label="Quotes and slogans"`
- Slide track: `aria-live="polite"` when autoplay is **off**; `aria-live="off"` while autoplaying, so a screen reader is not interrupted every six seconds
- Each slide: `role="group"`, `aria-roledescription="slide"`, `aria-label="3 of 5"`
- Previous / next: real `<button>`s, `aria-label` "Previous quote" / "Next quote", never disabled at the ends — they wrap
- Dots: real `<button>`s, `aria-label="Go to quote 3"`, active carries `aria-current="true"`
- All controls meet the 44px target size and carry the theme's `:focus-visible` outline
- Scroll behaviour is `smooth` only when `prefers-reduced-motion` does not request reduction
- The component is fully operable by keyboard, and focus never becomes trapped

Verified the same way as the navigation: a script under `tools/visual/` asserting each of the above against the rendered page, added to `npm run a11y:nav`'s sibling.

---

## 7. Files this would touch

```
patterns/section-quote.php              rewritten — track, slides, controls, directives
assets/js/quote-slider.js               new — Interactivity API view module (~80 lines)
assets/css/blocks/core-quote.css        extended — track, slides, controls, dots
functions.php                           + wp_register_script_module / enqueue
tools/visual/quote-a11y.mjs             new — the checks in section 6
```

No new dependency, no build step, no `node_modules` in the theme.

---

## 8. Open questions

1. **Autoplay: A, B or C?** (section 4) — I recommend A.
2. **Quote source: A, B or C?** (section 5) — I recommend A.
3. **Keep all five slogans**, or a different set? They are in `design/Home.dc.html`.
4. **Transition style.** The prototype swaps instantly. Scroll-snap gives a horizontal slide. A cross-fade is also possible. Slide is the natural fit for the mechanism.
5. **Does the section keep its `Quotes & slogans` eyebrow?** Currently an `<h2>`, which is doing useful work in the document outline.

---

## 9. Estimate

One chunk, comparable to the navigation overlay: pattern rewrite, ~80 lines of JavaScript, CSS for the track and controls, and an accessibility test script. The riskiest part is the `IntersectionObserver` dot syncing, which is why the no-JS fallback matters — if it misbehaves, the component is still usable.
