// tools/audit/suites/sold-state.mjs
// A car that is gone: the catalogue card opens it, the page goes grey, only the
// photographs keep a trace of colour, and a fixed label says so.
// Every assertion here failed at some point while it was being built. Exits 1.
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

// Every literal in mc-tokens.css that carries a hue. If one of these is still
// painted anywhere on a sold page, "tema gri total" is not true.
const BRAND = {
  'rgb(1, 123, 55)':   '--mc-wa',      'rgb(0, 110, 48)':   '--mc-wa-dark',
  'rgb(198, 53, 42)':  '--mc-price',   'rgb(168, 51, 15)':  '--mc-accent',
  'rgb(157, 49, 16)':  '--mc-accent-dark',
  'rgb(21, 88, 214)':  '--mc-blue',    'rgb(9, 62, 125)':   '--mc-blue-dark',
  'rgb(108, 170, 231)':'--mc-blue-light', 'rgb(123, 82, 0)':'--mc-warn',
};

const b = await p.launch({
  executablePath: exe,
  args: ['--no-sandbox', '--ignore-certificate-errors', `--host-resolver-rules=MAP ${HOST} 127.0.0.1`],
});

/* --- the catalogue: a sold card is a door, not a picture ----------------- */
let soldHref = null;
{
  const pg = await b.newPage();
  await pg.setViewport({ width: 1440, height: 900 });
  await pg.goto(`${B}/catalogo`, { waitUntil: 'networkidle0' });
  const r = await pg.evaluate(() => {
    const sold = [...document.querySelectorAll('.mc-card--sold')];
    const links = sold.map(c => c.querySelector('a.mc-card__link')?.getAttribute('href') || null);
    return { n: sold.length, linked: links.filter(Boolean).length, first: links.find(Boolean),
             filter: sold.length ? getComputedStyle(sold[0].querySelector('img')).filter : null };
  });
  soldHref = r.first;
  // It refused to link because partials/card.blade.php only built an href for
  // 'available'. CarPageController::show never filtered on status: the page was
  // always there, nothing pointed at it.
  is(r.n > 0 && r.linked === r.n, 'every sold card in the catalogue opens its page',
     `${r.linked}/${r.n} linked, e.g. ${r.first}`);
  is(r.filter && r.filter !== 'grayscale(1)' && r.filter.startsWith('grayscale('),
     'and the card keeps a trace of colour rather than going fully grey', r.filter);
  await pg.close();
}

/* --- the car page it opens ---------------------------------------------- */
if (!soldHref) { fail('no sold card to follow'); }
else {
  const pg = await b.newPage();
  const errs = [];
  pg.on('pageerror', e => errs.push(String(e)));
  await pg.setViewport({ width: 1440, height: 900 });
  const resp = await pg.goto(B + soldHref, { waitUntil: 'networkidle0' });
  is(resp.status() === 200, 'the page behind a sold card actually loads', `HTTP ${resp.status()}`);
  await new Promise(r => setTimeout(r, 800));

  const page = await pg.evaluate(() => {
    const i = document.getElementById('stage');
    return {
      onBody: document.body.className.includes('car--sold'),
      gone: !!document.querySelector('.car-gone'),
      stage: getComputedStyle(i).filter,
      thumb: (() => { const t = document.querySelector('.car-thumb img');
                      return t ? getComputedStyle(t).filter : '(none)'; })(),
      stageLoaded: i.complete && i.naturalWidth > 0,
    };
  });
  is(page.onBody, 'the sold theme is on <body>, so the header and footer go grey too');
  is(page.gone, 'and the state is in the reading order for everyone, not only in the label');
  is(page.stage === page.thumb && page.stage.startsWith('grayscale('),
     'the photographs carry the filter, stage and thumbnails alike', `${page.stage} / ${page.thumb}`);
  is(page.stageLoaded, 'and the main photograph actually decodes');

  // The green "-500 €" survived the first pass because --mc-ok and --mc-warn are
  // their own literals in mc-tokens.css, not aliases of --mc-wa.
  const leaks = await pg.evaluate((BRAND) => {
    const out = [];
    for (const el of document.querySelectorAll('*')) {
      const s = getComputedStyle(el);
      for (const prop of ['color', 'backgroundColor', 'borderTopColor', 'borderLeftColor']) {
        const v = s[prop];
        if (BRAND[v]) out.push(`${el.tagName.toLowerCase()}.${(el.className || '').toString().split(' ')[0]} ${prop}=${BRAND[v]}`);
      }
    }
    return [...new Set(out)];
  }, BRAND);
  is(leaks.length === 0, 'no brand hue is painted anywhere on the page',
     leaks.length ? leaks.slice(0, 4).join(' | ') : 'checked green, red, blue and warn');

  // The tab is fixed to the viewport edge, so the only thing that can go wrong
  // is it sitting on the words.
  for (const [w, shown] of [[1440, true], [1600, true], [1280, false], [390, false]]) {
    await pg.setViewport({ width: w, height: 900 });
    await new Promise(r => setTimeout(r, 250));
    const t = await pg.evaluate(() => {
      const tab = document.querySelector('.car-tab-sold');
      const cs = getComputedStyle(tab);
      if (cs.display === 'none') return { shown: false };
      const r = tab.getBoundingClientRect();
      const main = document.querySelector('main.car');
      const m = main.getBoundingClientRect(), ms = getComputedStyle(main);
      const contentRight = m.right - parseFloat(ms.paddingRight);
      return { shown: true, gap: Math.round(r.left - contentRight), text: tab.textContent.trim() };
    });
    is(t.shown === shown, `the label ${shown ? 'is shown' : 'is withheld'} at ${w}px`,
       t.shown ? `"${t.text}", ${t.gap}px clear of the text` : 'display:none');
    if (t.shown) is(t.gap > 0, `and at ${w}px it does not sit on the words`, `${t.gap}px`);
  }
  is(errs.length === 0, 'no script errors', errs.slice(0, 2).join(' | '));
  await pg.close();
}

/* --- and a car that is still for sale is untouched ----------------------- */
{
  const pg = await b.newPage();
  await pg.setViewport({ width: 1440, height: 900 });
  await pg.goto(`${B}/catalogo`, { waitUntil: 'networkidle0' });
  const href = await pg.evaluate(() =>
    [...document.querySelectorAll('.mc-card:not(.mc-card--sold):not(.mc-card--demo) a.mc-card__link')][0]?.getAttribute('href'));
  if (!href) { fail('no available car to compare against'); }
  else {
    await pg.goto(B + href, { waitUntil: 'networkidle0' });
    await new Promise(r => setTimeout(r, 600));
    const a = await pg.evaluate(() => ({
      sold: document.body.className.includes('car--sold'),
      filter: getComputedStyle(document.getElementById('stage')).filter,
      cta: getComputedStyle(document.querySelector('.mc-head .mc-btn--cta')).backgroundColor,
      tab: !!document.querySelector('.car-tab-sold'),
    }));
    is(!a.sold && a.filter === 'none' && !a.tab && a.cta === 'rgb(1, 123, 55)',
       'a car that is still for sale keeps its colour, its green and no label',
       `filter=${a.filter} cta=${a.cta} tab=${a.tab}`);
  }
  await pg.close();
}

await b.close();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
