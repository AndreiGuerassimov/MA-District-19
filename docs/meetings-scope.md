# Meetings — Scope (v2)

**Status:** built (round 1) — list page live with test data. Meeting pages and the homepage "Next meeting" card still to come.
**Source of truth:** `design/Meetings.dc.html`.
**Unblocks:** the site's only remaining 404 (`/meetings/`), and later the homepage "Next meeting" card.

## Decisions already made

| # | Decision |
|---|---|
| — | **No ACF.** ACF will be removed later; everything should be blocks |
| — | **Weekly meetings only.** No monthly calendar, no special events |
| — | **Filters as URL parameters** (`/meetings/?type=online`) |
| — | **Meeting cards do not link anywhere this round.** Individual meeting pages get their own design later |
| — | Remove the abandoned ACF `meeting` post type, taxonomy and two empty posts |

## Decided (round 2)

| # | Decision |
|---|---|
| 1 | **Use the 12 Step Meeting List plugin.** Blocks are for editorial pages whose layout changes; meetings are structured records with fixed layouts, so they don't need to be blocks. Losing the editor preview is accepted in exchange for a trusted, maintained tool |
| 2 | **WordPress is the source of truth.** A spreadsheet is used for the initial import only; after that, meetings are edited in the plugin's admin form |
| 3 | **Online meeting links are published publicly**, as AA Toronto does, to reduce friction. Disruptions are handled by hosts |

### How this resolves the template concern in §3

The template takeover only conflicts with *block* templates. The plugin's default
meeting finder is `legacy_ui`, and in that mode it explicitly defers to theme
files named `archive-meetings.php` and `single-meetings.php`. So the theme
supplies those two PHP templates — the plugin's supported extension point, with
no workaround filter.

They still render the theme's header and footer template parts and use the same
theme.json tokens, so the pages look identical to the rest of the site.

Side benefit, verified in the source: the plugin's front-end assets load only
from its own templates (`tsml_assets()`). With our templates in place, none of
them load — no jQuery, and no Leaflet map library from the third-party CDN
`unpkg.com`.

---

## 1. What the prototype does

Hero (same as other interior pages) → controls bar with All / In person / Online chips and a count → meetings grouped by day, Monday to Sunday, empty days omitted → each row: time, name, place, type badge, action → help card "New and not sure where to start?". The monthly calendar card is dropped (weekly only).

The seven meetings in the prototype are **invented design data** with fabricated Zoom IDs and will not be published.

---

## 2. What exists today

An abandoned start, registered through ACF's admin UI: a `meeting` post type, a `meeting-type` taxonomy (In-person, Online), a field group with **zero fields**, and two posts with titles only — the day is embedded in the title. No time, address or link data anywhere. This will be removed.

---

## 3. Where meeting data lives

### Option A — 12 Step Meeting List (TSML)

[code4recovery/12-step-meeting-list](https://github.com/code4recovery/12-step-meeting-list), distributed on WordPress.org. Version 3.19.18, updated 27 August 2026, 900+ installs. I read its source rather than rely on the listing.

**Genuinely good fit:**

- **Marijuana Anonymous is a built-in program**, with MA's own meeting types: Book Study, Beginners, Chip, Closed Captions, Discussion, Meditation, Men, Women, Non-Binary, Transgender, Persons of Color, Young People, Outdoor, Speaker, Wheelchair Accessible, Open to Non-Addicts
- **No ACF.** Zero references in its code
- Purpose-built data: day, start/end time, timezone, location with address, region/district, types, notes, group, and online meetings as first-class fields (conference URL, dial-in, conference notes)
- `tsml_get_meetings()` returns clean arrays we can render in our own blocks
- `[tsml_next_meetings]` handles the "next meeting" logic
- A standard JSON feed (Meeting Guide format) for interoperability
- Actively maintained by the recovery community

**How it handles data entry natively** (this answers "how does it do spreadsheets"):

| Method | How it works |
|---|---|
| Admin form | One screen per meeting: Meeting Information, location, contact info |
| CSV import | Upload a spreadsheet. The column format is `template.csv` in the plugin: Time, End Time, Day, Name, Location, Address, Region, Types, Notes, Timezone, Group, Conference URL, Conference Phone, and more |
| **Google Sheet as a live source** | Paste a Google Sheets URL as a "data source"; the plugin re-imports it on a schedule and reports what changed. Volunteers edit the sheet, the site follows |
| JSON feed | Import another TSML site's feed, same auto-refresh |

**Where it works against this project** — verified in the source:

| Issue | Detail |
|---|---|
| **Meetings are not edited in the block editor** | `tsml_meeting` supports only `title` and `author`, has no `show_in_rest`, and uses classic meta boxes. Directly at odds with "everything in blocks" |
| **It takes over meeting page templates** | Its `single_template` and `archive_template` filters return the plugin's own PHP files. It only defers to *classic* theme files (`single-meetings.php`). WordPress's block-template lookup then ignores a theme's `single-tsml_meeting.html` — because the incoming template is a plugin path, the lookup narrows to the single most specific candidate. **Your upcoming block-designed meeting pages would need a workaround filter** |
| It claims `/meetings/` | Its default slug is `meetings`. Configurable — and setting it empty makes meeting pages non-public, which suits "no links this round" |
| Not yet declared for WP 7.1 | Tested up to 7.0.4. Would need a local check |
| Hosted services | Address geocoding goes through `geo.code4recovery.org`; Google Sheets imports go through `sheets.code4recovery.org`. Meeting addresses and sheet contents pass through Code for Recovery's servers. Meeting addresses are public anyway, but worth knowing |

### Option B — Our own meeting data model, no plugin, no ACF

A small **site plugin** (`ma-meetings`), not the theme — meeting data must survive a theme change.

| Piece | Approach |
|---|---|
| Post type | `ma_meeting`, block-editor enabled, `show_in_rest` |
| Fields | Registered post meta (`register_post_meta`): day, start time, end time, format (in person / online / hybrid), location name, address, conference URL, conference notes, types |
| Editing structured fields | A PHP panel in the block editor's meta box area with a **day dropdown, time inputs and format radios**. No JavaScript build step. The meeting's description stays in blocks |
| Why not block bindings for fields | WordPress 7.1 can bind paragraphs to post meta, but that makes day and time **free text**. "Tues" or "7:30pm" breaks sorting and the next-meeting calculation. Structured schedule data needs structured inputs |
| Types | A `ma_meeting_type` taxonomy seeded with MA's official types (the list TSML ships) |
| Import | CSV upload screen, **using TSML's column names** so data stays portable in either direction |
| Next meeting | Weekly-only, `America/Toronto` — roughly 40 lines |
| Meeting pages (later) | Plain block template `single-ma_meeting.html`. No workaround needed |
| This round | Meeting pages kept non-public until their design lands |

| Gain | Cost |
|---|---|
| Native block editor, native block templates for your meeting page design | We own and maintain the code |
| No template workarounds, no third-party services | Build the import screen, validation and next-meeting logic |
| Exactly the fields the design needs | No map or geocoding (not in the design) |

### Assessment

TSML is a **good tool** and the right answer for many fellowship sites. For this project, two of its behaviours cut against decisions you've already made: meetings aren't edited in blocks, and it overrides block templates for the meeting pages you're designing.

**My lean is B**, because meeting pages designed in blocks are coming and the template conflict would be a permanent workaround. **Choose A** if you'd rather write less custom code and are happy managing meetings in its form or a Google Sheet. Either way the data format matches TSML's CSV, so changing course later isn't a rebuild.

---

## 4. Source of truth — a people question

Whichever option, pick **one** place meetings are edited. Two-way editing is where schedule sites go wrong.

| | Suits | Catch |
|---|---|---|
| **WordPress is the source** | People comfortable in wp-admin | A spreadsheet is used for the initial import, then retired |
| **A spreadsheet is the source**, synced on a schedule | Service volunteers who already keep a meeting list and don't use WordPress | Any edit made in WordPress is overwritten at the next sync. With A this is built in; with B we'd add a sync job |

Who maintains MA Toronto's meeting list today, and where?

---

## 5. Page build (either option)

- `templates/page.html` already renders the hero
- A server-rendered Meetings List section: day groups, time/name/place/badge, the chips as links, count reflecting the filter
- **Cards are not links this round.** Online meetings still need the join action to be useful — see §6
- Help card with the amber button (contrast to be measured first)

## 6. Accessibility and safety

- Day headings `<h2>`; each day's meetings a list; times in `<time datetime>`
- Chips are links with `aria-current` on the active filter
- Action links name the meeting ("Join Life With Hope on Zoom")
- **Online links:** publishing Zoom IDs and passcodes publicly invites disruption, and anonymity is MA's core principle. Options: publish the link; publish without the passcode (on request); or link on request only. **MA Toronto's call.**
- **Real data only.** Test meetings stay as unpublished drafts until MA Toronto supplies the list
