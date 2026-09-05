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
node reviews-photos.mjs    # 0 photographs cut, 0 matted, 0 text overflowing at 1440/768/390
node reviews-widths.mjs    # every card renders at exactly its computed width (the Safari flex-shrink guard)
node reviews-seam.mjs      # the loop seam is exactly one row: 0px error, every pair one stride apart
node reviews-speed.mjs     # px/s delivered vs the constant (scrollLeft rounds — this caught 1px/frame)
node contact-landing.mjs   # the strips land on both contact panels at 0px on all four edges
node contact-frames.mjs    # share of the contact scroll with no photograph on screen (was 56%, must be ~0)
```

Screenshots go to `tools/audit/out/` (or the dir you pass as the first argument).
Puppeteer resolves from this repo's `node_modules`; Chrome from `~/.cache/puppeteer/chrome`.
The host is mapped to 127.0.0.1 inside each script — never point one at production.
