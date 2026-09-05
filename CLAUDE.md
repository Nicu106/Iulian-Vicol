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
`git log --oneline -1` must still be `643a3b9`.

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
3. **Test in more than Chrome's head.** Safari collapsed every card to a 22–35px sliver
   because `flex-shrink:1` in an `overflow-x:auto` flex container lays out against the
   VISIBLE box. Chrome happened not to. Assert the invariant ("each card is exactly its
   computed width"), not the accident ("no overlap in Chrome").
4. **Stress the system, not the sample.** The 24 current reviews are not the input; "any
   image, any text, any length" is. `shapeFor()` was run over 132 ratio×length
   combinations before it was trusted; that found a fatal (`end()` on a class constant).
5. **The audit floor** — run before every commit that touches a page:

       node tools/audit/audit.mjs contrast /inicio     # 0 failing (occluded ≠ failing)
       node tools/audit/audit.mjs overflow /inicio     # 0 at 390 / 768 / 1400
       node tools/audit/audit.mjs targets  /inicio     # only the keyboard-only halt (16x6) may show
       node tools/audit/audit.mjs ladder   /inicio     # {} {} {} {}
       node tools/audit/audit.mjs nojs     /inicio     # hiddenOnlyWithoutJs: ["button.hm-fb__halt"] only

   Known, deliberate "failures" (do not fix them): the contact hero words read as
   white-on-band because the tool cannot see the photograph under them (they are 13.6:1
   on it); `button.hm-fb__halt` is a keyboard-only stop control, 16x6 at rest by design.
6. **Regression suites** live in `tools/audit/suites/` (see its README). Run the relevant
   one after any change to that component. They fail without the fix they encode.

## 3. The system: `public/css/mc-tokens.css` is canonical

Everything on every page comes from the token file; the brandbook at `/brandbook` parses
it live. Never introduce a literal that has a token. When a value has no token and needs
one, add the token WITH a comment saying why (see `--mc-scrim`). Current ladders:

    colour   --mc-bg #F4F6FA · --mc-surface #FFF · --mc-band #E8EDF5 · --mc-deep #040B1F
             --mc-ink #111C2E · --mc-ink-2 #475467 · --mc-ink-3 #5D6B80 (lightest text allowed)
             --mc-hairline #E3E8F0 (decorative) · --mc-rule #C9D2DF (structural edges)
             --mc-blue #1558D6 (primary) · --mc-price #C6352A (price ONLY) · --mc-wa #017B37
             --mc-scrim 4,7,14 (channels; the black inside gradients over photographs)
    type     --t-h1 34→52 (also --t-display) · --t-h2 28→40 · --t-h3/--t-sub 18 · --t-prose 17→18
             --t-ui 16 · --t-small 14 · --t-label 13 (the floor). Family: DM Sans everywhere.
    space    --s-1 .25rem … --s-5 1.5rem --s-6 2rem --s-7 3rem --s-8 4rem --s-9 6rem --s-10 8rem
    radius   --mc-r-card 0px — the car card and everything inside it is SQUARE (client decision).
             --mc-r-s 4px chips · --mc-r-l 22px legacy/brandbook · --mc-r-pill icon discs only
    motion   --m-instant 80 · --m-quick 160 · --m-state 220 · --m-move 280 · --m-reveal 420 (ms)
             --e-out .22,.61,.36,1 · --e-inout .4,0,.2,1 · --e-in · --e-line
             Derive, don't invent: a 630ms flip is `calc(var(--m-reveal) * 1.5)`.
    image    --pos-portrait 50% 40%  (only meaningful when something IS cropped; see §5)

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

## 5. Photographs: NEVER CROP. Ever.

The admin uploads phone photos constantly; nobody will set focal points. Measured: a
2.35:1 band showed 49% of a 3:4 photo's height (heads off); a 260px column beside text
showed 78% of its width (people cut at the edge). Twitter abandoned saliency cropping
of people after publishing its bias numbers; no CDN publishes accuracy figures.

The rule: **the box takes the image's own aspect ratio** (`HomePageController::ratioOf()`,
cached per path+mtime) and the image is `object-fit: contain`. Then there is nothing to
cut and nothing to letterbox; a truly odd image is matted whole on `--mc-band`, never
trimmed. Text that must share the box steps its type down (`--scale`) before the box
widens. Serve through `route('img.resize', ['w'=>…])` with a 400/600/900 srcset — the
originals average 702KB.

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
