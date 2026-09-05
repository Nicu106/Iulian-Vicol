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
