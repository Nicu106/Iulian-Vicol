# IV MOTORCLASS — v2 design environment. Read this before touching anything.

You are continuing a long design engagement for a Spanish used-car dealer
(ivmotorclass.com, Málaga, five German marques, one owner who answers WhatsApp
himself). The client writes in Romanian, judges by screenshots in Chrome and
Safari and on a phone, and has a precise, demanding taste: premium, unique,
never generic, never "cheap". The standard already reached is high. Match it.

## 0. Two environments. One rule.

    /var/www/motorclass       PRODUCTION  (ivmotorclass.com)      — NEVER modify. Read only.
    /var/www/motorclass-v2    THIS REPO   (v2design.ivmotorclass.com) — all work happens here.

Separate PHP-FPM pool, separate SQLite DB, separate git. Production has had zero
commits from this engagement and it must stay that way. If you find yourself
editing under /var/www/motorclass, stop. Verify at the end of every session:
`git -C /var/www/motorclass log --oneline -1` must still be `643a3b9` (this repo's HEAD is
something else — the path matters).

Reach the dev site from this box with
`curl --insecure --resolve v2design.ivmotorclass.com:443:127.0.0.1 https://v2design.ivmotorclass.com/...`
and in puppeteer with `--host-resolver-rules=MAP v2design.ivmotorclass.com 127.0.0.1`.

## 1. The client's design contract is law

The client supplied the "Contract de calitate în design frontend" and asked that it
be applied on every design request. Its full text is in
`docs/DESIGN-GUIDE.md §1`. The parts that most often decide the outcome:

- **Process first.** Direction + audit of the direction BEFORE code. The audit question:
  "if I'd been asked this for any other product in the category, would I have proposed the
  same thing?" If yes, it is the default, not design. Change it and say what changed.
- **Forbidden defaults** — recognise them and refuse them: card kits with uniform radius
  and soft grey shadows; 01/02/03 markers on things that aren't a sequence; ALL-CAPS eyebrow
  labels; the 3-cards-with-icon grid; centred hero + two buttons; fade-and-slide-up on every
  section; hover-lift on every card; "→" glued to button text; Title Case; "seamless",
  "unlock", "transform"; unverifiable claims.
- **Boldness is spent in ONE place per page.** Everything else is quiet and disciplined.
- **Every element justifies its existence.** Before declaring done, remove one thing.
- **Explicit constraints beat your aesthetic judgement, always.** If the brief fixes
  colours, fonts or a concept, respect it exactly.
- **First reply to a design brief: direction and its audit. No code.**

## 2. Measure. Never guess. Then LOOK.

This is the discipline that produced the current quality, and the one most at risk when
a new session starts:

1. **Every claim is a measurement.** "It fits", "it's centred", "nothing overlaps",
   "the speed is 34px/s" — each of these was asserted by a script and printed as a number
   before it went in a commit message. Write the probe, run it, quote the number.
2. **Then look at the frames.** Measurement missed: seams drawn on an untouched photo;
   white-on-white strips at 1.08:1; a 3-pixel photograph; heads cut off; a 90° card as a
   hole in the row. Screenshots caught every one. Take them (`tools/audit/audit.mjs shot`,
   or a puppeteer `elementHandle.screenshot()`), and open them with the Read tool.
3. **Test in more than Chrome's head — but know there is no Safari on this box.** The
   client tests Safari and phones and sends screenshots; ask for them. Safari collapsed
   every card to a 22–35px sliver because `flex-shrink:1` in an `overflow-x:auto` flex
   container lays out against the VISIBLE box; Chrome happened not to. So encode Safari
   lessons as invariants a Chrome run can assert ("each card is exactly its computed
   width" — `tools/audit/suites/reviews-widths.mjs`), not as the accident ("no overlap").
4. **Stress the system, not the sample.** The current reviews (25 active in the DB, all
   with photographs; 24 render because one quote is a single character and
   `HomePageController` keeps only quotes over 20 chars) are not the input; "any image,
   any text, any length" is. `shapeFor()` was run over 132 ratio×length
   combinations before it was trusted; that found a fatal (`end()` on a class constant).
5. **The audit floor** — run before every commit that touches a page, from the repo root:

       cd /var/www/motorclass-v2
       node tools/audit/audit.mjs contrast /inicio     # failing: 0   (occluded: 48 = the review back faces; fine)
       node tools/audit/audit.mjs contrast /contacto   # failing: 2   — exactly p.ct-hero__kicker and p.ct-lead (see below)
       node tools/audit/audit.mjs overflow /inicio     # overflow: 0 at 390/768/1400; escaping: ["button.hm-fb__halt"] is expected
       node tools/audit/audit.mjs targets  /inicio     # small: only "Detener el movimiento" 16x6 (keyboard-only control)
       node tools/audit/audit.mjs ladder   /inicio     # {} {} {} {}
       node tools/audit/audit.mjs nojs     /inicio     # hiddenOnlyWithoutJs: ["button.hm-fb__halt"] only

   **Reading the output.** `failing` is the verdict; `occluded` is text covered by something
   (not a failure); `escaping` lists elements outside the viewport (the halt button is
   off-screen by design); `hiddenInBothStates` is informational (a CSS decision, 3 on
   /inicio, 2 on /contacto); `worst` names the offenders. There are seven checks —
   `contrast overflow targets ladder nojs measure shot` — and a bare `audit.mjs` silently
   runs `contrast /`, so always pass both arguments.
   Known, deliberate "failures" (do not fix them): the two contact hero lines read as
   white-on-band because the tool cannot see the photograph under them (they measure
   13.6:1 on it); `button.hm-fb__halt` is a keyboard-only stop control, 16x6 at rest.
6. **Regression suites** live in `tools/audit/suites/` (see its README; run them from that
   directory). Each prints ✓/✗ lines and exits 1 on any ✗ — a non-zero exit IS a
   regression. Run the relevant one after any change to that component.

## 3. The system: `public/css/mc-tokens.css` is canonical

Everything on every page comes from the token file; the brandbook at `/brandbook` parses
it live. Never introduce a literal that has a token. When a value has no token and needs
one, add the token WITH a comment saying why (see `--mc-scrim`). Current ladders:

    colour   --mc-bg #F4F6FA · --mc-surface #FFF · --mc-band #E8EDF5 · --mc-deep #040B1F
             --mc-ink #111C2E · --mc-ink-2 #475467 · --mc-ink-3 #5D6B80 (lightest text allowed)
             --mc-hairline #E3E8F0 (decorative) · --mc-rule #C9D2DF (structural edges)
             --mc-blue #1558D6 (primary) · --mc-price #C6352A (the price) · --mc-wa #017B37
             --mc-accent #A8330F (darker red: 13–16px text, error borders) · --mc-accent-dark hover
             --mc-accent-tint price-chip ground · --mc-navy #0E2E57 (footer, fixed bars; 13.57:1 white)
             --mc-scrim 4,7,14 (channels; the black inside gradients over photographs)
    type     --t-h1 34→52 (also --t-display) · --t-h2 28→40 · --t-h3/--t-sub 18 · --t-prose 17→18
             --t-ui 16 · --t-small 14 · --t-label 13 (the floor). Family: DM Sans everywhere.
    space    --s-1 .25rem … --s-5 1.5rem --s-6 2rem --s-7 3rem --s-8 4rem --s-9 6rem --s-10 8rem
    radius   --mc-r-card 0px — the car card and everything inside it is SQUARE (client decision).
             --mc-r-s 4px chips · --mc-r-l 22px BRANDBOOK SPECIMENS ONLY · --mc-r-pill icon discs only
    motion   --m-instant 80 · --m-quick 160 · --m-state 220 · --m-move 280 · --m-reveal 420 (ms)
             --e-out .22,.61,.36,1 · --e-inout .4,0,.2,1 · --e-in · --e-line
             Derive, don't invent: a 630ms flip is `calc(var(--m-reveal) * 1.5)`.
    image    --ar-* and --pos-portrait are LEGACY (brandbook specimens only). Product
             testimonial photographs are never cropped — see §5. --ar-card 4/3 IS live: the car card.

`docs/DESIGN-REVIEWER.md` is a strict critic prompt for reviewing a section; its ladder
table predates the square-card decision — the token file wins where they disagree.

## 4. Motion principles (sourced — numbers in docs/DESIGN-GUIDE.md §4)

- **Scrubbed = linear.** Anything mapped to scroll position moves linearly; easing a
  scrubbed transform is what makes scroll and picture disagree (GreenSock's own rule).
  Put the feel in the stagger and in opacity, never in the scrubbed position.
- **Speed in px/s, never a fixed duration** — or adding one review silently changes the speed.
- **Velocity is damped, not set:** `v += (want - v) * (1 - Math.exp(-dt/TAU))`, TAU ≈ 150ms.
  dt-normalised so 120Hz doesn't run double. Hover slows (to 25–35%), never hard-stops.
- **Stagger 50–80ms; spend the time on the arrival**, not the debris.
- **Edge masks: three stops, middle at 20% alpha**, in px (Linear ships 64px). A two-stop
  linear mask reads as grey haze.
- **No per-item scale/fade in a moving row.** Uniform motion + a good mask is the expensive read.
- **`scrollLeft` rounds.** Never `scrollLeft += 0.57` — it becomes 1px/frame. Own the
  position as a float and assign it.
- **Auto-moving content must be stoppable (WCAG 2.2.2).** "A mechanism" need not be
  visible: the keyboard-only halt button (visually hidden until `:focus`) conforms with zero
  chrome. Hover-pause + reduced-motion alone does NOT conform.
- **`prefers-reduced-motion`**: nothing moves, and every piece of content must still be
  reachable (the reviews go static: photo above, words below). Same for no-JS.

## 4. One layout: `resources/views/layouts/site.blade.php`

Every page extends it. A page brings its content and nothing else:

    @section('title')    the WHOLE title — /inicio leads with the company name,
                         the others trail it, so the layout appends nothing
    @section('current')  which nav item is marked
    @section('body')     extra <body> classes (the sold theme uses this)
    @push('css')         page-only stylesheets, after the shared four
    @push('head')        preloads
    @section('content')  the page
    @section('after')    below the footer — the phone dock, the photo viewer
    @push('js')          page scripts

The layout owns charset, viewport, robots, the fonts, and mc-tokens + brandbook +
catalog + foot. Do not repeat them in a page. Before this there were five
standalone documents and they had already drifted: /coche carried
`interactive-widget=resizes-content` and /catalogo did not.

`tools/audit/suites/layout.mjs` holds it. When refactoring anything structural,
capture a fingerprint first (doc height, box geometry, stylesheet list, nav, title)
and diff it after — that is what caught /inicio silently losing its `home` body
class and having its title rewritten.

## 4a. A new page returns 200 and is not your page: the allowlist

`app/Http/Middleware/BrandbookOnly.php` holds this environment closed. Any path not in
its `ALLOW` list gets `pages/held.blade.php` — status 200, navy, titled "Brandbook".
It is not a 404, so a curl looks fine and the contrast audit reports 15 elements
checked. /vende was "working" for two turns before I noticed. Add the path (and any
POST or redirect alias) to `ALLOW` when you add a route.

## 4b. Before you put type on a photograph, read the photograph

Draw the file into a canvas and reduce it to three grids — mean luminance, mean
gradient, mean saturation, 12 x 16 for a portrait file. That is one `pg.evaluate`
and it tells you where the frame is calm, where it is busy and where the colour is.
Do it BEFORE choosing where the words go, not after they look wrong.

A gradient scrim under a headline is usually a sign the type is in the wrong place.
On /contacto the headline sat over the wheel and the lit floor — the busiest and
brightest regions in the file — and the scrim was treating a self-inflicted wound.
The client's own words: *"cand alegi sa pui text pe o imagine trebuie sa o faci
strategic, nu doar sa-l pui ... ce ai facut acum impresiona lumea acum 10 ani."*

Also read the file's SHAPE. /contacto's photograph is 3:4 and was being forced into
a 16:10 band, losing 53% of itself. A portrait file wants a portrait-shaped field.
Full method, numbers and the crop that came out of them: docs/DESIGN-GUIDE.md §3b.
Layout traps that cost a rebuild each (full-bleed grids and `100vw`, `overflow:
hidden` killing sticky, `--mc-head-h` lying at 390px, sticky panels opening seams):
§4b.

## 4c. Images are automatic — do not add a manual step

Photographs are served as resized WebP derivatives built by `App\Support\Img` and
`/img/{w}`, cached forever under `/storage/cache/` and served from there by nginx
without touching PHP. Templates use `<x-img>`; give it a path, a measured `sizes`
and a `max`, nothing else.

Nobody has to run anything. `VehicleObserver` queues the work on save,
`motorclass-v2-queue.service` does it, and `motorclass-v2-images.timer` sweeps up
hourly for whatever the observer cannot see. The units are in `docs/systemd/`.
If you add a page with photographs, use `<x-img>` and you are done — measure the
slot at 320/390/768/1000/1440/1800 first and put the real numbers in `sizes`.

The trap: `sizes` must account for HEIGHT on an `object-fit: cover` box that is
taller than the image's ratio, or the browser picks a file too small and scales it
up. Details and the rest of the measurements: docs/DESIGN-GUIDE.md §3d.

## 5. Customer photographs: NEVER CROP. Ever.

Scope: this rule is for CUSTOMER/TESTIMONIAL photographs — unknown ratio, uploaded by the
admin, people in them. Vehicle photographs are different: the car card is deliberately a
fixed 4/3 `object-fit: cover` (`--ar-card`), as is the detail page's lead photo. Do not
"fix" those. The admin uploads customer photos constantly; nobody will set focal points. Measured: a
2.35:1 band showed 49% of a 3:4 photo's height (heads off); a 260px column beside text
showed 78% of its width (people cut at the edge). Twitter abandoned saliency cropping
of people after publishing its bias numbers; no CDN publishes accuracy figures.

The rule: **the box takes the image's own aspect ratio** (`HomePageController::ratioOf()`,
cached per path+mtime) and the image is `object-fit: contain`. Then there is nothing to
cut and nothing to letterbox; a truly odd image is matted whole on `--mc-band`, never
trimmed. Text that must share the box steps its type down (`--scale`) before the box
widens. Numbers: `ratioOf()` clamps the file's ratio to 0.45–2.2 and `shapeFor()` to
0.2–4.0; type steps are 1/.9/.8/.7 (`--scale`, solved for the 560px desktop card) and a
SECOND scale `--scale-m` solved for the phone box (496 tall, ≤343 wide) — a 0.88-ratio
photo kept scale 1 on a card that had shrunk 30% and overran 126px until it existed;
only past the smallest step does the card widen, to `FB_W_HARD` 900, and the photo sits
matted whole. On phones a ≤5% mat is accepted where the 88vw width cap binds (uniform
height + ratio-exact width + screen width cannot all hold). Serve through
`route('img.resize', ['w'=>…])` with a 400/600/900 srcset — the originals average 702KB.

## 6. Responsive & browser lessons (each cost real time)

- Test at **390, 768, 1400/1440**; phone breakpoint is `max-width: 999px`, hero/photo
  behaviours switch at 900/1000. Phone is the primary screen, not a reduced version.
- **`flex-shrink: 0` on items and rows inside a scrolling flex container** (Safari), and
  **flex longhands when the basis is a `var()`** (`flex-grow/shrink/basis`), not the shorthand.
- **Never size a card to its contents when a child's width comes from `aspect-ratio` on a
  stretched box** — it's circular and the browser guesses low (370px card, 423px photo).
  Fix the height; derive widths from it.
- `<figure>` carries UA `margin: 1em 40px` — zero it. `cat-wrap` carries `margin-inline:auto`
  — never put a `max-width` on the same element or the block centres.
- The header is `position: relative` (scrolls away); a sticky `top:0; height:100vh` stage
  therefore overhangs the fold by the header height at rest — anchor words to the viewport.
- `mask-image` needs `-webkit-mask-image` too.
- **When editing CSS with scripts, find the block's END by a marker that comes AFTER its
  START.** `s.index('/* ---- how it actually goes')` sat BEFORE the block and triplicated
  the file — twice. Line-based replacement with asserted boundaries, then `grep -c` the
  banner to prove there's exactly one.
- Font metrics cannot be estimated reliably (8.9–11.5 px/char across 23 reviews). Let the
  browser lay text out; decide only coarse things server-side (which layout, how wide).

## 7. Where things are

    resources/views/partials/head.blade.php, foot.blade.php   one header, one footer, all pages
    resources/views/partials/card.blade.php                     the car card (available/sold/demo)
    pages/inicio.blade.php   + public/css/home.css      HomePageController     /inicio
    pages/catalogo.blade.php + public/css/catalog.css   BrandCatalogController /catalogo
    pages/coche.blade.php    + public/css/car.css       CarPageController      /coche/{slug}
    pages/contacto.blade.php + public/css/contact.css   ContactPageController  /contacto
    pages/brandbook.blade.php+ public/css/brandbook.css BrandbookController    /brandbook
    public/css/mc-tokens.css   THE tokens        docs/vault/   structured project knowledge
    tools/audit/               measurement floor + regression suites (README there)

    Page JavaScript is INLINE in each page blade — the reviews engine is the <script> in
    inicio.blade.php from ~line 287. public/js/* is legacy from the old site; ignore it.
    Reviews come in through the admin: model app/Models/Testimonial.php, table
    testimonials(author_name, author_location, image_path, quote, is_active, order_index),
    UI at /admin/testimonials, files under public/storage/testimonials/.
    docs/vault/70-Audit/ catalogues 46 defects (incl. security) — read it before any
    backend work; the eight production defects in DESIGN-GUIDE §6 are there.

Each component's design intent and the reasons behind it are in `docs/DESIGN-GUIDE.md §5`,
and in the commit messages — read `git log` for the file you are about to touch; the
messages are written as design rationale with the measurements that justified them.

## 8. How to work here

- Do the direction + audit first for anything new (contract §1). Then build the whole
  thing, then measure, then look, then fix what the frames show, then commit.
- **One commit per resolved concern, with a long message** in the existing style:
  what was wrong (measured), what changed, why it had to be that way, and the numbers that
  verify it. Future sessions read these as documentation.
- Prefer Bash + heredocs/python for edits. Write puppeteer probes as `.mjs` (ESM).
- Never add controls, labels, arrows or "chrome" to reduce your own uncertainty; the
  client reads chrome as cheap. Solve it in the motion or the layout.
- Never truncate content ("ningún texto recortado" is a promise on the page). Never
  print `author_location` (19 of 25 say Santander; the quotes say otherwise).
- The admin will keep adding reviews and photos. Build systems that adapt to any input,
  not arrangements tuned to today's 24. If it needs a human to look at each photo, it's wrong.
- Report honestly: what was verified, with numbers; what wasn't; what the client must decide.
