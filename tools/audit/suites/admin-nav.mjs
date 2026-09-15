// tools/audit/suites/admin-nav.mjs
// The panel's navigation must keep working however many sections it grows to.
//
// The rule (App\Support\AdminNav): every section in the desk rail, grouped once
// there are enough; on a phone at most five tabs, and from six sections the
// first four plus "Más", which opens the rest in a sheet (or /admin/mas where
// the sheet cannot open). This suite renders the real layout with 5, 6, 8 and
// 12 sections — the real five and invented ones — and measures what a thumb and
// an eye would meet. The admin sits behind auth, so pages are rendered to disk
// by PHP and loaded as file://, the same way the admin floor is audited.
import p from 'puppeteer';
import { readdirSync, writeFileSync, readFileSync, mkdirSync } from 'fs';
import { execFileSync } from 'child_process';

const HOST = process.env.AUDIT_HOST || 'v2design.ivmotorclass.com';
const ROOT = '/var/www/motorclass-v2';
const OUT  = `${ROOT}/tools/audit/out/admin-nav`;
mkdirSync(OUT, { recursive: true });
const dir  = `${process.env.HOME}/.cache/puppeteer/chrome`;
const exe  = `${dir}/${readdirSync(dir).filter(d => d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;

let bad = 0;
const ok   = (t, d) => console.log(`✓ ${t}${d ? '  — ' + d : ''}`);
const fail = (t, d) => { bad++; console.log(`✗ ${t}${d ? '  — ' + d : ''}`); };
const is   = (c, t, d) => (c ? ok : fail)(t, d);

// ---- the sections to try --------------------------------------------------
const make = (n, groups = false) => Array.from({ length: n }, (_, k) => ({
  route: null, match: `fake.i${k}`, href: `#i${k}`,
  label: ['Panel', 'Coches', 'Mensajes', 'Opiniones', 'Ventas', 'Solicitudes de prueba',
          'Clientes', 'Facturas', 'Proveedores', 'Estadísticas', 'Usuarios', 'Ajustes'][k],
  tab:   ['Panel', 'Coches', 'Mensajes', 'Opiniones', 'Ventas', 'Pruebas',
          'Clientes', 'Facturas', 'Proveed.', 'Datos', 'Usuarios', 'Ajustes'][k],
  count: k === 2 ? 3 : (k === 7 ? 12 : null),
  group: groups ? (k < 3 ? 'Día a día' : k < 7 ? 'Contenido' : 'Gestión') : null,
}));

const SCENARIOS = [
  { name: 'real',    items: null,            current: null },
  { name: 'five',    items: make(5),         current: 'fake.i1' },
  { name: 'six',     items: make(6),         current: 'fake.i1' },
  { name: 'eight',   items: make(8),         current: 'fake.i6' },   // the page you are on is behind "Más"
  { name: 'twelve',  items: make(12, true),  current: 'fake.i0' },
];

// ---- render with PHP ------------------------------------------------------
const php = `<?php
require '${ROOT}/vendor/autoload.php';
$app = require '${ROOT}/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
$s = json_decode(file_get_contents($argv[1]), true);
foreach ($s as $sc) {
  App\\Support\\AdminNav::fake($sc['items'], $sc['current']);
  file_put_contents('${OUT}/' . $sc['name'] . '.html', view('layouts.ad')->render());
  if ($sc['items'] !== null) {
    file_put_contents('${OUT}/' . $sc['name'] . '-more.html', view('admin.more')->render());
  } else {
    file_put_contents('${OUT}/real-count.txt', (string) count(App\\Support\\AdminNav::items()));
  }
  App\\Support\\AdminNav::fake(null);
}
echo "rendered\\n";
`;
writeFileSync(`${OUT}/render.php`, php);
writeFileSync(`${OUT}/scenarios.json`, JSON.stringify(SCENARIOS));
execFileSync('php', [`${OUT}/render.php`, `${OUT}/scenarios.json`], { cwd: ROOT });
const REAL_N = Number(readFileSync(`${OUT}/real-count.txt`, 'utf8'));
for (const f of readdirSync(OUT).filter(f => f.endsWith('.html'))) {
  const h = readFileSync(`${OUT}/${f}`, 'utf8').split('http://localhost/').join(`https://${HOST}/`);
  writeFileSync(`${OUT}/${f}`, h);
}

// ---- measure --------------------------------------------------------------
const b = await p.launch({ executablePath: exe, args: ['--no-sandbox', '--ignore-certificate-errors',
  `--host-resolver-rules=MAP ${HOST} 127.0.0.1`] });

for (const sc of SCENARIOS) {
  const n = sc.items ? sc.items.length : REAL_N;
  const overflow = n > 5 ? n - 4 : 0;
  console.log(`\n— ${sc.name}: ${n} sections`);

  // phone
  {
    const pg = await b.newPage();
    await pg.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true, deviceScaleFactor: 2 });
    await pg.goto(`file://${OUT}/${sc.name}.html`, { waitUntil: 'networkidle0' });
    const m = await pg.evaluate(() => {
      const lis = [...document.querySelectorAll('.ad-nav > li')].filter(li => getComputedStyle(li).display !== 'none');
      const tabs = lis.map(li => {
        const a = li.querySelector('a'), r = li.getBoundingClientRect(), n = li.querySelector('.ad-nav__n');
        const nr = n && n.getBoundingClientRect();
        const lab = [...li.querySelectorAll('.ad-nav__l > span')].find(x => !x.classList.contains('ad-nav__n') && getComputedStyle(x).display !== 'none');
        const lr = lab && lab.getBoundingClientRect();
        const touch = nr && lr && !(nr.right <= lr.left || nr.left >= lr.right || nr.bottom <= lr.top || nr.top >= lr.bottom);
        return { more: li.classList.contains('ad-nav__more'), w: r.width, h: r.height, on: a.classList.contains('is-on'),
                 text: a.innerText.replace(/\s+/g, ' ').trim(), spill: a.scrollWidth > a.clientWidth + 1,
                 badgeOut: nr ? (nr.right > r.right + 0.5 || nr.left < r.left - 0.5 || nr.top < r.top - 0.5) : false,
                 badgeTouchesLabel: !!touch };
      });
      const sheet = document.getElementById('ad-more');
      return { tabs, overflowPx: Math.max(0, document.documentElement.scrollWidth - innerWidth),
               sheetHidden: sheet ? getComputedStyle(sheet).display === 'none' : null,
               moreHref: document.querySelector('.ad-nav__more a')?.getAttribute('href') || null };
    });
    const want = Math.min(n, 5);
    is(m.tabs.length === want, `390: ${want} tabs in the bar`, m.tabs.map(t => t.text).join(' | '));
    const ws = m.tabs.map(t => t.w);
    is(Math.max(...ws) - Math.min(...ws) <= 1, '390: tabs share the bar equally', ws.map(w => w.toFixed(1)).join(', '));
    is(m.tabs.every(t => t.h >= 44), '390: every tab is at least 44px tall');
    is(!m.tabs.some(t => t.spill), '390: no tab label spills its tab');
    is(!m.tabs.some(t => t.badgeOut), '390: a count stays inside its own tab');
    is(!m.tabs.some(t => t.badgeTouchesLabel), '390: a count never sits on top of its label');
    is(m.overflowPx === 0, '390: no sideways scroll', `${m.overflowPx}px`);
    if (overflow) {
      is(m.tabs.at(-1).more && m.tabs.at(-1).text.startsWith('Más'), '390: the fifth tab is "Más"');
      is(/\/admin\/mas$/.test(m.moreHref || ''), '390: "Más" is a real link to /admin/mas', m.moreHref);
      is(m.sheetHidden === true, '390: the sheet is hidden until asked for');

      const onIdx = sc.items ? sc.items.findIndex(i => i.match === sc.current) : -1;
      if (onIdx >= 4) is(m.tabs.at(-1).on, '390: "Más" is marked when the current page is behind it');
      else if (onIdx >= 0) is(m.tabs[onIdx]?.on && !m.tabs.at(-1).on, '390: the current tab is marked, "Más" is not');

      await pg.click('.ad-nav__more a');
      await new Promise(r => setTimeout(r, 450));
      const s = await pg.evaluate(() => {
        const el = document.getElementById('ad-more'), r = el.getBoundingClientRect();
        const links = [...el.querySelectorAll('.ad-navlist__i')];
        return { open: el.matches(':popover-open'), top: r.top, bottom: r.bottom, n: links.length,
                 minH: Math.min(...links.map(a => a.getBoundingClientRect().height)),
                 on: !!el.querySelector('.ad-navlist__i.is-on'),
                 expanded: document.querySelector('.ad-nav__more a').getAttribute('aria-expanded'),
                 focus: document.activeElement?.classList.contains('ad-navlist__i') };
      });
      is(s.open, '390: pressing "Más" opens the sheet');
      is(Math.abs(s.bottom - 844) <= 1 && s.top >= 0, '390: the sheet sits on the bottom edge, inside the screen', `top ${s.top.toFixed(0)}, bottom ${s.bottom.toFixed(0)}`);
      is(s.n === overflow, `390: the sheet lists the other ${overflow}`, `${s.n}`);
      is(s.minH >= 44, '390: every row in the sheet is at least 44px', `${s.minH}px`);
      is(s.expanded === 'true' && s.focus, '390: aria-expanded is set and focus moves into the sheet');
      if (onIdx >= 4) is(s.on, '390: the current page is marked inside the sheet');
      await pg.screenshot({ path: `${OUT}/${sc.name}-390-sheet.png` });
      await pg.keyboard.press('Escape');
      await new Promise(r => setTimeout(r, 150));
      is(await pg.evaluate(() => !document.getElementById('ad-more').matches(':popover-open')), '390: Escape closes it');
    } else {
      is(!m.tabs.some(t => t.more) && m.sheetHidden === null, '390: no "Más" and no sheet while everything fits');
    }
    await pg.close();
  }

  // desk
  {
    const pg = await b.newPage();
    await pg.setViewport({ width: 1440, height: 700 });
    await pg.goto(`file://${OUT}/${sc.name}.html`, { waitUntil: 'networkidle0' });
    const m = await pg.evaluate(() => {
      const vis = e => getComputedStyle(e).display !== 'none' && e.getBoundingClientRect().width > 0;
      const links = [...document.querySelectorAll('.ad-nav .ad-nav__i')].filter(vis);
      const heads = [...document.querySelectorAll('.ad-nav__g')].filter(vis);
      const rail = document.querySelector('.ad-rail');
      const last = links.at(-1);
      rail.scrollTop = rail.scrollHeight;
      const lr = last.getBoundingClientRect();
      return { links: links.length, more: links.some(a => a.closest('.ad-nav__more')), heads: heads.length,
               lastReachable: lr.bottom <= innerHeight + 0.5 && lr.top >= 0,
               overflowPx: Math.max(0, document.documentElement.scrollWidth - innerWidth) };
    });
    is(m.links === n && !m.more, `1440: all ${n} sections in the rail, no "Más"`, `${m.links}`);
    if (sc.name === 'twelve') {
      is(m.heads === 3, '1440: grouped under their three headings', `${m.heads}`);
      is(m.lastReachable, '1440: at 700px tall the last section is still reachable by scrolling the rail');
      await pg.screenshot({ path: `${OUT}/twelve-1440.png` });
    }
    is(m.overflowPx === 0, '1440: no sideways scroll');
    await pg.close();
  }

  // the page "Más" falls back to
  if (overflow && sc.items) {
    const pg = await b.newPage();
    await pg.setViewport({ width: 390, height: 844 });
    await pg.goto(`file://${OUT}/${sc.name}-more.html`, { waitUntil: 'networkidle0' });
    const c = await pg.evaluate(() => document.querySelectorAll('main .ad-navlist__i').length);
    is(c === overflow, `/admin/mas lists the same ${overflow} sections`, `${c}`);
    await pg.close();
  }
}

await b.close();
console.log(bad ? `\n${bad} failed` : '\nall good');
process.exit(bad ? 1 : 0);
