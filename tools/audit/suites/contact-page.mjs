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
  const pic = await pg.evaluate(() => {
    const i = document.getElementById('hero-img');
    const r = i.getBoundingClientRect();
    return { nat: [i.naturalWidth, i.naturalHeight], box: [r.width, r.height],
             transform: getComputedStyle(i).transform };
  });
  is(pic.box[0] <= pic.nat[0] && pic.box[1] <= pic.nat[1],
     'the photograph is never drawn larger than the file',
     `${Math.round(pic.box[0])}x${Math.round(pic.box[1])} from ${pic.nat.join('x')}`);
  is(pic.transform === 'none',
     'and it carries no transform, so nothing is resampled', pic.transform);

  // A 32px error here shipped once: the grid's main track was the full container
  // while every .cat-wrap section under it started a gutter further in. A 16px
  // error shipped too: the grid's gutter plus a padding-inline of its own.
  const spine = await pg.evaluate(() => {
    const edge = (sel) => {
      const e = document.querySelector(sel);
      if (!e) return null;
      return Math.round(e.getBoundingClientRect().left + parseFloat(getComputedStyle(e).paddingLeft));
    };
    return { open: edge('.ct-open__say'), ways: edge('.ct-open__ways-wrap'),
             where: edge('.ct-where__say'), far: edge('.ct-far'), write: edge('.ct-write') };
  });
  const xs = Object.values(spine).filter(v => v !== null);
  is(new Set(xs).size === 1, 'every section starts on the same vertical spine',
     JSON.stringify(spine));

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
      const map  = document.querySelector('.ct-where__canvas').getBoundingClientRect();
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

  // At 3:4 the photograph is 520 of an 844px screen; leading with it put the
  // second line of the opening statement behind the fixed dock.
  const order = await pg.evaluate(() => {
    const t = (s) => Math.round(document.querySelector(s).getBoundingClientRect().top);
    return { say: t('.ct-open__say'), pic: t('.ct-open__pic'), ways: t('.ct-open__ways-wrap') };
  });
  is(order.say < order.pic && order.pic < order.ways,
     'phone reads statement, then photograph, then the ways', JSON.stringify(order));

  const clear = await pg.evaluate(() => {
    const h = document.querySelector('.ct-open__h').getBoundingClientRect();
    const dock = document.querySelector('.cat-dock');
    const d = dock ? dock.getBoundingClientRect().top : window.innerHeight;
    return { hBottom: Math.round(h.bottom), dockTop: Math.round(d) };
  });
  is(clear.hBottom <= clear.dockTop,
     'and the opening statement clears the dock', `${clear.hBottom} vs ${clear.dockTop}`);

  // On a phone the full width IS the photograph's width, so nothing comes off it.
  const shape = await pg.evaluate(() => {
    const i = document.getElementById('hero-img');
    const r = i.getBoundingClientRect();
    return { box: r.width / r.height, file: i.naturalWidth / i.naturalHeight };
  });
  is(Math.abs(shape.box - shape.file) < 0.01,
     'and the photograph is shown at its own ratio, uncropped',
     `${shape.box.toFixed(3)} vs ${shape.file.toFixed(3)}`);

  await pg.close();
}

await b.close();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
