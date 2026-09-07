// tools/audit/suites/layout.mjs
// One document for the whole site. Every page must come through layouts/site,
// which means: the same head, the same header, the same footer, in that order,
// once each — and a page must still be able to differ where it legitimately does.
import p from 'puppeteer';
import { readdirSync } from 'fs';
import { execSync } from 'child_process';

const HOST = process.env.AUDIT_HOST || 'v2design.ivmotorclass.com';
const B    = `https://${HOST}`;
const ROOT = '/var/www/motorclass-v2';
const dir  = `${process.env.HOME}/.cache/puppeteer/chrome`;
const exe  = `${dir}/${readdirSync(dir).filter(d => d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;

let bad = 0;
const ok   = (t, d) => console.log(`✓ ${t}${d ? '  — ' + d : ''}`);
const fail = (t, d) => { bad++; console.log(`✗ ${t}${d ? '  — ' + d : ''}`); };
const is   = (c, t, d) => (c ? ok : fail)(t, d);

// No page may carry its own document any more. Five copies of one head is how
// /coche ended up with a viewport meta the others did not have.
{
  const stray = execSync(
    `cd ${ROOT} && grep -l 'DOCTYPE\\|<head>\\|preconnect' resources/views/pages/{inicio,catalogo,coche,contacto,sell-car}.blade.php 2>/dev/null || true`,
    { shell: '/bin/bash' }).toString().trim();
  is(stray === '', 'no page declares its own document', stray || 'all five extend layouts/site');
  const ext = execSync(`cd ${ROOT} && head -1 resources/views/pages/{inicio,catalogo,coche,contacto,sell-car}.blade.php | grep -c "@extends('layouts.site')" || true`,
    { shell: '/bin/bash' }).toString().trim();
  is(ext === '5', 'and all five extend the shared layout', `${ext}/5`);
}

const browser = await p.launch({ executablePath: exe,
  args: ['--no-sandbox', '--ignore-certificate-errors', `--host-resolver-rules=MAP ${HOST} 127.0.0.1`] });

const ROUTES = [
  ['/inicio',   'IV MOTORCLASS — Coches alemanes premium en Málaga', 'Inicio'],
  ['/catalogo', 'Catálogo — IV MOTORCLASS',                          'Coches'],
  ['/contacto', 'Contacto — IV MOTORCLASS',                          'Contacto'],
  ['/vende',    'Vende tu coche — IV MOTORCLASS',                    'Vende tu coche'],
  ['/coche/volkswagen-golf-2017-p3pif', null, null],
];

const BASE = ['brandbook.css', 'catalog.css', 'foot.css', 'mc-tokens.css'];

for (const [route, title, current] of ROUTES) {
  const pg = await browser.newPage();
  await pg.setViewport({ width: 1440, height: 900 });
  const errs = []; pg.on('pageerror', e => errs.push(String(e)));
  await pg.goto(B + route, { waitUntil: 'networkidle0' });

  const m = await pg.evaluate(() => {
    const one = (s) => document.querySelectorAll(s).length;
    const order = [...document.body.children].map(e => e.tagName + '.' + (e.className || '').split(' ')[0]);
    return {
      title: document.title,
      heads: one('.mc-head'), foots: one('.mc-foot'), mains: one('main'),
      current: document.querySelector('.mc-nav__i.is-current')?.textContent.trim() || null,
      nav: [...document.querySelectorAll('.mc-nav__i')].length,
      css: [...document.styleSheets].map(s => (s.href || '').split('/').pop().split('?')[0]).filter(Boolean),
      viewport: document.querySelector('meta[name=viewport]')?.content || '',
      robots: document.querySelector('meta[name=robots]')?.content || '',
      // header first, footer last of the in-flow furniture
      headerFirst: order[0]?.startsWith('HEADER'),
      footIndex: order.findIndex(x => x.startsWith('FOOTER')),
      mainIndex: order.findIndex(x => x.startsWith('MAIN')),
    };
  });

  is(m.heads === 1 && m.foots === 1 && m.mains === 1,
     `${route} has exactly one header, one main and one footer`,
     `${m.heads}/${m.mains}/${m.foots}`);
  is(m.headerFirst && m.mainIndex > 0 && m.footIndex > m.mainIndex,
     `${route} orders them header → main → footer`);
  is(m.nav === 5, `${route} carries the five nav links`, `${m.nav}`);
  is(BASE.every(c => m.css.includes(c)), `${route} loads the four shared stylesheets`,
     m.css.join(' '));
  is(m.viewport.includes('interactive-widget') && m.robots.includes('noindex'),
     `${route} inherits the shared viewport and robots meta`);
  if (title) is(m.title === title, `${route} keeps its own title`, m.title);
  if (current) is(m.current === current, `${route} marks its nav item`, m.current || '(none)');
  is(errs.length === 0, `${route} has no script errors`, errs.slice(0, 1).join(''));
  await pg.close();
}

// A page must still be able to differ where it should: the sold theme dresses the
// whole document, header and footer included.
{
  const pg = await browser.newPage();
  await pg.setViewport({ width: 1440, height: 900 });
  await pg.goto(`${B}/coche/renault-clio-15-dci-2008-owgrb`, { waitUntil: 'networkidle0' });
  const r = await pg.evaluate(() => ({
    body: document.body.className.trim(),
    headCta: getComputedStyle(document.querySelector('.mc-head .mc-btn--cta')).backgroundColor,
  }));
  is(r.body.includes('car--sold') && r.headCta === 'rgb(71, 84, 103)',
     'a page can still dress the whole document — the sold theme reaches the header',
     `body="${r.body}" header CTA ${r.headCta}`);
  await pg.close();
}

await browser.close();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
