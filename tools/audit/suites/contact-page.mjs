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
    return { open: edge('.ct-open__h'), ways: edge('.ct-ways .ct-way__k'),
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
      // the map band under the photograph was removed on 2026-09-23
      out.push({ y, underPhoto: Math.round(pic.bottom - hold.bottom), picToMap: 0 });
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

  // 2026-09-23: the ways come before the photograph. With the photograph first
  // (3:4, 520px) the first way to reach him started at y 913 on an 844 screen.
  const order = await pg.evaluate(() => {
    const t = (s) => Math.round(document.querySelector(s).getBoundingClientRect().top);
    return { say: t('.ct-open__say'), ways: t('.ct-open__ways-wrap'), pic: t('.ct-open__pic') };
  });
  is(order.say < order.ways && order.ways < order.pic,
     'phone reads statement, then the ways, then the photograph', JSON.stringify(order));

  // The page's reason to exist is one tap from arrival: both buttons wholly on
  // the first screen, thumb-sized, and in its lower half.
  const go = await pg.evaluate(() => [...document.querySelectorAll('.ct-way__go .mc-btn')].map(e => {
    const r = e.getBoundingClientRect(); return { top: Math.round(r.top), bottom: Math.round(r.bottom), h: Math.round(r.height) };
  }));
  is(go.length === 2 && go.every(g => g.bottom <= 844 && g.h >= 52),
     'WhatsApp and Llamar are on the first screen, 52px tall', JSON.stringify(go));
  is(go.every(g => g.top >= 844 / 2 - 60), 'and within reach of the thumb', `top ${go[0]?.top}`);

  const once = await pg.evaluate(() => (document.querySelector('.ct-open').textContent.match(/614 753 187/g) || []).length);
  is(once === 1, 'the number is printed once, not once per app', `${once}`);

  // The dock repeats those two buttons, so it waits until they have scrolled off.
  const dock = async () => pg.evaluate(() => getComputedStyle(document.querySelector('.cat-dock')).visibility);
  const d0 = await dock();
  await pg.evaluate(() => window.scrollTo(0, 1400)); await new Promise(r => setTimeout(r, 700));
  const d1 = await dock();
  await pg.evaluate(() => window.scrollTo(0, 0)); await new Promise(r => setTimeout(r, 700));
  const d2 = await dock();
  is(d0 === 'hidden' && d1 === 'visible' && d2 === 'hidden',
     'the dock waits while the page\'s own buttons are showing', `${d0} → ${d1} → ${d2}`);

  const shape = await pg.evaluate(() => {
    const r = document.querySelector('.ct-open__hold').getBoundingClientRect();
    return r.width / r.height;
  });
  is(Math.abs(shape - 4 / 3) < 0.01, 'the photograph closes the act at 4:3', shape.toFixed(3));

  await pg.close();
}

await b.close();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
