// tools/audit/suites/car-page.mjs
// The car page: the two offer panels, and the full-screen viewer's controls.
// Both assertions below encode a defect that shipped. Exits 1 on failure.
import p from 'puppeteer';
import { readdirSync } from 'fs';

const HOST = process.env.AUDIT_HOST || 'ivmotorclass.com';
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
  [...document.querySelectorAll('.mc-card:not(.mc-card--sold):not(.mc-card--soon) a.mc-card__link')][0]?.getAttribute('href'));
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
           panels: document.querySelectorAll('.car-with .car-off').length,
           steps: [...document.querySelectorAll('.car-with .car-off')].map(o => o.querySelectorAll('.car-off__step').length),
           included: document.querySelectorAll('.car-off__step--inc').length };
});
is(col.there && col.inColumn,
   'everything that comes with the car sits in the right-hand column');
// + "Compartir", 2026-10-06; + the instalment under the price and the calculator before the panels, 2026-11-04
is(col.order.join(' ') === 'car-price car-fin-hint mc-specs car-act car-share car-fin car-with',
   'under the price, the facts and the buttons, in that order', col.order.join(' → '));
is(col.specsCopies === 1, 'the six facts are rendered exactly once', `${col.specsCopies} copies`);
is(col.panels === 2 && col.steps.join(',') === '3,2',
   'two panels: three warranty steps and two maintenance steps', JSON.stringify(col.steps));
is(col.included === 1, 'exactly one step is marked as included');

// The old calculator and the "request a viewing / an offer" block were deleted
// (2026-09-05). The instalment calculator of 2026-11-04 is .car-fin, checked below.
const gone = await pg.evaluate(() => ({
  calc: !!document.querySelector('.car-calc, .car-ask, #calc-sum'),
  form: !!document.querySelector('#car-form, .car-form'),
}));
is(!gone.calc && !gone.form, 'the calculator and the request form are gone',
   `calc:${gone.calc} form:${gone.form}`);

/* --- the instalment calculator (App\Support\Finance), 2026-11-04 ----------- */
const fin = async () => pg.evaluate(() => {
  const on = [...document.querySelectorAll('.car-fin__out')].filter(o => getComputedStyle(o).display !== 'none');
  return { shown: on.map(o => +o.dataset.n), q: on.map(o => o.querySelector('[data-q]').textContent),
           checked: [...document.querySelectorAll('input[name="fin-n"]:checked')].map(r => +r.value),
           terms: [...document.querySelectorAll('.car-fin__t')].map(l => l.textContent.trim()),
           down: !document.querySelector('.car-fin__down').hidden,
           wa: decodeURIComponent((document.querySelector('.car-fin__go').href.split('text=')[1] || '').replace(/\+/g, ' ')),
           hint: document.querySelector('.car-fin-hint b')?.textContent };
});
let f = await fin();
is(f.terms.join('|') === '6 meses|1 año|2 años|3 años|4 años|5 años', 'six terms, from six months to five years', f.terms.join(' · '));
is(f.checked.join() === '60' && f.shown.join() === '60', 'five years is chosen when the page opens, and only its figures show', JSON.stringify(f));
is(f.hint === f.q[0], 'the line under the price quotes the same instalment', `${f.hint} / ${f.q[0]}`);
is(f.down, 'with script, the deposit line is there');
await (await pg.$$('.car-fin__t'))[3].click();
f = await fin();
is(f.checked.join() === '36' && f.shown.join() === '36' && / 3 años, sin entrada, /.test(f.wa), 'pressing 3 años shows its figures and the WhatsApp message says so', f.wa);
await pg.type('#fin-down', '5000');
const f2 = await fin();
is(f2.q[0] !== f.q[0] && / con 5\.000 € de entrada, /.test(f2.wa), 'a deposit lowers the instalment and goes into the message', `${f.q[0]} → ${f2.q[0]}`);
// the slider sets the same deposit, and leaves the term alone
await pg.$eval('#fin-down', e => { e.value = ''; e.dispatchEvent(new Event('input', { bubbles: true })); });
await pg.$eval('#fin-range', e => { e.value = 5000; e.dispatchEvent(new Event('input', { bubbles: true })); });
const f3 = await fin();
const field = await pg.$eval('#fin-down', e => e.value);
is(f3.q[0] === f2.q[0] && field === '5.000' && f3.checked.join() === '36', 'the slider gives the same instalment, writes 5.000 in the field and keeps the term', `${f3.q[0]}, "${field}", ${f3.checked}`);

// The sidebar spans every row of the left column instead of sitting in row 1. A
// grid item sizes its row, so with it in row 1 the gallery's row was as tall as
// the panels beside it and the description began below all of that — ~790px of
// empty page under the thumbnails. The invariant is that nothing in the left
// column is separated from the block above it by more than the grid's own gap.
// Since 2026-11-04 that gap is the blocks' margin (--s-8), not row-gap: the
// column spans one more, flexible row that takes its surplus (car.css).
for (const w of [1440, 1200, 1000]) {
  await pg.setViewport({ width: w, height: 1000 });
  await new Promise(r => setTimeout(r, 350));
  const g = await pg.evaluate(() => {
    const grid = document.querySelector('.car-grid');
    const s8 = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--s-8')) * 16;
    const gap = Math.max(parseFloat(getComputedStyle(grid).rowGap) || 0, s8);
    const left = [...grid.children].filter(c => !c.classList.contains('car-side'))
      .map(c => c.getBoundingClientRect());
    let worst = 0, at = '';
    for (let i = 1; i < left.length; i++) {
      const d = Math.round(left[i].top - left[i - 1].bottom);
      if (d > worst) { worst = d; at = i; }
    }
    const side = document.querySelector('.car-side').getBoundingClientRect();
    const leftH = left.length ? Math.round(left[left.length - 1].bottom - left[0].top) : 0;
    return { blocks: left.length, gap: Math.round(gap), worst, at,
             leftH, rightH: Math.round(side.height) };
  });
  is(g.worst <= g.gap + 2,
     `no hole opens in the left column at ${w}px`,
     `worst gap ${g.worst}px against a ${g.gap}px row gap, ${g.blocks} blocks`);
  is(Math.abs(g.leftH - g.rightH) < Math.max(g.leftH, g.rightH),
     `and the two columns are the same order of length at ${w}px`,
     `left ${g.leftH}, right ${g.rightH}`);
}

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

/* --- pressing a thumbnail actually changes the photograph -----------------
   srcset beats src. Once the stage carried a srcset, setting only .src changed
   the attribute and left the picture alone — the page looked like it was ignoring
   the thumbnails. currentSrc is the only thing that says what is on screen. */
await pg.setViewport({ width: 1440, height: 900 });
await pg.goto(B + href, { waitUntil: 'networkidle0' });
await new Promise(r => setTimeout(r, 700));
{
  const swap = await pg.evaluate(async () => {
    const stage = document.getElementById('stage');
    const before = stage.currentSrc;
    const thumbs = document.querySelectorAll('.car-thumb');
    if (thumbs.length < 4) return { skip: true };
    thumbs[3].click();
    // decode-before-show means the change lands a beat after the click
    for (let i = 0; i < 60 && stage.currentSrc === before; i++) {
      await new Promise(r => setTimeout(r, 100));
    }
    return { before: before.split('/').pop(), after: stage.currentSrc.split('/').pop(),
             marked: thumbs[3].classList.contains('is-on'),
             hasSet: !!stage.getAttribute('srcset') };
  });
  is(swap.skip || swap.before !== swap.after,
     'pressing a thumbnail changes the photograph on the stage',
     swap.skip ? 'too few photos' : `${swap.before.slice(0, 18)} → ${swap.after.slice(0, 18)}`);
  is(swap.skip || swap.marked, 'and the thumbnail is marked as the one being shown');
  is(swap.skip || swap.hasSet, 'and the stage keeps a srcset after the swap, not a bare src');
}

/* --- the full-screen viewer ---------------------------------------------
   Phones (≤768px, since 2026-10-05): no arrows — a swipe strip with dots; the
   counter follows the photograph on screen. Larger screens keep the arrows,
   symmetric and level. */
for (const [w, h] of [[390, 844], [360, 780], [1440, 900]]) {
  await pg.setViewport({ width: w, height: h, isMobile: w < 560, hasTouch: w < 560 });
  await pg.reload({ waitUntil: 'networkidle0' });
  const a = await pg.evaluate(async () => {
    const t = document.querySelector('.car-track');
    (t || document.getElementById('stage')).click(); await new Promise(r => setTimeout(r, 700));
    const vis = s => [...document.querySelectorAll(s)].filter(e => getComputedStyle(e).display !== 'none' && e.getBoundingClientRect().width > 0).length;
    const out = { arrows: vis('.car-view__nav'), track: !!document.querySelector('.car-view__track'), dots: vis('.car-dots--view i') };
    if (out.track) { const vt = document.querySelector('.car-view__track'); vt.scrollLeft = vt.clientWidth * 2; await new Promise(r => setTimeout(r, 400));
      out.count = document.getElementById('view-count').textContent.trim(); }
    else { const R = s => document.querySelector(s).getBoundingClientRect(); const p = R('.car-view__nav--prev'), n = R('.car-view__nav--next');
      out.sameRow = Math.round(p.top) === Math.round(n.top); out.gap = Math.round(n.left - p.right); }
    document.getElementById('view-x').click(); return out;
  });
  if (w <= 768) {
    is(a.arrows === 0 && a.track && a.dots > 1, `phone ${w}: full screen is a swipe strip with dots, no arrows`, JSON.stringify(a));
    is(/^3 \//.test(a.count || ''), `and its counter follows the photograph on screen`, a.count);
  } else {
    is(a.arrows === 2 && a.sameRow && a.gap > 0, `desk ${w}: the viewer keeps two level arrows`, JSON.stringify(a));
  }
}

is(errs.length === 0, 'no script errors', errs.slice(0, 2).join(' | '));
await pg.close();
await b.close();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
