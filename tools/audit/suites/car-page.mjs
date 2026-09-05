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
const where = await pg.evaluate(() => {
  const sec = document.querySelector('.car-with');
  if (!sec) return { there: false };
  const prev = sec.previousElementSibling, next = sec.nextElementSibling;
  return { there: true,
           after: prev?.querySelector('h2')?.textContent.trim() || null,
           before: next?.querySelector('h2')?.textContent.trim() || null,
           panels: document.querySelectorAll('.car-off').length,
           steps: [...document.querySelectorAll('.car-off')].map(o => o.querySelectorAll('.car-off__step').length),
           included: document.querySelectorAll('.car-off__step--inc').length };
});
is(where.there, 'the offers section is on the page');
is(where.after === 'Especificaciones técnicas', 'and it sits under the specifications', `after "${where.after}"`);
is(where.panels === 2 && where.steps.join(',') === '3,2',
   'two panels: three warranty steps and two maintenance steps', JSON.stringify(where.steps));
is(where.included === 1, 'exactly one step is marked as included');

// The calculator and the "request a viewing / an offer" block were deleted.
const gone = await pg.evaluate(() => ({
  calc: !!document.querySelector('.car-calc, .car-ask, #calc-sum'),
  form: !!document.querySelector('#car-form, .car-form'),
}));
is(!gone.calc && !gone.form, 'the calculator and the request form are gone',
   `calc:${gone.calc} form:${gone.form}`);

// A ladder that wraps into 2 + 1 reads as a mistake. Below 560 it becomes a
// column on purpose; above it, the three steps share one row.
for (const w of [1440, 1200, 1000, 768, 560]) {
  await pg.setViewport({ width: w, height: 1000 });
  await new Promise(r => setTimeout(r, 300));
  const rows = await pg.evaluate(() => {
    const cells = document.querySelector('.car-off').querySelectorAll('.car-off__step');
    return new Set([...cells].map(c => Math.round(c.getBoundingClientRect().top))).size;
  });
  is(rows === 1, `the warranty ladder is one row at ${w}px`, `${rows} row(s)`);
}
for (const w of [480, 390, 320]) {
  await pg.setViewport({ width: w, height: 1000 });
  await new Promise(r => setTimeout(r, 300));
  const r = await pg.evaluate(() => {
    const cells = document.querySelector('.car-off').querySelectorAll('.car-off__step');
    return { rows: new Set([...cells].map(c => Math.round(c.getBoundingClientRect().top))).size,
             n: cells.length };
  });
  is(r.rows === r.n, `and one step per line at ${w}px, not a broken grid`, `${r.rows} of ${r.n}`);
}

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
