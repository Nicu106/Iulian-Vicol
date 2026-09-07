// tools/audit/suites/images.mjs
// What a first-time visitor downloads, and whether it still looks like a photograph.
// Every assertion here encodes a defect that was live on this site. Exits 1.
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

// Budgets are the measured figure with headroom, not aspirations. /coche was
// 7.17 MB before this work; /inicio carries a 25-photograph carousel and is the
// one page where the bytes are the design.
const BUDGET = { '/coche': 0.60, '/catalogo': 0.75, '/inicio': 1.40, '/contacto': 0.35 };

const browser = await p.launch({
  executablePath: exe,
  args: ['--no-sandbox', '--ignore-certificate-errors', `--host-resolver-rules=MAP ${HOST} 127.0.0.1`],
});

// one real car, picked from the catalogue rather than hard-coded
const nav = await browser.newPage();
await nav.setViewport({ width: 1440, height: 900 });
await nav.goto(`${B}/catalogo`, { waitUntil: 'networkidle0' });
const carHref = await nav.evaluate(() =>
  document.querySelector('.mc-card:not(.mc-card--demo) a.mc-card__link')?.getAttribute('href'));
await nav.close();

for (const route of [carHref, '/catalogo', '/inicio', '/contacto']) {
  const key = route.startsWith('/coche') ? '/coche' : route;
  const pg = await browser.newPage();
  await pg.setViewport({ width: 390, height: 844, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
  await pg.setCacheEnabled(false);

  const seen = [];
  pg.on('response', async res => {
    if (res.request().resourceType() !== 'image') return;
    let n = +(res.headers()['content-length'] || 0);
    if (!n) { try { n = (await res.buffer()).length; } catch {} }
    seen.push({ url: res.url(), bytes: n });
  });

  await pg.goto(B + route, { waitUntil: 'networkidle0', timeout: 120000 });
  await new Promise(r => setTimeout(r, 700));

  const mb = seen.reduce((a, i) => a + i.bytes, 0) / 1048576;
  is(mb <= BUDGET[key], `${route} stays inside its image budget`,
     `${mb.toFixed(2)} MB of ${BUDGET[key]} MB, ${seen.length} requests`);

  // /coche pointed its stage and its 42 thumbnails at the dealer's ORIGINALS —
  // 7.17 MB on a 390px phone, one file of 3.59 MB. Nothing may serve an original.
  const originals = seen.filter(i => /\/storage\/(?!cache\/)/.test(i.url));
  is(originals.length === 0, `${route} serves no original files`,
     originals.length ? originals[0].url.slice(-60) : 'all derived');

  // Nothing above 400 KB: that is a photograph, not a page.
  const heavy = seen.filter(i => i.bytes > 400 * 1024);
  is(heavy.length === 0, `${route} has no single image over 400 KB`,
     heavy.length ? `${(heavy[0].bytes / 1024).toFixed(0)} KB` : 'heaviest under the line');

  // The one thing the client has rejected twice: a photograph scaled up to fill
  // its box. `sizes` that only counts WIDTH gets this wrong on a tall cover box —
  // the contact hero was picking a 720px file for a slot that needed 787.
  const up = await pg.evaluate(async () => {
    const out = [];
    for (const i of document.images) {
      const r = i.getBoundingClientRect();
      if (r.width < 80 || !i.currentSrc || i.currentSrc.startsWith('data:')) continue;
      let real;
      try {
        const bmp = await createImageBitmap(await (await fetch(i.currentSrc)).blob());
        real = [bmp.width, bmp.height]; bmp.close();
      } catch { continue; }
      const fit = getComputedStyle(i).objectFit;
      const s = fit === 'contain'
        ? Math.min(r.width / real[0], r.height / real[1])
        : Math.max(r.width / real[0], r.height / real[1]);
      if (s > 1.02) out.push({ cls: (i.className || i.id || 'img').slice(0, 24), s: +s.toFixed(2),
                               box: Math.round(r.width) + 'x' + Math.round(r.height), real: real.join('x') });
    }
    return out;
  });
  is(up.length === 0, `${route} never scales a photograph up to fill its box`,
     up.length ? up.map(u => `${u.cls} ${u.box} from ${u.real} = ${u.s}x`).join(' | ') : 'every image at or above 1:1');

  // The image the page is judged on must not be lazy: a lazily-loaded LCP is the
  // classic own goal, and it is invisible unless you look for it.
  const hero = await pg.evaluate(() => {
    const i = [...document.images].sort((a, b) => {
      const A = a.getBoundingClientRect(), B = b.getBoundingClientRect();
      return (B.width * B.height) - (A.width * A.height);
    })[0];
    if (!i) return null;
    return { lazy: i.loading === 'lazy', prio: i.fetchPriority || i.getAttribute('fetchpriority'),
             cls: (i.className || i.id || 'img').slice(0, 28) };
  });
  is(hero && !hero.lazy, `${route}'s largest image is not lazy-loaded`,
     hero ? `${hero.cls} loading=${hero.lazy ? 'lazy' : 'eager'} fetchpriority=${hero.prio || '(none)'}` : 'no image');

  await pg.close();
}

// Stylesheets were going out uncompressed: nginx has gzip on but its default
// gzip_types is text/html alone, so only the HTML was compressed.
{
  const pg = await browser.newPage();
  await pg.setCacheEnabled(false);
  await pg.goto(`${B}/contacto`, { waitUntil: 'networkidle0' });
  // Not the Content-Encoding header: Chrome hands back the DECODED body and does
  // not surface that header through the devtools protocol, so reading it reports
  // every stylesheet as uncompressed whether it is or not. transferSize against
  // decodedBodySize is what actually crossed the wire.
  const css = await pg.evaluate(() => performance.getEntriesByType('resource')
    .filter(e => e.initiatorType === 'link' && e.name.includes('.css'))
    .map(e => ({ f: e.name.split('/').pop().split('?')[0],
                 sent: e.transferSize, raw: e.decodedBodySize })));
  const plain = css.filter(c => c.raw > 512 && c.sent >= c.raw * 0.9);
  const saved = css.reduce((a, c) => a + (c.raw - c.sent), 0);
  is(css.length > 0 && plain.length === 0, 'every stylesheet is served compressed',
     plain.length ? plain.map(c => c.f).join(', ')
                  : `${css.length} files, ${(saved / 1024).toFixed(0)} KB saved on this page alone`);
  await pg.close();
}

await browser.close();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
