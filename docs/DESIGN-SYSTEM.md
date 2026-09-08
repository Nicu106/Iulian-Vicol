# The design system, as built

Everything standing on `v2design.ivmotorclass.com` as of 2026-09-08, written so a new
session — any model — can add a page that looks like it belongs without re-deriving any
of it.

`CLAUDE.md` is the operating manual: what to do and in what order.
`docs/DESIGN-GUIDE.md` is the reasoning and the research behind each decision.
**This file is the inventory**: what exists, what it is called, and when to use it.

---

## 0 · The order of operations

1. **Read the brief for the one hard constraint** and put it first on the page. On
   `/vende` that is the five marques; a Renault owner should learn it in the first
   second, not after seventeen fields.
2. **Measure the slot before you style it.** Rendered widths at 320/390/768/1000/1440/1800.
   Every `sizes`, every breakpoint and every clamp ceiling in this system came from a
   measurement, not from a round number.
3. **Build with the tokens.** If a value has no token and needs one, add the token with a
   comment saying why. `public/css/mc-tokens.css` is the only place they live.
4. **Look at it.** Screenshots at 1440 and 390, walked top to bottom. Measurement alone
   has passed pages with 3px-tall photographs and text scaled 1.09× on them.
5. **Run the floor**, then write a suite that encodes whatever you just got wrong.

---

## 1 · The document

`resources/views/layouts/site.blade.php`. Every page extends it and brings content only.

| Slot | For |
|---|---|
| `@section('title')` | the **whole** title — `/inicio` leads with the company name, the others trail it |
| `@section('current')` | which nav item is marked |
| `@section('body')` | extra `<body>` classes — the sold theme uses this |
| `@push('css')` | page-only stylesheets, after the shared four |
| `@push('head')` | preloads |
| `@section('content')` | the page |
| `@section('after')` | below the footer: phone dock, photo viewer |
| `@push('js')` | page scripts |

The layout owns charset, viewport (`interactive-widget=resizes-content`), robots, the
fonts, and `mc-tokens` + `brandbook` + `catalog` + `foot`. **Never repeat them in a page.**

Held by `tools/audit/suites/layout.mjs`.

---

## 2 · The grid, and the spine

Two containers, and they agree to the pixel.

- **`.cat-wrap`** — the normal container. `max-width: var(--mc-container)` (1200), gutters
  `--mc-gutter-m` (16) / `--mc-gutter-d` (32) at 768.
- **`.ct-grid`** — the full-bleed grid, for a section that must reach the screen edge while
  its text stays on the spine:

```
[full-start] minmax(gut,1fr)
[main-start] repeat(12, minmax(0, calc((container - 2*gut) / 12))) [main-end]
             minmax(gut,1fr) [full-end]
```

  Built from the container token, **never from `100vw`**: `calc((100vw - 1200px)/2)` is
  wrong by half a scrollbar and puts a section a few pixels off every `.cat-wrap` above it.

**The spine** is where every heading starts — 152px at 1440. Assert it: three sections,
one x. It has broken twice (a 32px and a 16px error), both invisible by eye.

---

## 3 · Type

One ladder, in `mc-tokens.css`. Sizes are chosen by the **measure they must fit**, not by
taste — `--t-mega`'s ceiling is 100 because the longest line renders 523px in a 663px
column at that size.

| Token | px | Used for |
|---|---|---|
| `--t-mega` | 52 → 100 | a page's opening statement. `/contacto`, `/vende` |
| `--t-h1` | 34 → 52 | the thing that matters most on the page — a phone number, a km figure |
| `--t-h2` | 28 → 40 | section headings |
| `--t-km` | 22 → 28 | the one-sentence statement. A size, despite the name |
| `--t-prose` | 17 → 18 | body |
| `--t-value` `--t-small` `--t-label` | 17 / 14 / 13 | values, notes, labels. 13 is the floor |

Big type takes negative tracking (`-.02em` to `-.045em`) and line-height under 1.

**A contact page whose phone number is smaller than its headings has its priorities
backwards.** Inverting them deliberately is allowed and is the design.

---

## 4 · Colour

- **Ink ladder** (`--mc-ink` / `-2` / `-3`) is this system's grey: blue-cast, never black,
  every step already proven for contrast on all four grounds.
- **One accent** (`--mc-price`, `--mc-accent`) plus **one channel colour** (`--mc-wa`).
- **Marque fields** — `#022254` VW, `#930016` Audi, `#004086` BMW, `#01172E` Mercedes,
  `#C50007` Porsche. The same hex wherever a marque appears: catalogue rows and the
  `/vende` tiles are the same objects.
- **Dark bands** are `--mc-navy` for the site's own, `#000` only when quoting TikTok, and
  `--mc-deep` for the footer. Three different darks on purpose so they never merge.

**State themes are one token block, not thirty rules.** `.car--sold` repoints every accent
at the ink ladder and no component knows it happened. Remember `--mc-ok` and `--mc-warn`
are their own literals, not aliases of `--mc-wa`.

**On a dark ground, state the colour on `<b>`.** `brandbook.css` has a global
`b, strong { color: var(--mc-ink) }` that beats inherited white — it rendered labels at
1.12:1 and looked merely "a bit dim".

---

## 5 · Page patterns

Five, and they cover everything built. Do not invent a sixth without a reason.

| Pattern | Where | The rule |
|---|---|---|
| **Split opening** | `/contacto` 7\|5, `/vende` | words on the page ground, photograph in a field shaped like its own ratio. No headline over a photograph unless the frame has been read (§7) |
| **Full-bleed band** | `/contacto` distances, `/inicio` social | for the one section that carries weight. Quiet only reads as quiet with something loud either side |
| **Question \| answers** | `/vende` 4\|8 | heading and its sentence left, the fields right. Stacks below 900 |
| **Ladder rows** | `/contacto` ways, `/vende` prices, `/coche` warranty | label small above, value large below. No hairlines between — space does that, which is why the space is large |
| **Column beside content** | `/coche` | everything that comes with the car in one column. The sidebar spans every row of the other column or it dictates row 1's height |

**Section air**: 96 a side (`--s-9`), 48 on a phone. **128 a side is a hole**, measured —
two of them meeting is 256px of nothing.

---

## 6 · Components added since the brandbook

The brandbook's `mc-*` set still holds. These are new, and are page-scoped by prefix
(`ct-` contact, `sl-` sell, `car-` car, `hm-` home, `cat-` catalogue).

| Class | What | The decision |
|---|---|---|
| `.ct-way` | a way to reach him | value at `--t-h1`, larger than the heading above it. Faint rule underneath at rest, ink on hover — a link must read as one without colour |
| `.ct-trips` | the distance band | three equal columns on navy, `tabular-nums` |
| `.sl-marque` | a marque tile | the catalogue's colour, a radio underneath, the tile is the label. The chosen one lifts and takes a white ring; the others stay lit — dimming the rest reads as a disabled toolbar |
| `.sl-pill` | a two-to-four-way choice | never a dropdown for four options |
| `.sl-drop` `.sl-prev` | photograph upload | previews `object-fit: contain` — nothing anyone hands us is cropped. Removing one rebuilds the input through a `DataTransfer`, so the input stays the truth |
| `.sl-sec__n` | the numbered movement | a navy square. The only ornament on that page |
| `.car-off` | warranty / maintenance panel | same shape for both; the only difference is one starts at "Incluido". The included step inverts to navy |
| `.car-tab-sold` | the sold stamp | 72px square, square corners — a stamp with rounded corners is a button. Fixed bottom-left; on a phone it gives way to the dock |
| `.hm-soc` | the TikTok band | the number carries TikTok's chromatic split. Borrowing the visual language *of the thing linked to* is what stops it being decoration |
| `<x-img>` | every photograph | §7 |

---

## 7 · Photographs

**Read the frame before writing on it.** Draw the file into a canvas, reduce it to three
12×16 grids — mean luminance, mean gradient, mean saturation. Do this *before* choosing
where type goes. A gradient scrim under a headline usually means the type is in the wrong
place.

**Delivery** is `<x-img>`: a path, a measured `sizes`, a `max`.

- Derivatives are WebP q82 from `/img/{w}`, cached forever at `/storage/cache/`, served by
  nginx without touching PHP. Widths: 320 / 480 / 720 / 1080 / 1600 / 2000 and no others.
- **`sizes` must account for HEIGHT on a tall `object-fit: cover` box.** A 3:4 photograph
  in a 531×1049 slot needs 787px, not 531 — sized by width the browser picks too small and
  scales up.
- `naturalWidth` is **not** the file's width once a `w` srcset is in play; fetch
  `currentSrc` to check for upscaling.
- Nothing is ever drawn larger than the file. An upscaled crop is the single thing that
  reads as cheap however good the type is.
- Customer photographs are **never cropped**. Vehicle cards deliberately are (4/3 cover).

Nobody runs anything: `VehicleObserver` queues on save, `motorclass-v2-queue.service`
builds, `motorclass-v2-images.timer` sweeps hourly.

---

## 8 · Motion

Scroll-driven motion is **linear**; entrances use `--e-out`.

| Thing | Number |
|---|---|
| arrival | 16px rise, `--m-reveal` (420ms), 60ms stagger, **once** |
| colour / border / opacity | `--m-quick` 160ms |
| panels, lines | `--m-move` 280ms |
| the TikTok counter | 1400ms out-cubic, once |

Every motion has a `prefers-reduced-motion` path that is not "nothing happens" but "the
end state, immediately". A photograph that drifts a few pixels over two screens is a
companion; one that scales is resampling and looks cheap.

---

## 9 · Forms

The `/vende` pattern is the reference. Six required questions, not seventeen: ask what the
person *knows*, establish the rest yourself.

Five defences, none of which costs an honest visitor anything — and **no CAPTCHA**, which
charges every honest user for the few who are not:

| Layer | Rule |
|---|---|
| throttle | 4/hour, 10/day per IP (`RateLimiter::for('sell-car')`) |
| honeypot | a field off-screen, `aria-hidden`, `tabindex="-1"` — not `display:none` |
| clock | encrypted timestamp; under 4s refused, over 2h stale |
| content | links, markup, Cyrillic/CJK/Arabic |
| files | server-sniffed `mimetypes` **and** `getimagesize`, min 200×200, 60 MB total |

Public POSTs also carry `PublicFormLimits`: `bootstrap/app.php` removes every PHP limit for
the admin's 359 MB uploads, and that must not apply to strangers.

---

## 10 · The floor

Nothing ships until these pass. `tools/audit/README.md` lists every check and every
false positive already encoded.

```
node tools/audit/audit.mjs contrast|overflow|targets|nojs /route
node tools/audit/colour-scan.mjs /route      # colour per 100px band
for f in tools/audit/suites/*.mjs; do node "$f"; done
```

Fifteen suites. **When you get something wrong, the fix is a suite, not a memory.**

---

## 11 · The traps, in one list

Each cost real time at least once.

1. `BrandbookOnly` serves a **200** holding page for any path not on its allowlist. A new
   route "works" and is not your page.
2. `overflow: hidden` on an ancestor silently kills `position: sticky`. Use `clip`.
3. An absolutely positioned grid child with a definite `grid-column` is laid out against
   its **grid area**, not the padding box.
4. A grid item **sizes its row** — a sidebar in row 1 dictates the height of row 1.
5. `srcset` beats `src`. Setting `.src` alone changes the attribute and not the picture.
6. `--mc-head-h` says 56 and the header renders 100 at 390 (the nav wraps).
7. `background` shorthand resets `background-clip`.
8. A later rule at the same specificity wins — check the whole file before adding a media
   query.
9. Chrome does not surface `Content-Encoding`; compare `transferSize` to `decodedBodySize`.
10. Reading back the property you just set proves you set it, **not that it did anything**
    — a `filter` on a cross-origin iframe was inert for weeks.
11. An edit whose end boundary sits before its start duplicates the file. Work back to
    front and verify by counting section banners.
