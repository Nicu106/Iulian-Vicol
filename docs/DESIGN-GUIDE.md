# Design guide — the reference behind CLAUDE.md

Read `CLAUDE.md` first; it is the operating manual. This file holds the material it
points to: the client's contract in full, the research with its sources and numbers,
the reasoning behind each built component, and the catalogue of measurement
false-positives. It exists so that a different model, in a new session, reaches the
same decisions for the same reasons.

## 1. The client's contract (verbatim, Romanian)

Ești designer de produs și frontend engineer. Standardul nu e „funcțional și curat".
Standardul e o interfață despre care cineva care se pricepe la design spune că a fost
gândită de un om cu gust, nu asamblată din piese implicite.

Majoritatea interfețelor generate ratează nu pentru că sunt urâte, ci pentru că sunt
anonime. Arată ca orice alt produs. Sarcina ta e ca cineva să poată recunoaște acest
produs dintr-un singur screenshot, fără logo.

### Procesul e obligatoriu, nu opțional
Nu scrie cod la prima replică. Niciodată.
- **Pasul 1, direcția.** Tokenii de culoare (max 5, cu rol), rolurile tipografice (și de ce
  perechea asta), conceptul de layout cu wireframe ASCII, o propoziție despre ce face
  interfața distinctă, elementul unic pe care cheltui îndrăzneala.
- **Pasul 2, auditul direcției.** Dacă mi s-ar fi cerut asta pentru orice alt produs din
  categorie, aș fi propus același lucru? Dacă da, e defaultul, nu design. Schimbă și spune ce.
- **Pasul 3, codul.** Abia acum.
- **Pasul 4, scăderea.** Înainte de a declara gata, elimină un element. Spune ce ai eliminat.

### Defaulturi interzise
**Culoare:** crem cald + serif + terracotta; negru tentat (#0B0B0B/#111) în loc de o culoare
aleasă; gradient ca decor; accent care apare peste tot; mai mult de 5 culori.
**Tipografie:** Inter fără motiv; o singură familie cu greutăți ca substitut de ierarhie;
ALL CAPS spațiat pe etichete; monospace ca semnal de „tehnic"; titluri care sunt doar text
mai mare.
**Layout:** kit de carduri (radius uniform, umbră gri sub fiecare); markeri 01/02/03 pe
non-secvențe; eyebrow ALL CAPS deasupra titlurilor; grila de 3 carduri cu icon; hero centrat
cu două butoane; CTA-uri diferite ca umplutură.
**Motion:** fade-and-slide-up pe fiecare secțiune; hover-lift pe fiecare card; animație care
nu răspunde unei acțiuni.
**Copy:** transform/unlock/revolutionize/cutting-edge/seamless/empower; Title Case; „→" la
final de buton; afirmații neverificabile.

### Ce se cere în loc
Îndrăzneala într-un singur loc. Tipografia e designul (două familii care se contrazic
productiv; sărituri mari; <80 caractere/rând). Spațiul e material, densitatea o decizie.
Ierarhia se vede din trei metri (la 25% zoom). Fiecare element își justifică existența.
Un CTA principal, repetat consistent.

### Prag tehnic
Responsive real de la 360px, mobile primul; focus vizibil pe tot ce e interactiv;
`prefers-reduced-motion`; contrast WCAG AA verificat, nu presupus; HTML semantic;
tokeni în config, zero valori arbitrare; fără librărie de animație dacă CSS ajunge.

### Cum răspunzi
Prima replică: doar direcția și auditul ei. Fără cod. A doua, după aprobare: codul.
Ultima: ce ai eliminat și de ce. **Dacă brief-ul fixează culori, fonturi sau un concept,
respectă-le exact. Constrângerea explicită bate întotdeauna judecata ta estetică.**

## 2. What the client has said that changes how you work

- "nu stiu de ce faci totul asa de fara interes fata de fable … sa faci ceva unicat" —
  measured corrections are not design. Study, look at references, make something unique.
- "acesta pagina de contacte este tot un pic cam inchisa" — the site's spine is light
  (22–24% dark by page height); do not drift dark.
- "nu vreau butoane de baza … asta arata cheap" — chrome reads as cheap; then later, two
  simple buttons were explicitly requested. Follow the latest instruction; keep them minimal.
- "adminul va adauga feedback constant … trebuie sa lucreze perfect" and "nici nu vreau ca
  tu sa le aranjezi … sa putem incarca orice imagine si orice text de orice marime" —
  build systems, never per-item tuning. This ruled out manual focal points.
- "imi place abordarea ca pentru small review sa fie coloana si pentru mari sa pui intr-o
  parte" — the client likes adaptive shape that follows content.
- "sa dea vibe ca chiar sunt fotografii nice" — no frames, no mats around photographs.

## 3. Measurement: the catalogue of false positives

Every one of these was reported as a defect that was not there, and each correction is
now encoded in `tools/audit/audit.mjs` or in a suite:
- A background is not always up the ancestor chain (full-bleed rows paint on a sibling).
- Covered text is occluded, not low-contrast (hero words over a photograph the tool can't see).
- The target is the label, not the control (24px checkbox in a 356x57 label).
- 2.5.8 exempts a link in a sentence; a keyboard-only control has no pointer target.
- Decorative `aria-hidden` text is out of 1.4.3's scope.
- A table scrolling in its own box is not overflow.
- Gradient grounds read their first stop; translucent layers must be composited.
- Contrast "1:1" on a gradient = tool fault, not design fault.
- 82% dark by rendered pixels vs 59% dark by page height: say which you measured.
- "0 overlapping pairs in Chrome" was an accident; the invariant is "each card renders at
  exactly its computed width" (Safari shrank them).
- A test that samples right after a glide must account for residual velocity (that one
  turned out to be a real 14–16px bug, fixed by zeroing `v` at glide start/end).
- Puppeteer's `page.tap(selector)` failed to find a node `document.querySelector` found;
  drive buttons with in-page `.click()` in tests.

## 3b. Reading a photograph before you write on it

The client threw away two versions of /contacto. The second one was a full-bleed
photograph with a big white headline over a dark gradient, and his words were:
*"arata dea dreputl oribiil si iesftin ... nu este specific apple de loc sau google
sau custom design deloc"*, and then, when pressed on why: **"cand alegi sa pui text
pe o imagine trebuie sa o faci strategic, nu doar sa-l pui — trebuie sa analizezi
continutul imaginii, ce se afla in ea, si dupa asta sa incepi a edita ... ce ai
facut acum impresiona lumea acum 10 ani."**

He is right on both counts, and the second one is measurable.

**The method.** Draw the file into a canvas and reduce it to three grids (12 x 16
works for a portrait file). Mean luminance says where it is light or dark. Mean
absolute gradient says where it is calm or busy. Mean saturation says where the
colour lives. It takes one `pg.evaluate` and it settles arguments that otherwise
get settled by taste. Do this BEFORE deciding where type goes.

**What it said about `public/img/banner/contacto.jpg` (2400x3200):**

| region | reading |
|---|---|
| right half, below row 7 | luminance 13-29, gradient 0-7 — the calmest, darkest field in the frame |
| cols 1-3, rows 8-13 | gradient 24-37 — the busiest region: the wheel and its reflections |
| col 0, rows 9-15 | luminance 166-202 — a hard bright strip, the lit floor |
| rows 7-13, cols 5-11 | saturation 60-75% — the red tail light |
| rows 0-3 | luminance to 167 — garage ceiling and strip lights |

The headline had been set bottom-left: the wheel and the lit floor. The heavy
gradient it needed to stay legible was treating a wound that had been chosen. The
calm field was the opposite corner.

**What actually followed from the numbers** was not "move the type to the right".
It was that the file is 3:4 and was being forced into a 16:10 band, throwing away
53% of it, and that a portrait file wants a portrait-shaped hole. So the type came
off the photograph entirely — ink on the page ground, full contrast, no scrim — and
the photograph took a tall column beside it. Then the same maps chose the crop:
`object-position: 68% 46%` spends the 33% that `cover` discards on the bright noisy
left edge and on a near-black sliver of the car's right side (luminance 13-16), and
keeps the roofline, the glass and the tail light.

**The general rule.** A gradient scrim under a headline is usually a sign that the
type is in the wrong place. Read the frame first; place the type in the calm; and
if there is no calm region large enough, the honest answer is that the type does not
belong on the picture.

## 3c. When the client says something is missing and cannot name it

*"parca pagina asta are prea putina culoare si parca are nevoie de un pic mai mult
dar tot nu inteleg ce"* — the most useful kind of feedback, and the hardest to act
on by looking, because the fault is usually not in any one element.

**Scan the page in bands.** `node tools/audit/colour-scan.mjs /contacto` renders the
full page, cuts it into 100px bands and reports, for each: mean saturation, the
share of pixels over 15% saturation, and the share over 35%. A feeling becomes a
shape.

For /contacto it read:

| region | coloured pixels |
|---|---|
| y 0–1,100, the opening | 30–40% |
| y 1,100–3,600 | 0–7%, with **twelve consecutive bands at exactly 0** |
| y 3,600–4,195, the footer | 75–100% |

So the page was colour, then 2,500px — 60% of its height — of nothing, then colour.
Not a missing element: a missing *stretch*. Quiet only reads as quiet when something
loud sits on either side of it, and there was 2,500px of quiet with nothing to be
quiet against.

The fix was one band, not decoration spread thin: the distances section — the only
place on the page where other people speak — became a full-bleed navy field.
Whole-page coloured pixels went 24.3% → 40.5%, dead bands 12 → 9.
`--mc-blue-tint` was considered and rejected by the same measurement: at under 5%
saturation it would not have registered at all.

**And it caught a rule that had never done anything.** The map carried
`filter: grayscale(1)` with a paragraph justifying it. Rendered with `grayscale(1)`,
with `saturate(.9)` and with `none`, the pixels inside that frame are identical —
10.6% mean saturation, 25% over 0.10, in all three: a CSS filter on the parent does
not reach a cross-origin frame's own compositing. The rule had been written, read
back from `getComputedStyle`, believed and documented for weeks. Only sampling the
painted pixels showed it was inert. **Reading back the property you just set proves
you set it, not that it did anything.**

## 3d. Image delivery: what was actually wrong, measured

The client: *"vreau sa faci asa ca paginile astea sa se incarce super super rapid
... chiar daca cineva va intra pentru prima data."* Measured first, at 390px with a
cold cache:

| page | image bytes before | after |
|---|---|---|
| /coche | **7.17 MB** | 0.22 MB |
| /inicio | 1.94 MB | 1.15 MB |
| /contacto | 1.07 MB | 0.11 MB |
| /catalogo | 0.74 MB | 0.46 MB |

**The car page was serving the dealer's originals.** The stage photograph and all
42 thumbnails pointed straight at `/storage/...`: one file of 3.59 MB and another
of 1.80 MB, on a phone. The resizing endpoint existed and that page simply never
called it. Nothing about this is visible in a screenshot.

**Five things that were wrong, in order of what they cost:**

1. Originals served instead of derivatives — 7.17 MB → 0.22 MB.
2. Page banners served as raw JPEGs out of `public/` (contacto.jpg alone: 1.06 MB
   → 43 KB at 720px WebP). The endpoint bounced anything that was not `/storage/`.
3. `immutable, max-age=1y` on a URL with nothing in it that changes when the photo
   does. Replace a picture and returning visitors keep the old one until 2027. The
   URL now carries the source's mtime.
4. Derivatives served through PHP. Routing the banners through the endpoint cut
   /catalogo's hero from 253 KB to 48 KB **and pushed LCP from 532 ms to 680**,
   because a static file had become a Laravel boot. Built derivatives are now
   linked as files and nginx serves them; LCP fell to 324 ms.
5. Stylesheets uncompressed. `gzip on` with `gzip_types` commented out means
   text/html alone — 82 KB of CSS per page going out raw.

**Generation cost is the "first visitor" problem.** GD on this box: 1.36 s at
400px, 1.76 s at 1600, from a 4.68 MB source. Routing /coche through the endpoint
took its cold load from 1.3 s to 3.0 s before anything was pre-built.
`php artisan images:warm` builds what the first screen of every page needs — 630
derivatives, 228 s — and leaves the rest on demand. Run it after a bulk upload.

**What is NOT available here:** GD reports `AVIF Support: no`, there is no Imagick
and no cwebp/avifenc binary, so WebP is the format without a system change. AVIF
would save roughly another 20-30% on photographs; it needs a decision about
installing an encoder.

### Does this work for photographs uploaded LATER?

Yes, and it was tested rather than assumed. A file that had never been seen
before: `Img::size()` read it, the srcset was built from it, the endpoint produced
0.24 s at 320px and 0.57 s at 1080, and the second request took 0.026 s. Nothing
has to be told about a new photograph.

What did NOT happen on its own was pre-building, so the first visitor after an
upload paid that cost. `App\Observers\VehicleObserver` now watches the model —
not the admin controller, because a seeder, an import or whatever replaces that
screen later are other ways in — and fires when `cover_image` or `gallery_images`
change. Editing a price queues nothing.

**It runs after the response, not on a queue.** This server has no queue worker,
no supervisor and no cron for the scheduler. That was verified the hard way: the
first version dispatched to the database queue, two jobs were queued by a save,
and nothing ever ran them. `pgrep -f queue:work` had matched the grep's own
command line — a false positive worth remembering.

So `dispatchAfterResponse()` runs it inside the same PHP-FPM process once the
admin's page has been sent. Measured: `save()` returns in 14 ms and all five
widths of the new cover exist afterwards. That process is one of six
(`pm.max_children = 6`), so the job is bounded to 25 s and works in visible order
— card and stage, then the thumbnail strip, then the size a thumbnail press swaps
to. Anything past the budget is built on demand, which is the behaviour that
existed before and is not a failure.

For a bulk import, `php artisan images:warm --clicks` is still the right tool:
1,993 derivatives in 19 minutes. A real queue worker would remove the 25 s ceiling
and is one systemd unit, but it is a standing service and nobody has asked for
one.

## 4. Research findings with sources (motion, images, carousels)

**Speed / drift.** Libraries stating px/s pick 50 (Motion+ Ticker, react-fast-marquee);
Linear's and Vercel's shipped logo walls derive to 20–24 px/s; GSAP `horizontalLoop`
default 100. For sentences, use reading speed: 238 wpm silent (Brysbaert 2019) → a 40-word
review needs ~10s; a card is legible over ~700px of travel. Chosen: 34 px/s (contact strips),
then for the reviews row: 110 between cards / 24 at the detent / 2.2× arrival boost.

**Linear is mandatory when scrubbed.** Every shipped marquee hard-codes `linear`; Swiper's
default `ease` is why its marquee recipes all override it. GreenSock: "the horizontal
tween must use ease:none … a very common mistake."

**Damping.** Lenis `damp(x,y,λ,dt)=lerp(x,y,1-e^(-λdt))`, λ = lerp×60, lerp 0.1–0.125
(darkroom's satus uses 0.125) → τ ≈ 130–170ms. Rory Driscoll 2016 is the origin.
Motion+ hover slowdown: a tweened multiplier (`hoverFactor`), ~300ms — never
`animation-play-state: paused`.

**Throw / inertia.** iOS-derived, independently reached by Motion and Framer:
amplitude = 0.8×velocity, time constant 325–400ms, velocity sampled as a running average
(0.8/0.2), stop at |Δ| ≤ 0.5px. GSAP Draggable `overshootTolerance: 0` for loops.

**Masks.** Linear ships `--Marquee-shadow-size: 64px` with a three-stop curve: transparent
0 → `#0003` at half the band → black at the band. Perceived opacity is non-linear; a
two-stop mask reads as haze. Use px. `-webkit-mask-image` for Safari. Mask, not overlay
divs (overlays show as rectangles on any non-flat ground).

**Loop.** Two copies, translate by exactly one row (`calc(-100% - gap)` per copy, or a
modulo on an owned float position). Enough copies to exceed any viewport. Duplicates
`aria-hidden`.

**Stagger.** 50–80ms between pieces (Codrops movers 50ms, Slice Revealer 80ms, darkroom
`--reveal-stagger: 60ms`); per-piece 0.35–0.8s; layout morphs 0.9–1.2s inOut; destination
content 0.7–1s, offset into the tail of the morph.

**Carousels.** The popular "carousels don't work" case is largely folklore (misquoted
Nielsen, back-computed "32 clicks", unsourced Obama story); carousels also won A/B tests.
What holds: Runyon's ~1% interaction; fixed intervals defeated by reading time (production
showed 3 testimonials per 4s slide = 16 words readable at 238 wpm); Level A failure via
`pause:'hover'`. Brechman et al. 2015 (JMCQ 92(4), doi 10.1177/1077699015604851):
tickers that UPDATE beat tickers that SCROLL for comprehension, no downside in liking —
the reason the phone runs a stepped sequence, not a drift.

**WCAG 2.2.2.** Requires "a mechanism" to pause/stop/hide auto-moving content >5s; it need
not be visible. A visually-hidden, keyboard-focusable stop button (skip-link pattern)
conforms. Hover-pause + reduced-motion alone fails keyboard and touch users.

**Images of people.** Twitter/X abandoned saliency cropping after publishing demographic
parity gaps (8/4/7/2%); no CDN (Cloudinary, imgix, Thumbor) publishes accuracy figures.
WordPress core, Drupal, Craft and Sanity all ship MANUAL focal points emitted as
`object-position` — ruled out here because the admin will not set them. Hence: no crop;
box = image ratio; `contain`.

## 4b. Layout traps that each cost a rebuild

- **A full-bleed grid built from `100vw` is wrong by half a scrollbar.**
  `calc((100vw - 1200px)/2)` is the usual way to write one; `100vw` counts the
  scrollbar and an element's width does not, so every section built that way sits
  a few pixels off every `.cat-wrap` above it. Build the tracks from the container
  token instead — `minmax(gutter,1fr) [main-start] repeat(12, minmax(0, calc((container - 2*gutter)/12))) [main-end] minmax(gutter,1fr)` —
  and the main track resolves from the element's own width, which is what
  `.cat-wrap` resolves from. Assert it: three sections, one x.
- **And the main track is the container MINUS its gutters.** The first version put
  `main-start` at 120px while every `.cat-wrap` section under it started at 152.
- **Then do not add the gutter twice.** A `padding-inline` on an element already
  sitting in the main track put one caption 16px right of everything else.
- **`overflow: hidden` on an ancestor silently creates a scroll container and kills
  `position: sticky`.** Use `overflow: clip`. It is a very common line in a layout
  wrapper and it fails without any error.
- **`--mc-head-h` lies on a phone.** It says 56px; at 390 the header renders 100
  because the nav wraps to a second line. Anything that subtracts the header must
  measure it, not read the token — or, better, be built so it does not need to.
- **A sticky panel shorter than its column opens a seam.** A one-screen photograph
  in a 1,049px column held for ~180px and then let a 43px band of page ground open
  between its bottom edge and the top of the next section. Invisible in a
  screenshot of the landing. Check geometry at four scroll positions, not one.
- **Two 128px paddings meeting make a 256px hole, not air.** 96 a side reads as
  deliberate; 128 a side reads as a bug. Air is only air if something is on both
  sides of it.

## 5. The built components, and why they are the way they are

**Header/footer partials** — one of each for all pages. The footer wordmark is tone on
tone, lit from above (the client rejected outlines and a "black on navy" carve).

**Catálogo (/catalogo)** — one full-bleed row per German marque, its colour sweeping the
row (palette re-picked in OKLCH: VW #022254, Audi #930016, BMW #004086, Mercedes #01172E,
Porsche #C50007); "Ver todos" expands a marque to the whole screen with the edges visibly
travelling and the tone deepening; collapse re-anchors on the next section (0px unexpected
movement). Cards square, standardised to 3 or 4 per row. Netflix-style rails on phone.

**Coche (/coche/{slug})** — photos grouped Interior / Exterior / Imperfecciones (the
imperfections are shown on purpose — it is the dealer's honesty claim); arrows on the main
photo; full-screen viewer; chips year/fuel/transmission/power.

**Contacto (/contacto)** — the bold element: a full-screen car that is milled into twelve
strips which carry their slice of the car (never emptied — 56% of the scroll had no image
before that fix), walk into the two panels (contact details + Google map, both real
production data), and dissolve into them at 0px on all four edges. Linear scrub, staggered
outside-in, lift shadow while flying, three-stop mask, Google map behind a click shield
(it eats the wheel otherwise). The 720 km story sits below as three columns. Only cities
the review TEXTS name are used. Page is 13% dark (was 82%).

**Inicio (/inicio) — "Cómo va, de verdad"** — replaced the stock 4-step funnel whose
steps were untrue for this dealer; four moments each linking to the place on the site
that evidences it; a spine with square stops; numbers as the loud element.

**Inicio — the reviews row** — the most-iterated component. Final state:
- One rectangle per review, TWO FACES: photograph front, words back. Card = photo's own
  ratio (`shapeFor()`); `contain`; type steps down (`--scale` 1/.9/.8/.7, and a phone
  scale `--scale-m` solved for the 496×343 phone box) before the card widens. On the
  desktop none of the 24 is matted and 20 keep full type; on phones a ≤5% mat is the
  accepted residual where the 88vw cap binds. No border, two-layer shadow; large
  opening quote mark in `--mc-rule`; byline on a 2.5rem rule.
- Desktop: travels left→right for ever (owned float `pos`, wrap at row width, seam 0px);
  the PHOTOGRAPH sits in the middle and is the slowest thing on the page (detent: 24 px/s
  within 120px, up to 110 between; 2.2× arrival boost); the card TURNS AS IT LEAVES
  (LEAD 130px past centre, over 140px) — never at the centre (a 90° card is a hole).
  Two 48px buttons carry the centred picture across and bring the next; clicking any
  photo brings it to the middle; presses land at 0px (velocity zeroed at glide start/end).
  Hover → 35%; keyboard focus (`:focus-visible`, never the buttons) → stop; halt button
  keyboard-only; reduced-motion/no-JS → static stacked faces.
- Phone (≤999px): a STEPPED SEQUENCE, not a drift — photo 2.2s → turn (630ms transition)
  → hold 55ms/char (4–14s) → next. "Next" is one step of that sequence (turn, then advance);
  "previous" the reverse; a horizontal swipe IS a step (`touch-action: pan-y`, the card
  follows the finger 0.25× up to 40px, step on lift, 40px threshold); a finger down holds
  everything; a touch cancels any running glide and takes over from its target; a glide
  landing keeps the turn (`phase = reading ? 'text' : 'photo'`). Tapping the centred card =
  next; tapping another seats it.
- Images via `img.resize` (400/600/900 srcset); first 8 eager; row printed enough times
  to exceed 3600px; zero reviews renders nothing; `author_location` never printed.

## 6. Things the client has deferred / must decide (do not resolve unilaterally)

Catalogue banner stock photo; the "Soporte 24/7" claim (dropped); WhatsApp green choice; filters at ~25 cars; star ratings; eight production
defects catalogued but not applied; the `/inicio` people section and the contact page both
used a held-viewport gesture (the reviews row no longer does).
