// tools/audit/suites/header.mjs
// The one header every page shares. Five links have to fit at every width without
// the header growing: it was 148px tall at 320-430 with the links wrapped onto
// two rows, and a fifth link put them on three. Exits 1.
import p from 'puppeteer';
import { readdirSync } from 'fs';

const HOST = process.env.AUDIT_HOST || 'v2design.ivmotorclass.com';
const dir  = `${process.env.HOME}/.cache/puppeteer/chrome`;
const exe  = `${dir}/${readdirSync(dir).filter(d => d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;

let bad = 0;
const ok   = (t, d) => console.log(`✓ ${t}${d ? '  — ' + d : ''}`);
const fail = (t, d) => { bad++; console.log(`✗ ${t}${d ? '  — ' + d : ''}`); };
const is   = (c, t, d) => (c ? ok : fail)(t, d);

const b = await p.launch({ executablePath: exe,
  args: ['--no-sandbox', '--ignore-certificate-errors', `--host-resolver-rules=MAP ${HOST} 127.0.0.1`] });

const LINKS = ['Inicio', 'Coches', 'Quién soy', 'Contacto', 'Vende tu coche'];

for (const route of ['/inicio', '/catalogo', '/contacto', '/vende']) {
  const pg = await b.newPage();
  await pg.setViewport({ width: 1440, height: 900 });
  await pg.goto(`https://${HOST}${route}`, { waitUntil: 'domcontentloaded' });
  const names = await pg.evaluate(() => [...document.querySelectorAll('.mc-nav__i')].map(a => a.textContent.trim()));
  is(JSON.stringify(names) === JSON.stringify(LINKS), `${route} carries all five links`, names.join(' · '));
  const cur = await pg.evaluate(() => document.querySelector('.mc-nav__i.is-current')?.textContent.trim() || '(none)');
  is(cur !== '(none)', `and marks where you are`, cur);
  await pg.close();
}

// height is a function of width and nothing else
const pg = await b.newPage();
await pg.goto(`https://${HOST}/inicio`, { waitUntil: 'domcontentloaded' });
for (const [w, want] of [[320, 100], [360, 100], [390, 100], [430, 100], [600, 100], [768, 100], [900, 72], [1024, 72], [1440, 72]]) {
  await pg.setViewport({ width: w, height: 844, isMobile: w < 600, hasTouch: w < 600 });
  await new Promise(r => setTimeout(r, 250));
  const m = await pg.evaluate(() => {
    const h = Math.round(document.querySelector('.mc-head').getBoundingClientRect().height);
    const items = [...document.querySelectorAll('.mc-nav__i')];
    const rows = new Set(items.map(i => Math.round(i.getBoundingClientRect().top))).size;
    const nav = document.querySelector('.mc-nav');
    const last = items[items.length - 1];
    nav.scrollLeft = nav.scrollWidth;
    const reach = last.getBoundingClientRect().right <= innerWidth + 1;
    nav.scrollLeft = 0;
    return { h, rows, reach, overflow: Math.max(0, document.documentElement.scrollWidth - innerWidth) };
  });
  is(m.h === want, `header is ${want}px at ${w}`, `${m.h}px`);
  is(m.rows === 1, `and the links sit on one row at ${w}`, `${m.rows} row(s)`);
  is(m.reach && m.overflow === 0, `and the last link is reachable without the page overflowing at ${w}`,
     `reachable:${m.reach} overflow:${m.overflow}`);
}
await pg.close();
await b.close();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
