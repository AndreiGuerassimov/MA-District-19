# To do

Items parked for later, with enough context to pick up cold.

## Meetings

- [ ] **"One Day at a Time" (Thursday, 6:30–7:30 PM)** — left out of the import.
  The live site describes it only as *"a closed meeting in a Rehab Facility"*,
  gives **no address**, and it is absent from the live calendar and events list.
  Its only detail is a service contact, +1 (905) 960-3796. Decide whether it
  belongs on the public list at all (it may be for the facility's residents
  only), and if so, what location to show.
  Source: https://www.matoronto.org/meetings-html/ — "Thursday - One Day at a Time".

- [ ] **Joint Recovery's temporary location expires end of September 2026.**
  Imported from the main listing: Royal York Road United Church, 851 Royal York
  Rd, Etobicoke, 7:30–8:45 PM, *"TEMPORARY MEETING LOCATION UNTIL END OF
  SEPTEMBER 2026"*. The live calendar lists the regular venue as Our Lady of
  Sorrows Church, 3055 Bloor St W, 7:30–8:30 PM. Confirm with the group where
  they meet from October and update the meeting in wp-admin → Meetings.

- [x] ~~Delete test meetings before launch~~ — done 13 Sep 2026.

- [ ] **List page title reads "Meetings Archive".** Yoast owns titles. Change it in
  Yoast SEO → Settings → Content types → Meetings → archive title (e.g. "Find a
  meeting %%sep%% %%sitename%%").

- [ ] **Show which meetings (and which weeks) are open vs closed.** Every meeting
  is closed by default — only for people with a desire to stop using marijuana.
  Some weeks are open to anyone: speaker meetings and medallion (chip)
  celebrations. Some meetings have a fixed open week, e.g. the third week of the
  month is a speaker meeting. Needs a way to record this per meeting (and per
  week of the month) and to show it clearly on the list and meeting pages.
  Today the rules only appear as prose in each meeting's description, and the
  plugin's MA meeting types have no "Closed" code — only "O" (open), with no
  notion of week-of-month. Needs scoping.

- [ ] **Confirm the old contact numbers are still current** before launch. Several
  listings on matoronto.org look years old (one still mentioned masks). Numbers
  are now public on each meeting page.

- [ ] **Location pages** (`/locations/…`) redirect to `/meetings/`. Design one only
  if needed — a meeting's page already shows its location and the other meetings
  held there.

## Content

- [x] ~~Homepage "A Solution" card → "How It Works"~~ — done 13 Sep 2026; links to `/how-it-works/`.

- [x] ~~Create the How It Works page~~ — built 13 Sep 2026 (page 14, "Full-width sections" template).

- [ ] **Redirect old URLs before launch.** The old site's How It Works section
  lived under `/a-solution/…` (e.g. `/a-solution/the-twelve-steps/`); the new
  pages are under `/how-it-works/…`. Add 301s in the Redirection plugin so
  links and search results keep working. `/our-stories/` and `/7th-tradition/`
  also exist on the old site with no new equivalent yet.

- [ ] **Add more slogans to the homepage slider.** It currently has three: "One
  day at a time.", "Keep it simple.", "Progress, not perfection." Add them in the
  editor on the homepage (page 49), in the quote section — duplicate a slide and
  retype it. The dots and previous/next buttons adjust automatically.

- [ ] **Upload the literature and connect it to the Literature page cards.** All
  six cards on `/literature/` (page 63) link to `#`: Life with Hope, A New Leaf,
  Detoxing from Marijuana, The Twelve Questions, For the Newcomer, Twelve Steps
  Workbook. Upload the files (or find the official links) and set each card's
  title link. Check what MA World Services allows to be hosted locally versus
  linked from marijuana-anonymous.org.

- [ ] **Replace the placeholder logo with the official MA Toronto logo.** The
  header and footer currently draw a green "MA" circle in CSS
  (`.ma-logo__mark` in `assets/css/header.css`, `.ma-footer__mark` in
  `footer.css`). A PNG of the real logo exists, but it needs a vector (SVG)
  version first — either sourced from MA Toronto / MAWS or redrawn. Once there
  is an SVG, swap the roundel for `core/site-logo` (or an inline SVG) in
  `parts/header.html` and `parts/footer.html`, and keep the wordmark as the
  link text so the accessible name stays "Marijuana Anonymous".

- [ ] **Lower the mobile menu breakpoint.** The header switches to the
  hamburger menu below 1100px (`settings.viewport.tablet` in `theme.json`),
  sized for the old 8-item menu. Since Home and Meetings were removed the menu
  has 4 top-level items and would fit on narrower screens. Find the new width
  where it stops fitting (`npm run breakpoints`) and lower the value.

## Housekeeping

- [ ] Remove leftover ACF `meeting-type` taxonomy definition and draft page #37
  "Meetings" (empty `core/query` for the old post type).
- [ ] `tools/visual/sections.mjs` points at `design/Home-standalone.html`, which
  was removed from `design/`. Repoint it at `design/Home.dc.html`.
