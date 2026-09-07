// tools/audit/suites/social.mjs
// The TikTok section on /inicio. The number is the picture, so it has to arrive,
// land on the real figure, and be readable by someone who cannot see it move.
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

const browser = await p.launch({ executablePath: exe,
  args: ['--no-sandbox', '--ignore-certificate-errors', `--host-resolver-rules=MAP ${HOST} 127.0.0.1`] });

/* --- where it sits, and what it links to --------------------------------- */
{
  const pg = await browser.newPage();
  await pg.setViewport({ width: 1440, height: 900 });
  const errs = []; pg.on('pageerror', e => errs.push(String(e)));
  await pg.goto(`${B}/inicio`, { waitUntil: 'networkidle0' });

  const m = await pg.evaluate(() => {
    const sec = document.getElementById('social');
    const reviews = document.getElementById('reviews');
    const rows = [...sec.querySelectorAll('.hm-soc__row')];
    return {
      afterReviews: sec.getBoundingClientRect().top > reviews.getBoundingClientRect().top,
      links: rows.map(r => {
        const a = r.querySelector('a');
        const b = a.getBoundingClientRect();
        return { href: a.getAttribute('href'), blank: a.target === '_blank',
                 rel: a.getAttribute('rel') || '', lead: r.classList.contains('hm-soc__row--lead'),
                 h: Math.round(b.height), bg: getComputedStyle(a).backgroundColor };
      }),
      // the real figure has to be in text, not only in the aria-hidden counter
      spoken: sec.querySelector('.hm-soc__unit').textContent.replace(/\s+/g, ' ').trim(),
      counterHidden: sec.querySelector('.hm-soc__big').getAttribute('aria-hidden') === 'true',
      ground: getComputedStyle(sec).backgroundColor,
    };
  });

  is(m.afterReviews, 'the section sits below the reviews — proof first, reach second');
  is(m.links.length === 3, 'three platforms', `${m.links.length}`);
  is(/tiktok\.com/.test(m.links[0].href) && m.links[0].lead,
     'TikTok leads, because TikTok is the one that works', m.links[0].href);
  is(m.links[0].h > m.links[1].h && m.links[0].bg === 'rgb(255, 255, 255)',
     'and it is the only one that is not a quiet row',
     `${m.links[0].h}px white vs ${m.links[1].h}px`);
  is(m.links.every(l => l.blank && /noopener/.test(l.rel)),
     'every outbound link opens away and carries rel=noopener');
  is(/600\.000/.test(m.spoken) && /TikTok/.test(m.spoken),
     'the real figure is in readable text, not only in the animated counter', m.spoken);
  is(m.counterHidden, 'and the counter itself is hidden from a screen reader');
  is(m.ground === 'rgb(0, 0, 0)', "on TikTok's own black, so it is not the footer's navy", m.ground);
  is(errs.length === 0, 'no script errors', errs.slice(0, 1).join(''));
  await pg.close();
}

/* --- the arrival ----------------------------------------------------------
   It starts at zero, counts, and the two chromatic halves come in wide and
   settle into register. Sampled while it runs, not only at the end. */
{
  const pg = await browser.newPage();
  await pg.setViewport({ width: 1440, height: 900 });
  await pg.goto(`${B}/inicio`, { waitUntil: 'networkidle0' });
  const run = await pg.evaluate(async () => {
    const n = () => document.getElementById('soc-n').textContent;
    const ghost = () => getComputedStyle(document.querySelector('.hm-soc__ghost--c')).translate;
    const start = { n: n(), g: ghost() };
    document.getElementById('social').scrollIntoView({ block: 'center' });
    await new Promise(r => setTimeout(r, 350));
    const mid = { n: n(), g: ghost() };
    await new Promise(r => setTimeout(r, 2000));
    const end = { n: n(), g: ghost() };
    return { start, mid, end };
  });
  is(run.start.n === '0', 'the number starts at zero', run.start.n);
  is(run.mid.n !== '0' && run.mid.n !== run.end.n, 'it counts rather than appearing',
     `${run.start.n} → ${run.mid.n} → ${run.end.n}`);
  is(run.end.n === '600.000', 'and lands on the real figure', run.end.n);
  is(run.start.g !== run.end.g && run.end.g === '-3px 2px',
     'the chromatic halves travel and settle into register',
     `${run.start.g} → ${run.end.g}`);
  await pg.close();
}

/* --- someone who asked for less motion ------------------------------------ */
{
  const pg = await browser.newPage();
  await pg.setViewport({ width: 1440, height: 900 });
  await pg.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
  await pg.goto(`${B}/inicio`, { waitUntil: 'networkidle0' });
  await new Promise(r => setTimeout(r, 500));
  const r = await pg.evaluate(() => ({
    n: document.getElementById('soc-n').textContent,
    g: getComputedStyle(document.querySelector('.hm-soc__ghost--c')).translate,
  }));
  is(r.n === '600.000' && r.g === '-3px 2px',
     'with reduced motion the number is simply there, in register',
     `${r.n} at ${r.g}`);
  await pg.close();
}

await browser.close();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
