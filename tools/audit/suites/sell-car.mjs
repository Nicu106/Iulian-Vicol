// tools/audit/suites/sell-car.mjs
// "Vende tu coche": the marque question first, six required answers, photographs
// shown whole, a real submission that lands as a pending car nobody can see.
// Exits 1 on failure. Creates one row and deletes it.
import p from 'puppeteer';
import { readdirSync } from 'fs';
import { execSync } from 'child_process';

const HOST = process.env.AUDIT_HOST || 'v2design.ivmotorclass.com';
const B    = `https://${HOST}`;
const ROOT = '/var/www/motorclass-v2';
const dir  = `${process.env.HOME}/.cache/puppeteer/chrome`;
const exe  = `${dir}/${readdirSync(dir).filter(d => d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;
// The PHP goes in through an environment variable, not the argument list: inside
// the shell's double quotes every `$v` in the snippet expanded to nothing and
// tinker was handed a parse error. `"$TINK"` expands once, to the code, and the
// code's own dollars are never seen by the shell.
// the security suite may have spent this IP's four-an-hour allowance
execSync(`cd ${ROOT} && php artisan cache:clear >/dev/null 2>&1`);
const tinker = (code) => execSync(`cd ${ROOT} && php artisan tinker --execute="$TINK" 2>/dev/null`,
  { env: { ...process.env, TINK: code } }).toString().trim();

let bad = 0;
const ok   = (t, d) => console.log(`✓ ${t}${d ? '  — ' + d : ''}`);
const fail = (t, d) => { bad++; console.log(`✗ ${t}${d ? '  — ' + d : ''}`); };
const is   = (c, t, d) => (c ? ok : fail)(t, d);

const MARK = 'PRUEBA-SUITE-' + Date.now().toString(36);
const b = await p.launch({ executablePath: exe,
  args: ['--no-sandbox', '--ignore-certificate-errors', `--host-resolver-rules=MAP ${HOST} 127.0.0.1`] });

/* --- the page ------------------------------------------------------------ */
{
  const pg = await b.newPage();
  const errs = []; pg.on('pageerror', e => errs.push(String(e)));
  await pg.setViewport({ width: 1440, height: 900 });
  const r = await pg.goto(`${B}/vende`, { waitUntil: 'networkidle0' });
  is(r.status() === 200 && await pg.$('.sl-marque'), '/vende is served, not the holding page',
     'BrandbookOnly allowlist must carry vende and sell-car');
  const redirect = await (await fetch(`${B}/sell-car`, { redirect: 'manual' }).catch(() => null))?.status;
  is(redirect === 301, 'the live site\'s /sell-car address still works', `HTTP ${redirect}`);

  const shape = await pg.evaluate(() => {
    const tiles = [...document.querySelectorAll('.sl-marque')];
    const cat = ['#022254', '#930016', '#004086', '#01172E', '#C50007'].map(h => h.toLowerCase());
    const cols = tiles.slice(0, 5).map(t => (t.style.getPropertyValue('--brand') || '').toLowerCase());
    const q = new Set([...document.querySelectorAll('form [required]')].map(e => e.name)).size;
    const spine = Math.round(document.querySelector('.sl-open__say h1').getBoundingClientRect().left);
    return { tiles: tiles.length, coloursMatchCatalogue: JSON.stringify(cols) === JSON.stringify(cat),
             questions: q, spine, fields: document.querySelectorAll('form input:not([type=radio]):not([type=hidden]),form select,form textarea').length };
  });
  is(shape.tiles === 6, 'five marques and "otra" are the first question', `${shape.tiles} tiles`);
  is(shape.coloursMatchCatalogue, 'and they wear the catalogue\'s exact colours');
  is(shape.questions === 6, 'six required questions, not seventeen', `${shape.questions} required names, ${shape.fields} fields`);
  is(shape.spine === 152, 'the opening line sits on the site spine', `${shape.spine}px`);

  // the sections lay out two-column on a desk and stack on a phone
  const cols = await pg.evaluate(() => {
    const s = document.querySelector('.sl-sec');
    const h = s.querySelector('.sl-sec__head').getBoundingClientRect(), bd = s.querySelector('.sl-sec__body').getBoundingClientRect();
    return { side: bd.left > h.right, bodyW: Math.round(bd.width) };
  });
  is(cols.side && cols.bodyW > 600, 'each movement is question | answers at 1440', `fields column ${cols.bodyW}px`);
  await pg.setViewport({ width: 390, height: 844 }); await new Promise(r => setTimeout(r, 300));
  const stack = await pg.evaluate(() => {
    const s = document.querySelector('.sl-sec');
    const h = s.querySelector('.sl-sec__head').getBoundingClientRect(), bd = s.querySelector('.sl-sec__body').getBoundingClientRect();
    return { stacked: bd.top >= h.bottom, overflow: Math.max(0, document.documentElement.scrollWidth - innerWidth) };
  });
  is(stack.stacked && stack.overflow === 0, 'and stacks on a phone without overflow');
  is(errs.length === 0, 'no script errors', errs.slice(0, 2).join(' | '));
  await pg.close();
}

/* --- a real submission --------------------------------------------------- */
{
  const pg = await b.newPage(); await pg.setViewport({ width: 1440, height: 900 });
  await pg.goto(`${B}/vende`, { waitUntil: 'networkidle0' });
  const files = execSync(`ls ${ROOT}/storage/app/public/vehicles/*/*.jpg | head -2`).toString().trim().split('\n');
  await pg.click('.sl-marque:nth-child(2)');                      // Audi
  await pg.type('input[name=model]', 'A4 ' + MARK);
  await pg.select('select[name=year]', '2018');
  await pg.type('input[name=mileage]', '99000');
  await pg.type('input[name=seller_name]', 'Suite');
  await pg.type('input[name=seller_phone]', '600 000 000');
  await (await pg.$('#sl-files')).uploadFile(...files);
  await new Promise(r => setTimeout(r, 700));
  const prev = await pg.evaluate(() => ({ n: document.querySelectorAll('.sl-prev').length,
    fit: getComputedStyle(document.querySelector('.sl-prev img')).objectFit }));
  is(prev.n === 2 && prev.fit === 'contain', 'photographs preview whole, never cropped', `${prev.n} previews, object-fit ${prev.fit}`);
  await pg.click('.sl-prev:nth-child(1) .sl-prev__x'); await new Promise(r => setTimeout(r, 200));
  const left = await pg.evaluate(() => document.getElementById('sl-files').files.length);
  is(left === 1, 'removing a preview really removes the file from the input', `${left} left`);
  await (await pg.$('#sl-files')).uploadFile(...files); await new Promise(r => setTimeout(r, 400));
  // The form refuses anything sent inside four seconds — see sell-car-security.mjs
  // and the clock in SellCarRequest. A person takes about 25; this suite is faster
  // than any bot and has to wait like everyone else.
  await new Promise(r => setTimeout(r, 4500));
  await Promise.all([pg.waitForNavigation({ waitUntil: 'networkidle0', timeout: 120000 }), pg.click('#sl-submit')]);
  const landed = pg.url().endsWith('/vende?enviado=1');
  const h1 = await pg.evaluate(() => document.querySelector('h1')?.textContent.replace(/\s+/g, ' ').trim());
  is(landed && /Lo tengo/.test(h1 || ''), 'sending lands on the "received" state', `${pg.url().split(HOST)[1]} — "${h1}"`);
  await pg.close();

  const row = tinker(`$v=\\App\\Models\\Vehicle::where("model","like","%${MARK}%")->latest()->first(); echo $v? json_encode(["status"=>$v->status,"brand"=>$v->brand,"imgs"=>is_array($v->images)?count($v->images):-1,"cover"=>(bool)$v->cover_image,"slug"=>$v->slug]):"null";`);
  let v = null; try { v = JSON.parse(row); } catch {}
  is(v && v.status === 'pending' && v.brand === 'Audi', 'it is stored as a pending Audi for him to review', row.slice(0, 120));
  is(v && v.imgs === 2 && v.cover, 'with its photographs as an array and a cover set', v ? `${v.imgs} images` : '');
  if (v) {
    const pub = (await fetch(`${B}/coche/${v.slug}`).catch(() => null))?.status;
    is(pub === 404, 'and nobody can open it on the public car page until approved', `HTTP ${pub}`);
    tinker(`$v=\\App\\Models\\Vehicle::where("slug","${v.slug}")->first(); $d=storage_path("app/public/sell-cars/".$v->slug); if(is_dir($d)){foreach(glob($d."/*") as $f) unlink($f); rmdir($d);} $v->delete(); echo "cleaned";`);
    const gone = tinker(`echo \\App\\Models\\Vehicle::where("slug","${v.slug}")->count();`);
    is(gone === '0', 'the test submission is removed again', gone);
  }
}

await b.close();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
