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

- [ ] **Confirm the old contact numbers are still current** before launch. Several
  listings on matoronto.org look years old (one still mentioned masks). Numbers
  are now public on each meeting page.

- [ ] **Location pages** (`/locations/…`) redirect to `/meetings/`. Design one only
  if needed — a meeting's page already shows its location and the other meetings
  held there.

## Content

- [ ] **Homepage "A Solution" card → "How It Works"**, per the updated
  `design/Home.dc.html`. Content edit on page 49.

- [ ] **How It Works page** — new prototype `design/HowItWorks.dc.html`. Page #14
  exists but is empty.

## Housekeeping

- [ ] Remove leftover ACF `meeting-type` taxonomy definition and draft page #37
  "Meetings" (empty `core/query` for the old post type).
- [ ] `tools/visual/sections.mjs` points at `design/Home-standalone.html`, which
  was removed from `design/`. Repoint it at `design/Home.dc.html`.
