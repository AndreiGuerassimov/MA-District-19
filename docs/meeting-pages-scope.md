# Individual Meeting Pages + Real Meeting Data — Scope

**Status:** built 13 Sep 2026. 10 real meetings imported; `single-meetings.php` live; list cards link to meeting pages. Decisions below were confirmed as follows:

- Conflict 1 → **the detailed listing wins**: Joint Recovery at Royal York Road United Church, 7:30–8:45 PM (temporary to end of September 2026 — see `docs/TODO.md`).
- Conflict 6 → One Day at a Time **left out for now** (`docs/TODO.md`).
- B → online cards keep a **Join on Zoom** button with the Zoom wordmark (Simple Icons, CC0), plus "Meeting details".
- C → OpenStreetMap embed and directions; no API key.
- D → "Typical size" dropped; the meeting's **length** fills that tile.
- E → **reversed**: group phone and email are **public**, as on the old site (`tsml_contact_display = public`). This is a reskin. Shown as a "Contact" row in Meeting information, with the group note (e.g. Never Alone's "Call if there are any issues with access").
- F → the 7 TEST meetings were deleted.
- G → homepage "A Solution" → "How It Works" card — done 13 Sep 2026.

Also corrected from the live site during import: Women, Non-Binary, and Beyond notes gained "a closed meeting; speaker meeting on the fourth Thursday".
**Design:** `design/MeetingDetail.dc.html` (160 lines). Updated `design/Meetings.dc.html` now links each card to it.
**Data source:** the live site, https://www.matoronto.org/meetings-html/ (WordPress + Elementor + Spiffy Calendar), read as raw HTML — not via a summariser, so no times or addresses are paraphrased or invented.

---

## 1. Real meetings found

The live page carries the schedule **three times** — a calendar grid, a "detailed meeting information" section, and an upcoming-events list — and they do not fully agree. A draft import CSV in the plugin's format is prepared (kept out of the repository: it contains member contact numbers and Zoom passcodes, and the repository is public).

| Day | Time | Meeting | Where |
|---|---|---|---|
| Mon | 7:30–8:30 PM | Never Alone | CAMH Bell Gateway Building, 100 Stokes St |
| Tue | 7:00–8:00 PM | Life With Hope | Jackman Room, 310 Danforth Ave |
| Tue | 7:00–8:15 PM | A New Hope | Zoom |
| Wed | 7:30–8:30 PM | Joint Recovery | *see conflict 1* |
| Thu | 7:00–8:00 PM | Hope in Brampton | Emmanuel United Church, 420 Balmoral Dr, Brampton |
| Thu | 7:00–8:00 PM | MA Simcoe Group | Zoom |
| Thu | 7:00–8:00 PM | Women, Non-Binary, and Beyond | Zoom |
| Sat | 12:00–1:00 PM | Rainbow Recovery | Salvation Army Grace Health Centre, 650 Church St |
| Sun | 10:00–11:30 AM | MA Simcoe Step Study | Zoom |
| Sun | 5:00–6:00 PM | Never Alone | CAMH Bell Gateway Building, 100 Stokes St |

Also carried over: rooms and floors, entrances, TTC directions, open/closed status including "open on the third meeting of the month", rotating formats, Zoom meeting IDs and passcodes, dial-in numbers, and group contact phone/email (stored, private by default).

## 2. Conflicts on the live site — need a human answer

| # | Conflict | Proposed default |
|---|---|---|
| 1 | **Joint Recovery location.** Detail section: *"TEMPORARY MEETING LOCATION UNTIL END OF SEPTEMBER 2026"* — Royal York Road United Church, 851 Royal York Rd, Etobicoke. Events list and calendar: Our Lady of Sorrows Church, 3055 Bloor St W | Our Lady of Sorrows (the permanent venue); today is 13 September and the new site launches later. **Please confirm** |
| 2 | **Joint Recovery end time:** 8:30 PM (calendar, events) vs 8:45 PM (detail section) | 8:30 PM. Please confirm |
| 3 | **Life With Hope venue name:** "East End United" vs "Eastminster United Church" (same address) | East End United |
| 4 | **Never Alone room:** "Sacred Space" vs "Sacred Room" | Sacred Space |
| 5 | **Mask requirements** on Monday Never Alone and Rainbow Recovery, absent from the detail section | Omitted as outdated. Please confirm |
| 6 | **Thursday — One Day at a Time**, 6:30–7:30 PM, "a closed meeting in a Rehab Facility". **No address**, not in the calendar or events list | **Excluded.** It looks like a facility meeting not open to the public. Please confirm |

Light edits made to the copy: obvious typos ("expect" → "except", "focussed"), "stop smoking marijuana" → "stop using marijuana" to match MA's Third Tradition wording used elsewhere on the site.

---

## 3. Meeting page: design → data

The prototype is drawn for one in-person meeting. What each part is built from:

| Design element | Source | Notes |
|---|---|---|
| Back link, name, schedule pill | plugin: name, day, time, end time | "Mondays · 7:30 – 8:30 PM ET" |
| Badges (In person, Open meeting, Wheelchair accessible…) | plugin: attendance option + meeting types | |
| Get directions (button) | latitude/longitude | Design uses **OpenStreetMap**, not Google |
| Meeting information: Day & time, Type | plugin | |
| — Format | meeting types (Discussion, Speaker, Book Study…) | Row omitted when none set |
| — Access | Wheelchair Accessible type | Row omitted when not set — the live site has no access info |
| — Who can come | derived: open vs closed | Standard MA wording |
| — Cost | fixed text | Free; 7th Tradition basket optional |
| Add to calendar | generated `.ics`, weekly recurring | Small theme endpoint |
| Share | Web Share API, copy-link fallback | Progressive enhancement |
| Location card: name, address, room, bullets | location, address, **location notes** (one bullet per line) | Live data maps well: room, entrance, TTC, notices |
| First time here? card | fixed text + Contact link | Same component as the list page's help card |
| Map | **OpenStreetMap embed** (iframe) from lat/long | Third-party request; see decision C |
| About this meeting | meeting notes | |
| Tiles: Format / Typical size / Language | types; *no data*; language types | **"Typical size" has no source** — see decision D |
| Other meetings at this location | plugin: same location | Never Alone Sun + Mon share CAMH |
| "Meeting details change" + last updated | meeting's last-modified date | |

### Online meetings — not drawn

| Replace | With |
|---|---|
| Get directions button | **Join on Zoom** button |
| Location card | **How to join** card: meeting ID, passcode, dial-in numbers |
| Map | nothing |
| Other meetings at this location | omitted |

---

## 4. Decisions

| # | Decision | Proposed default |
|---|---|---|
| A | Conflicts 1–6 above | As proposed; **1 and 6 need your confirmation** |
| B | List page: the updated design swaps "Join on Zoom" / "Get directions" for **"Meeting details"** on every card. You wanted low friction for online meetings | Follow the design (cards → detail page), but **keep "Join on Zoom" on online cards** as a second action |
| C | Map: OpenStreetMap iframe (as designed) loads openstreetmap.org on page view | Use it, lazy-loaded. No tracking cookies, far lighter than Google Maps |
| D | "Typical size" tile — no data anywhere, and it would go stale | **Drop it**; keep Format and Language |
| E | Group contact phone/email | Import but **keep private** (plugin default); the design shows no contact on the page |
| F | Delete the 7 TEST meetings before importing real ones | Yes |
| G | Homepage "A Solution" card → "How It Works", per the updated prototype | Yes, small content edit |
