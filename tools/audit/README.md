# Audit

A measurement floor that holds without any API. Every design claim in the brandbook
and in the vault was produced by one of these checks, and every fix was confirmed by
re-running the check over the same neighbourhood.

```bash
node tools/audit/audit.mjs contrast /catalogo
node tools/audit/audit.mjs overflow /brandbook --w 320,390,768,1400,1800
node tools/audit/audit.mjs targets  /catalogo
node tools/audit/audit.mjs nojs     /catalogo
node tools/audit/audit.mjs measure  /brandbook
node tools/audit/audit.mjs shot     /catalogo --w 390,1400
node tools/audit/colour-scan.mjs   /contacto        # colour per 100px band
```

`AUDIT_HOST` overrides the host (default `v2design.ivmotorclass.com`, resolved to
127.0.0.1 so it works on the box without touching DNS). Never point it at production.

Screenshots land in `tools/audit/out/`, which is not tracked.

Every check has a false-positive story behind it. These are the ones already encoded,
each of which reported a defect that was not there until the check was corrected:

- **A background is not always up the ancestor chain.** A full-bleed row paints its
  colour on an absolutely positioned sibling behind the text. Sampling the real
  stacking order at the text's own centre is the only reliable read.
- **Covered text is occluded, not low-contrast.** A phone specimen's sticky bar hiding
  the spec rows behind it is the demonstration. Reported separately, never as a failure.
- **The target is the label, not the control.** A 24px checkbox inside a 356x57 label
  is a 356x57 target.
- **`naturalWidth` is not the file's width once a `w`-descriptor srcset is used.**
  The browser divides the intrinsic size by the chosen candidate's density, so it
  comes back equal to the slot and any "is this upscaled?" comparison against it is
  a tautology. Fetch `currentSrc` and read the real header.
- **Chrome does not surface `Content-Encoding` through the devtools protocol** — it
  hands back the decoded body. Reading that header reports every stylesheet as
  uncompressed whether it is or not. Compare `transferSize` with `decodedBodySize`.
- **A `sizes` computed from the slot's WIDTH is wrong on a tall `object-fit: cover`
  box.** The contact hero is 531px wide and 1049 tall; a 3:4 photograph in it is
  bound by the height, so the file has to supply 1049 x 0.75 = 787px. Sized by
  width the browser picked 720 and scaled it up 1.09x — an upscaled photograph on
  the page rebuilt specifically to stop those.
- **The contrast check ran at one width.** It opened `WIDTHS.at(-1)` and nothing
  else, so everything that only exists on a phone — the contact dock, the phone-only
  footer grid, the phone reviews sequence — had never been contrast-checked at all.
  It reported a car page 0/78 clean while its fixed bottom bar rendered white text on
  white, because at 1400 that bar is `display: none`. Now every width, merged.
- **Sample the centre of what is ON SCREEN and UNCLIPPED, not the clamped centre of
  the box.** Clamping to `innerHeight - 1` moves the probe off the element whenever
  its middle sits below the fold, and it then reads whatever is painted there: 87
  /catalogo card titles were reported at 1.06 against a marque colour field they
  never touch. Intersect the rect with the viewport AND with every clipping ancestor.
- **A hairline at the fold is not a reading.** The scroll loop keys rows by sampled
  background, so a wrong sample taken while an element was 1px tall at the viewport
  edge survives next to the correct one taken a step later, and is counted. Skip
  anything with under 4px of visible width or height; it will be sampled properly at
  another scroll position.
- **An absolutely positioned grid child with a definite `grid-column` is laid out
  against its GRID AREA, not the container's padding box.** The full-screen viewer's
  arrows carry `grid-column: 1` and `3` from the desktop rule; on the phone's
  one-column template column 3 is an implicit line, so `right: 22%` resolved against
  a zero-width strip and the button sat 3px from the screen edge while its partner
  sat at 95px. The counter beside them was always centred — it is the one absolutely
  positioned child with no `grid-column` on it, which is what pinned the cause.
  `grid-column: auto` restores the padding box as the containing block.
- **A control parked off-screen until the keyboard finds it has no target yet.** The
  reviews row's Pause control (SC 2.2.2) is clipped to 16x6 at `left:-9999px` and
  becomes a 44px button on `:focus`. Measured where it is parked it failed 2.5.8 on
  every run of `/inicio`, which is how a check teaches people to ignore it. The check
  now focuses such an element and measures the box it actually presents, and lists it
  under `shownOnFocus` so the run shows the judgement was made rather than skipped.
- **2.5.8 exempts a link in a sentence**, judged by whether the holder carries text
  beside the link, not by the tag it happens to sit in.
- **Decorative text is out of 1.4.3's scope** when it is `aria-hidden` and its words
  appear in real text nearby. A 176px wordmark in a footer is an ornament.
- **A table that scrolls in its own box is not overflow.** That is the fix, not the bug.
- **Disabling JavaScript disables the tool's own `evaluate`.** Turn it back on after the
  document is parsed; the page's scripts have already been skipped.
- **What matters without JavaScript is the difference**, not the absolute state. An
  element hidden in both states is a CSS decision.

Things learned the hard way, all encoded above:

- `ch` is not a character. Counting a rendered line needs a Range walk per character,
  with whitespace collapsed, or the number is wrong in both directions.
- Blade's newlines and indentation are in the DOM, so an uncollapsed count over-reads.
- A background has to be resolved up the ancestor chain; the element's own is usually
  transparent.
- `:has(> .x)` also matches `.x` itself unless you add `:not(.x)` — an element is not
  its own child.
- 2.5.8 exempts a target whose size is set by the line-height of the sentence around it,
  so an inline link in a paragraph is not a defect.
- Anything gated on the viewport needs the page scrolled before it is measured, or you
  measure the un-started state and call it a bug.

## Regression suites — `tools/audit/suites/`

Each suite was written while fixing a real defect and fails without that fix. Run the
one for the component you touched; quote its output in the commit message.

```bash
cd tools/audit/suites
node reviews-desktop.mjs   # 12 behaviours: direction, photo at centre, buttons, click-to-centre, geometry, reduced-motion, no-JS, phone sizing
node reviews-phone.mjs     # 13 steps at 390px: auto turn/advance, next/prev alternation, swipes as steps
node reviews-photos.mjs    # asserts: 0 cut and 0 quotes needing the scroll fallback at every width; 0 mat ≥1000px, ≤5% mat below
node reviews-widths.mjs    # every card renders at exactly its computed width (the Safari flex-shrink guard)
node reviews-seam.mjs      # the loop seam is exactly one row: 0px error, every pair one stride apart
node reviews-speed.mjs     # one sample of the speed profile, 24–110 px/s (scrollLeft rounds — this once caught 1px/frame)
node sell-car-security.mjs  # attacks /vende: honeypot, a submission under 4 s, a
                           # missing and a forged timestamp, links / markup / Cyrillic
                           # in the text, no CSRF token, the rate limit WITH a real
                           # token (without one every attempt is 419 and the limiter
                           # is never reached), a PHP file renamed .jpg, a .php under
                           # /storage — and that an honest seller still gets through.
node social.mjs             # the TikTok section: below the reviews, three platforms
                           # with TikTok leading as the only non-quiet row, every link
                           # rel=noopener, the real figure in readable text with the
                           # counter aria-hidden, on black not navy — and the arrival
                           # sampled while it runs: 0 → counting → 600.000 with the
                           # chromatic halves settling into register, plus the
                           # reduced-motion path where it is simply there.
node layout.mjs             # one document for the site: no page declares its own,
                           # all five extend layouts/site, exactly one header/main/footer
                           # in that order, five nav links, the four shared stylesheets,
                           # the shared viewport and robots meta, each page's own title
                           # and nav mark — and a page can still dress the whole
                           # document, which is how the sold theme reaches the header.
node header.mjs             # the shared header: all five links on every page, the
                           # current one marked, 100px tall from 320 to 768 and 72 from
                           # 900 — one row of links that scrolls sideways on a phone, the
                           # last one reachable, no page overflow. It was 148px at 320.
node sell-car.mjs           # /vende: served past the BrandbookOnly allowlist, /sell-car
                           # 301s to it, five marques + "otra" in the catalogue's exact
                           # colours, six required questions, question | answers at 1440
                           # and stacked on a phone, previews whole, a REAL submission
                           # (two photographs) that lands on the received state and is
                           # stored pending with an images array and a cover, 404s on
                           # the public car page, then is deleted again.
node images.mjs             # what a first-time visitor downloads: every page inside
                           # its byte budget, no original files served, nothing over
                           # 400 KB, no photograph scaled up to fill its box, the
                           # largest image never lazy, stylesheets compressed.
node car-page.mjs           # the car page: the two offer panels sit under the
                           # specifications, three warranty steps and two maintenance
                           # steps, the ladder is one row from 560px up and one step
                           # per line below it (never a broken 2 + 1), the calculator
                           # and the request form are gone, and the full-screen
                           # viewer's arrows are symmetric at five viewports.
node sold-state.mjs         # a car that is gone: every sold card in the catalogue
                           # opens its page, that page loads, the theme is on <body>
                           # so header and footer go grey too, the photographs keep a
                           # trace of colour rather than going flat — stage, thumbs
                           # AND the enlarged view — no brand hue (green, red, blue,
                           # warn) is painted anywhere, the square "Vendido" stamp is
                           # present at seven widths from 320 to 1600 and always clear
                           # of the phone dock, the enlarged photograph carries its own
                           # mark in ITS corner rather than the overlay's, and a car
                           # still for sale is untouched.
node contact-page.mjs       # the contact page: the photograph is never upscaled, every
                           # section starts on the same spine, the opening line fits its
                           # column, no band of ground opens under the photograph at any
                           # scroll, nothing is left invisible, and the phone reads
                           # statement -> photograph -> ways with the statement clear of
                           # the dock. Replaced contact-landing.mjs and contact-frames.mjs,
                           # which measured a page that was thrown away.
```

Every suite prints ✓/✗ lines and exits 1 on any ✗. The suites map the host themselves
and do not honour `AUDIT_HOST`. All seven `audit.mjs` checks: `contrast overflow targets
ladder nojs measure shot` (`measure` dumps computed styles for a selector; `shot` takes
screenshots). Run `audit.mjs` with both arguments — bare, it runs `contrast /`.

Screenshots go to `tools/audit/out/` (or the dir you pass as the first argument).
Puppeteer resolves from this repo's `node_modules`; Chrome from `~/.cache/puppeteer/chrome`.
The host is mapped to 127.0.0.1 inside each script — never point one at production.
