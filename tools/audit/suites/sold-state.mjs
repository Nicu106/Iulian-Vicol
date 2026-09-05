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
      viewer: getComputedStyle(document.getElementById('view-img')).filter,
      stageLoaded: i.complete && i.naturalWidth > 0,
    };
  });
  is(page.onBody, 'the sold theme is on <body>, so the header and footer go grey too');
  is(page.gone, 'and the state is in the reading order for everyone, not only in the label');
  // The viewer was missing from this list at first: a grey thumbnail opened a
  // full-colour photograph, the same picture in two states one tap apart.
  is(page.stage === page.thumb && page.thumb === page.viewer && page.stage.startsWith('grayscale('),
     'every photograph carries the filter — stage, thumbnails and the enlarged view',
     `${page.stage} / ${page.thumb} / ${page.viewer}`);
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

  // The square stamp holds the wide screens. On a phone it gives way to the dock,
  // which carries the word instead — two of them inside 120px of screen would be
  // the same statement twice.
  for (const w of [1600, 1440, 1280, 1024]) {
    await pg.setViewport({ width: w, height: 844 });
    await new Promise(r => setTimeout(r, 300));
    const t = await pg.evaluate(() => {
      const tab = document.querySelector('.car-tab-sold');
      const cs = getComputedStyle(tab), r = tab.getBoundingClientRect();
      return { shown: cs.display !== 'none', w: Math.round(r.width), h: Math.round(r.height),
               radius: cs.borderRadius, left: Math.round(r.left), text: tab.textContent.trim() };
    });
    is(t.shown && t.text === 'Vendido' && t.w === t.h && parseFloat(t.radius) === 0 && t.left < 40,
       `the square stamp is present, square and on the left at ${w}px`,
       `${t.w}x${t.h}, radius ${t.radius}, left ${t.left}`);
  }
  for (const w of [768, 390, 320]) {
    await pg.setViewport({ width: w, height: 844 });
    await new Promise(r => setTimeout(r, 300));
    const d = await pg.evaluate(() => {
      const tab = document.querySelector('.car-tab-sold');
      const dock = document.querySelector('.cat-dock');
      const sold = document.querySelector('.cat-dock__sold');
      return { stamp: getComputedStyle(tab).display,
               dock: dock ? getComputedStyle(dock).display : '(absent)',
               says: sold ? sold.textContent.trim() : null,
               price: !!document.querySelector('.cat-dock .mc-bar__price'),
               buttons: document.querySelectorAll('.cat-dock .mc-btn').length };
    });
    is(d.stamp === 'none', `the stamp gives way to the dock at ${w}px`, d.stamp);
    is(d.dock === 'block' && d.says === 'Vendido',
       `and the dock says it instead at ${w}px`, `"${d.says}"`);
    is(!d.price && d.buttons === 0,
       `with no price and no buttons — nothing here is for sale at ${w}px`,
       `price:${d.price} buttons:${d.buttons}`);
  }
  await pg.setViewport({ width: 1440, height: 900 });

  // "In coltul imaginii" — of the image, not of the overlay. The viewer centres
  // the photograph, so a mark pinned to the overlay would float in the black
  // beside a portrait shot.
  const mark = await pg.evaluate(async () => {
    document.getElementById('stage').click();
    await new Promise(r => setTimeout(r, 900));
    const img = document.getElementById('view-img');
    const m = document.querySelector('.car-view__sold');
    if (!m) return { there: false };
    const i = img.getBoundingClientRect(), r = m.getBoundingClientRect();
    return { there: true, open: !document.getElementById('view').hidden,
             dx: Math.round(r.left - i.left), dy: Math.round(r.top - i.top),
             inside: r.right <= i.right + 1 && r.bottom <= i.bottom + 1,
             text: m.textContent.trim(),
             imgBox: Math.round(i.width) + 'x' + Math.round(i.height) };
  });
  is(mark.there && mark.open, 'the enlarged view opens and carries its own mark');
  is(mark.dx === 0 && mark.dy === 0 && mark.inside,
     'and the mark sits in the corner of the photograph, not of the screen',
     `offset ${mark.dx},${mark.dy} inside ${mark.imgBox}`);
  is(mark.text === 'Vendido', 'and it says so', `"${mark.text}"`);
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
