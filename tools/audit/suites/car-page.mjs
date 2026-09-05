// tools/audit/suites/car-page.mjs
// The car page: the two offer panels, and the full-screen viewer's controls.
// Both assertions below encode a defect that shipped. Exits 1 on failure.
import p from 'puppeteer';
import { readdirSync } from 'fs';

const HOST = process.env.AUDIT_HOST || 'v2design.ivmotorclass.com';
const B    = `https://${HOST}`;
const dir  = `${process.env.HOME}/.cache/puppeteer/chrome`;
const exe  = `${dir}/${readdirSync(dir).filter(d => d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;

let bad = 0;
const ok   = (t, d) => console.log(`✓ ${t}${d ? '  — ' + d : ''}`);
const fail = (t, d) => { bad++; console.log(`✗ ${t}${d ? '  — ' + d : ''}`); };
const is   = (c, t, d) => (c ? ok : fail)(t, d);

const b = await p.launch({
  executablePath: exe,
  args: ['--no-sandbox', '--ignore-certificate-errors', `--host-resolver-rules=MAP ${HOST} 127.0.0.1`],
});

const pg = await b.newPage();
const errs = [];
pg.on('pageerror', e => errs.push(String(e)));
pg.on('console', m => { if (m.type() === 'error') errs.push(m.text()); });
await pg.setViewport({ width: 1440, height: 900 });
await pg.goto(`${B}/catalogo`, { waitUntil: 'networkidle0' });
const href = await pg.evaluate(() =>
  [...document.querySelectorAll('.mc-card:not(.mc-card--sold):not(.mc-card--demo) a.mc-card__link')][0]?.getAttribute('href'));
await pg.goto(B + href, { waitUntil: 'networkidle0' });
await new Promise(r => setTimeout(r, 700));

/* --- what goes with the car --------------------------------------------- */
const col = await pg.evaluate(() => {
  const side = document.querySelector('.car-side');
  const wit = document.querySelector('.car-with');
  if (!side || !wit) return { there: false };
  return { there: true,
           inColumn: side.contains(wit),
           order: [...side.children].map(c => c.className.split(' ')[0]),
           specsCopies: document.querySelectorAll('.car-specs').length,
           panels: document.querySelectorAll('.car-off').length,
           steps: [...document.querySelectorAll('.car-off')].map(o => o.querySelectorAll('.car-off__step').length),
           included: document.querySelectorAll('.car-off__step--inc').length };
});
is(col.there && col.inColumn,
   'everything that comes with the car sits in the right-hand column');
is(col.order.join(' ') === 'car-price mc-specs car-act car-with',
   'under the price, the facts and the buttons, in that order', col.order.join(' → '));
is(col.specsCopies === 1, 'the six facts are rendered exactly once', `${col.specsCopies} copies`);
is(col.panels === 2 && col.steps.join(',') === '3,2',
   'two panels: three warranty steps and two maintenance steps', JSON.stringify(col.steps));
is(col.included === 1, 'exactly one step is marked as included');

// The calculator and the "request a viewing / an offer" block were deleted.
const gone = await pg.evaluate(() => ({
  calc: !!document.querySelector('.car-calc, .car-ask, #calc-sum'),
  form: !!document.querySelector('#car-form, .car-form'),
}));
is(!gone.calc && !gone.form, 'the calculator and the request form are gone',
   `calc:${gone.calc} form:${gone.form}`);

// The panel is 336px in the sidebar and never wider than 410 anywhere, so the
// ladder is lines, not cells. What must never happen is a half-broken grid — two
// on one row and one orphaned under them.
for (const w of [1800, 1440, 1100, 1000, 900, 768, 600, 390, 320]) {
  await pg.setViewport({ width: w, height: 1100 });
  await new Promise(r => setTimeout(r, 300));
  const l = await pg.evaluate(() => {
    const cells = document.querySelector('.car-off').querySelectorAll('.car-off__step');
    const rows = new Set([...cells].map(c => Math.round(c.getBoundingClientRect().top))).size;
    return { rows, n: cells.length,
             panel: Math.round(document.querySelector('.car-off').getBoundingClientRect().width),
             overflow: Math.max(0, document.documentElement.scrollWidth - window.innerWidth) };
  });
  is(l.rows === 1 || l.rows === l.n,
     `the ladder is whole at ${w}px — all across or one per line, never 2 + 1`,
     `${l.rows} rows of ${l.n}, panel ${l.panel}px`);
  is(l.overflow === 0, `and nothing overflows at ${w}px`, `${l.overflow}px`);
}
await pg.setViewport({ width: 1440, height: 900 });
await new Promise(r => setTimeout(r, 300));

/* --- the full-screen viewer's arrows ------------------------------------
   They are absolutely positioned children of a grid container. With a definite
   grid-column they are laid out against their GRID AREA, not the container's
   padding box — and on the phone's one-column template, column 3 is an implicit
   line, so `right:22%` resolved against a zero-width strip. The next button sat
   3px from the screen edge while prev sat at 95px. */
for (const [w, h] of [[390, 844], [430, 932], [360, 780], [844, 390], [1440, 900]]) {
  await pg.setViewport({ width: w, height: h, isMobile: w < 560, hasTouch: w < 560 });
  await new Promise(r => setTimeout(r, 300));
  const a = await pg.evaluate(async () => {
    const v = document.getElementById('view');
    if (v.hidden) { document.getElementById('stage').click(); await new Promise(r => setTimeout(r, 600)); }
    const R = s => document.querySelector(s).getBoundingClientRect();
    const p = R('.car-view__nav--prev'), n = R('.car-view__nav--next');
    return { left: Math.round(p.left), right: Math.round(innerWidth - n.right),
             sameRow: Math.round(p.top) === Math.round(n.top),
             gap: Math.round(n.left - p.right) };
  });
  is(a.left === a.right, `the viewer arrows are symmetric at ${w}x${h}`,
     `left ${a.left}, right ${a.right}`);
  is(a.sameRow && a.gap > 0, `and level, without overlapping, at ${w}x${h}`, `gap ${a.gap}px`);
}

is(errs.length === 0, 'no script errors', errs.slice(0, 2).join(' | '));
await pg.close();
await b.close();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
