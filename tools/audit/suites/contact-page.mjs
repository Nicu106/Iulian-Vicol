// tools/audit/suites/contact-page.mjs
// Replaces contact-landing.mjs and contact-frames.mjs, which measured a version of
// this page that was thrown away. Every assertion here failed at some point during
// the rebuild; none of them is hypothetical. Exits 1 on any failure.
import p from 'puppeteer';
import { readdirSync } from 'fs';

const HOST = process.env.AUDIT_HOST || 'v2design.ivmotorclass.com';
const URLP = `https://${HOST}/contacto`;
const dir  = `${process.env.HOME}/.cache/puppeteer/chrome`;
const exe  = `${dir}/${readdirSync(dir).filter(d => d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;

let bad = 0;
const ok   = (t, d) => console.log(`✓ ${t}${d ? '  — ' + d : ''}`);
const fail = (t, d) => { bad++; console.log(`✗ ${t}${d ? '  — ' + d : ''}`); };
const is   = (c, t, d) => (c ? ok : fail)(t, d);

const b = await p.launch({
  executablePath: exe,
  args: ['--no-sandbox', '--ignore-certificate-errors',
         `--host-resolver-rules=MAP ${HOST} 127.0.0.1`],
});

/* ---------------------------------------------------------------- desktop */
{
  const pg = await b.newPage();
  const errs = [];
  pg.on('pageerror', e => errs.push(String(e)));
  pg.on('console', m => { if (m.type() === 'error') errs.push(m.text()); });
  await pg.setViewport({ width: 1440, height: 900 });
  await pg.goto(URLP, { waitUntil: 'networkidle0' });
  await new Promise(r => setTimeout(r, 700));

  // The whole reason the second version was thrown away. A 2400px file drawn into
  // a box wider than 2400 is an upscale, and an upscaled photograph is the single
  // thing that reads as cheap however good the type is. The first version opened
  // on one at scale(2.6).
  // naturalWidth is NOT the file's width once a w-descriptor srcset is in play:
  // the browser divides the intrinsic size by the candidate's density, so it
  // always comes back equal to the slot and the comparison is a tautology.
  // Fetch the chosen file and read its real header instead.
  const pic = await pg.evaluate(async () => {
    const i = document.getElementById('hero-img');
    const r = i.getBoundingClientRect();
    const bmp = await createImageBitmap(await (await fetch(i.currentSrc)).blob());
    const real = [bmp.width, bmp.height];
    bmp.close();
    return { real, box: [r.width, r.height], src: i.currentSrc.split('/').pop(),
             transform: getComputedStyle(i).transform };
  });
  // object-fit:cover scales by whichever edge is short, so both have to fit.
  const scale = Math.max(pic.box[0] / pic.real[0], pic.box[1] / pic.real[1]);
  is(scale <= 1.001,
     'the photograph is never drawn larger than the file it came from',
     `box ${Math.round(pic.box[0])}x${Math.round(pic.box[1])} from ${pic.real.join('x')} — cover scale ${scale.toFixed(2)}x`);
  is(pic.transform === 'none',
     'and it carries no transform, so nothing is resampled', pic.transform);

  // A 32px error here shipped once: the grid's main track was the full container
  // while every .cat-wrap section under it started a gutter further in. A 16px
  // error shipped too: the grid's gutter plus a padding-inline of its own.
  // The heading's own left edge, not the section's. A section can be full-bleed
  // with its content in a wrapper — .ct-far became a navy band and started
  // reporting 0 while the words inside it had not moved a pixel. What "one spine"
  // means is that the text lines up, so measure the text.
  const spine = await pg.evaluate(() => {
    const edge = (sel) => {
      const e = document.querySelector(sel);
      return e ? Math.round(e.getBoundingClientRect().left) : null;
    };
    return { open: edge('.ct-open__h'), ways: edge('.ct-band--wa .ct-band__k'),
             where: edge('.ct-where__say .ct-h2'), far: edge('.ct-far .ct-h2') };
  });
  const xs = Object.values(spine).filter(v => v !== null);
  is(new Set(xs).size === 1, 'every section starts on the same vertical spine',
     JSON.stringify(spine));

  // The form is not on the spine on purpose: it is the right-hand column of the
  // row it shares with the caption — 5 | 7, the mirror of the opening's 7 | 5.
  // As two separate sections each was a left block with its right half empty.
  // Below 900 they stack, full width. That also encodes a bug that shipped: the
  // caption was `span 7` at every width, which on a 390px phone is 209px.
  for (const [w, side] of [[1440, true], [1100, true], [900, true], [899, false], [390, false]]) {
    await pg.setViewport({ width: w, height: 900 });
    await new Promise(r => setTimeout(r, 300));
    const row = await pg.evaluate(() => {
      const a = document.querySelector('.ct-where__say').getBoundingClientRect();
      const b = document.querySelector('.ct-write').getBoundingClientRect();
      const main = document.querySelector('.ct-far .cat-wrap');
      const mw = main.getBoundingClientRect().width - parseFloat(getComputedStyle(main).paddingLeft) - parseFloat(getComputedStyle(main).paddingRight);
      return { sideBySide: Math.abs(a.top - b.top) < 2 && b.left >= a.right - 1,
               sayW: Math.round(a.width), writeW: Math.round(b.width), mainW: Math.round(mw) };
    });
    is(row.sideBySide === side,
       `caption and form are ${side ? 'side by side' : 'stacked'} at ${w}px`,
       `say ${row.sayW}, form ${row.writeW}`);
    if (!side) is(row.sayW >= row.mainW - 1,
       `and the caption takes the whole width at ${w}px`, `${row.sayW} of ${row.mainW}`);
  }
  await pg.setViewport({ width: 1440, height: 900 });
  await new Promise(r => setTimeout(r, 300));

  // The opening line is set from the measure it has to fit, not from taste.
  const line = await pg.evaluate(() => {
    const e = document.querySelector('.ct-open__h');
    const col = e.parentElement;
    const cs = getComputedStyle(col);
    const avail = col.getBoundingClientRect().width - parseFloat(cs.paddingLeft) - parseFloat(cs.paddingRight);
    let w = 0;
    e.childNodes.forEach(n => {
      if (n.nodeType !== 3) return;
      const rg = document.createRange(); rg.selectNodeContents(n);
      for (const r of rg.getClientRects()) w = Math.max(w, r.width);
    });
    return { widest: Math.round(w), avail: Math.round(avail), size: parseFloat(getComputedStyle(e).fontSize) };
  });
  is(line.widest <= line.avail,
     'the opening line fits its column without wrapping',
     `${line.widest} of ${line.avail} at ${line.size}px`);

  // The photograph must fill its column exactly. As a one-screen sticky panel it
  // left a 43px band of page ground between its bottom edge and the top of the
  // map from scroll ~180 onward — invisible in a screenshot of the landing, and
  // the reason this is checked at four scroll positions rather than one.
  const seam = await pg.evaluate(async () => {
    const out = [];
    for (const y of [0, 250, 450, 700]) {
      window.scrollTo(0, y);
      await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));
      const hold = document.querySelector('.ct-open__hold').getBoundingClientRect();
      const pic  = document.querySelector('.ct-open__pic').getBoundingClientRect();
      const map  = document.querySelector('.ct-bands').getBoundingClientRect();   // what follows the opening
      out.push({ y, underPhoto: Math.round(pic.bottom - hold.bottom),
                 picToMap: Math.round(map.top - pic.bottom) });
    }
    window.scrollTo(0, 0);
    return out;
  });
  const worst = Math.max(...seam.map(s => Math.abs(s.underPhoto)), ...seam.map(s => Math.abs(s.picToMap)));
  is(worst <= 1, 'no band of ground opens under the photograph at any scroll',
     seam.map(s => `${s.y}:${s.underPhoto}/${s.picToMap}`).join(' '));

  // The arrival is armed by script and must never leave anything unreadable.
  const unseen = await pg.evaluate(async () => {
    const H = document.documentElement.scrollHeight;
    for (let y = 0; y <= H; y += 300) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 60)); }
    window.scrollTo(0, 0); await new Promise(r => setTimeout(r, 500));
    return [...document.querySelectorAll('.ct-rise')]
      .filter(e => parseFloat(getComputedStyle(e).opacity) < 0.99).length;
  });
  is(unseen === 0, 'nothing is left invisible after a full pass', `${unseen} still hidden`);

  is(errs.length === 0, 'no script errors', errs.slice(0, 2).join(' | '));
  await pg.close();
}

/* ------------------------------------------------------------------ phone */
{
  const pg = await b.newPage();
  await pg.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  await pg.goto(URLP, { waitUntil: 'networkidle0' });
  await new Promise(r => setTimeout(r, 600));

  // The three ways are full-bleed bands, and on a phone they ARE the first
  // screen: each one a whole-width target, wholly visible without a scroll.
  const bands = await pg.evaluate(() => [...document.querySelectorAll('.ct-band__a')].map(a => {
    const r = a.getBoundingClientRect();
    return { top: Math.round(r.top), bottom: Math.round(r.bottom), w: Math.round(r.width), h: Math.round(r.height), href: a.getAttribute('href').slice(0, 7) };
  }));
  is(bands.length === 3 && bands.map(b => b.href).join() === 'https:/,tel:+34,mailto:',
     'three bands: WhatsApp, phone, e-mail, in that order', bands.map(b => b.href).join(' '));
  is(bands.every(b => b.bottom <= 844 && b.w === 390 && b.h >= 88),
     'all three wholly on the first screen, full width, thumb-sized', JSON.stringify(bands.map(b => [b.top, b.bottom])));

  const once = await pg.evaluate(() => (document.querySelector('.ct-bands').textContent.match(/614 753 187/g) || []).length);
  is(once === 1, 'the number is printed once, not once per app', `${once}`);

  // The dock repeats WhatsApp and the phone, so it waits until the bands have scrolled off.
  const dock = async () => pg.evaluate(() => getComputedStyle(document.querySelector('.cat-dock')).visibility);
  const d0 = await dock();
  await pg.evaluate(() => window.scrollTo(0, 1600)); await new Promise(r => setTimeout(r, 700));
  const d1 = await dock();
  await pg.evaluate(() => window.scrollTo(0, 0)); await new Promise(r => setTimeout(r, 700));
  const d2 = await dock();
  is(d0 === 'hidden' && d1 === 'visible' && d2 === 'hidden',
     'the dock waits while the bands are showing', `${d0} → ${d1} → ${d2}`);

  // The bands sweep in like the catalogue's rows, and are never left unpainted.
  await new Promise(r => setTimeout(r, 1800));
  const live = await pg.evaluate(() => [...document.querySelectorAll('.ct-band')].every(b => b.classList.contains('is-live')
    && getComputedStyle(b.querySelector('.ct-band__fill')).clipPath.replace(/\s/g, '') !== 'inset(0px100%0px0px)'));
  is(live, 'every band has swept in and is painted');

  // One map on this page: the footer's band would be the same place twice.
  is(!(await pg.$('.mc-foot__place')), 'the footer map is switched off here');

  await pg.close();
}

/* -------------------------------------------------------------- the map */
{
  // A picture of the map until it is asked for: not one request to Google Maps
  // (the site font from fonts.googleapis is a separate matter and allowed).
  const pg = await b.newPage();
  const reqs = [];
  pg.on('request', r => reqs.push(r.url()));
  await pg.setViewport({ width: 1440, height: 900 });
  await pg.goto(URLP, { waitUntil: 'networkidle0' });
  await pg.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 300) { scrollTo(0, y); await new Promise(r => setTimeout(r, 60)); } });
  await new Promise(r => setTimeout(r, 500));
  const maps = u => /maps\.google|google\.com\/maps|maps\.gstatic|maps\.googleapis/.test(u);
  const before = reqs.filter(maps).length;
  const img = reqs.filter(u => /\/img\/map\//.test(u)).length;
  is(before === 0 && img >= 1, 'the map is our picture until asked for: no Google Maps request', `google ${before}, map images ${img}`);
  await pg.click('#map-on');
  await pg.waitForSelector('#ct-map iframe', { timeout: 5000 }).catch(() => {});
  is(!!(await pg.$('#ct-map iframe')), 'and "Activar el mapa" builds the live one');
  await pg.close();
}

await b.close();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
