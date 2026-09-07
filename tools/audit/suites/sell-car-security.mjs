// tools/audit/suites/sell-car-security.mjs
// Attacks the only unauthenticated POST on the site that writes a row and takes
// files. Every case here was run against the form before the defence existed.
// Exits 1. Cleans up every row it creates.
import p from 'puppeteer';
import { readdirSync, writeFileSync, unlinkSync } from 'fs';
import { execSync } from 'child_process';

const HOST = process.env.AUDIT_HOST || 'v2design.ivmotorclass.com';
const B    = `https://${HOST}`;
const ROOT = '/var/www/motorclass-v2';
const dir  = `${process.env.HOME}/.cache/puppeteer/chrome`;
const exe  = `${dir}/${readdirSync(dir).filter(d => d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;
const sh = (c) => execSync(c, { shell: '/bin/bash' }).toString().trim();
const tinker = (code) => execSync(`cd ${ROOT} && php artisan tinker --execute="$TINK" 2>/dev/null`,
  { env: { ...process.env, TINK: code } }).toString().trim();
const rows = () => +tinker('echo \\App\\Models\\Vehicle::where("status","pending")->count();');
const clearLimiter = () => execSync(`cd ${ROOT} && php artisan cache:clear >/dev/null 2>&1`);

let bad = 0;
const ok   = (t, d) => console.log(`✓ ${t}${d ? '  — ' + d : ''}`);
const fail = (t, d) => { bad++; console.log(`✗ ${t}${d ? '  — ' + d : ''}`); };
const is   = (c, t, d) => (c ? ok : fail)(t, d);

const before = rows();
const browser = await p.launch({ executablePath: exe,
  args: ['--no-sandbox', '--ignore-certificate-errors', `--host-resolver-rules=MAP ${HOST} 127.0.0.1`] });

/** Fill the visible form. Returns the page, left on /vende, not yet submitted. */
async function form(pg, over = {}) {
  await pg.goto(`${B}/vende`, { waitUntil: 'networkidle0' });
  await pg.click('.sl-marque:nth-child(1)');
  await pg.type('input[name=model]', over.model ?? 'Golf');
  await pg.select('select[name=year]', '2018');
  await pg.type('input[name=mileage]', '90000');
  await pg.type('input[name=seller_name]', over.name ?? 'Prueba Seguridad');
  await pg.type('input[name=seller_phone]', '600 000 000');
  if (over.description) { await pg.type('textarea[name=description]', over.description); }
  if (over.honeypot) { await pg.evaluate(v => { document.getElementById('apellido_2').value = v; }, over.honeypot); }
  if (over.stamp !== undefined) { await pg.evaluate(v => { document.querySelector('input[name=t]').value = v; }, over.stamp); }
  return pg;
}
const submit = async (pg) => {
  await pg.evaluate(() => document.getElementById('sl-form').submit());
  await pg.waitForNavigation({ waitUntil: 'networkidle0', timeout: 60000 }).catch(() => {});
  return { url: pg.url(), body: await pg.evaluate(() => document.body.innerText.slice(0, 400)) };
};
const accepted = (r) => r.url.includes('enviado=1');

/* ---- 1. the honeypot ---------------------------------------------------- */
{
  clearLimiter();
  const pg = await browser.newPage();
  await form(pg, { honeypot: 'http://spam.example' });
  await new Promise(r => setTimeout(r, 4500));
  const r = await submit(pg);
  is(!accepted(r) && rows() === before, 'a filled honeypot is refused and writes nothing', r.url.split(HOST)[1]);
  await pg.close();
}

/* ---- 2. faster than a person can read ----------------------------------- */
{
  clearLimiter();
  const pg = await browser.newPage();
  await form(pg);
  const r = await submit(pg);                 // no wait at all
  is(!accepted(r) && rows() === before, 'a submission under four seconds is refused');
  await pg.close();
}

/* ---- 3. no clock, and a forged one -------------------------------------- */
for (const [stamp, what] of [['', 'missing'], ['not-an-encrypted-value', 'forged']]) {
  clearLimiter();
  const pg = await browser.newPage();
  await form(pg, { stamp });
  await new Promise(r => setTimeout(r, 4500));
  const r = await submit(pg);
  is(!accepted(r) && rows() === before, `a ${what} timestamp is refused`);
  await pg.close();
}

/* ---- 4. the shape of spam ------------------------------------------------ */
for (const [text, what] of [
  ['Compra viagra barato en http://spam.example ahora', 'a link'],
  ['<a href="x">click</a> [url=y]aqui[/url]', 'markup'],
  ['Продам машину, звоните сейчас', 'Cyrillic'],
]) {
  clearLimiter();
  const pg = await browser.newPage();
  await form(pg, { description: text });
  await new Promise(r => setTimeout(r, 4500));
  const r = await submit(pg);
  is(!accepted(r) && rows() === before, `${what} in the text is refused`);
  await pg.close();
}

/* ---- 5. no CSRF token ---------------------------------------------------- */
{
  clearLimiter();
  const out = sh(`curl -sk --resolve ${HOST}:443:127.0.0.1 -o /dev/null -w '%{http_code}' -X POST ${B}/vende -F 'brand=bmw' -F 'model=X' -F 'year=2018' -F 'mileage=1' -F 'seller_name=X' -F 'seller_phone=600000000'`);
  is(out === '419' && rows() === before, 'a post without a CSRF token is rejected', `HTTP ${out}`);
}

/* ---- 6. the rate limit ---------------------------------------------------
   With a REAL session and token. Without one every attempt is 419 — CSRF sits in
   the web group and runs before the route's throttle — which is the cheaper
   rejection and the right order, but it means a tokenless loop never reaches the
   limiter and proves nothing about it. A bot that scrapes the token gets here. */
{
  clearLimiter();
  const jar = '/tmp/sec-jar.txt';
  sh(`rm -f ${jar}`);
  const page = sh(`curl -sk --resolve ${HOST}:443:127.0.0.1 -c ${jar} ${B}/vende`);
  const token = (page.match(/name="_token" value="([^"]+)"/) || [])[1];
  is(!!token, 'the form issues a CSRF token to a fresh visitor');
  const codes = [];
  for (let i = 0; i < 6; i++) {
    codes.push(sh(`curl -sk --resolve ${HOST}:443:127.0.0.1 -b ${jar} -c ${jar} -o /dev/null -w '%{http_code}' -X POST ${B}/vende -F '_token=${token}' -F 'brand=bmw'`));
  }
  sh(`rm -f ${jar}`);
  is(codes.includes('429'), 'the fifth attempt in an hour from one IP is throttled', codes.join(' '));
  is(rows() === before, 'and none of them wrote a row');
  clearLimiter();
}

/* ---- 7. a script dressed as a photograph --------------------------------- */
{
  clearLimiter();
  const evil = '/tmp/evil.php.jpg';
  writeFileSync(evil, '<?php system($_GET["c"]); ?>');
  const pg = await browser.newPage();
  await form(pg);
  await (await pg.$('#sl-files')).uploadFile(evil);
  await new Promise(r => setTimeout(r, 4500));
  const r = await submit(pg);
  is(!accepted(r) && rows() === before, 'a PHP file renamed .jpg is refused as a photograph');
  unlinkSync(evil);
  await pg.close();
}

/* ---- 8. nothing under /storage executes ---------------------------------- */
{
  sh(`mkdir -p ${ROOT}/storage/app/public/_sec && printf '<?php echo "EXECUTED"; ?>' > ${ROOT}/storage/app/public/_sec/p.php`);
  const code = sh(`curl -sk --resolve ${HOST}:443:127.0.0.1 -o /dev/null -w '%{http_code}' ${B}/storage/_sec/p.php`);
  const body = sh(`curl -sk --resolve ${HOST}:443:127.0.0.1 ${B}/storage/_sec/p.php | head -c 40`);
  is(code === '403' && !body.includes('EXECUTED'), 'a .php file under /storage is never executed', `HTTP ${code}`);
  sh(`rm -rf ${ROOT}/storage/app/public/_sec`);
}

/* ---- 9. and an honest seller still gets through --------------------------- */
{
  clearLimiter();
  const MARK = 'SEC-OK-' + Date.now().toString(36);
  const pg = await browser.newPage();
  await form(pg, { model: 'Golf ' + MARK, description: 'Un dueño, siempre en garaje. Un roce en la puerta.' });
  const files = sh(`ls ${ROOT}/storage/app/public/vehicles/*/*.jpg | head -1`).split('\n');
  await (await pg.$('#sl-files')).uploadFile(...files);
  await new Promise(r => setTimeout(r, 4500));
  const r = await submit(pg);
  is(accepted(r) && rows() === before + 1, 'an honest submission is still accepted', r.url.split(HOST)[1]);
  await pg.close();
  tinker(`foreach(\\App\\Models\\Vehicle::where("model","like","%${MARK}%")->get() as $v){ $d=storage_path("app/public/sell-cars/".$v->slug); if(is_dir($d)){foreach(glob($d."/*") as $f) unlink($f); rmdir($d);} $v->delete(); } echo "ok";`);
  is(rows() === before, 'and the test row is removed again', `${rows()} pending`);
}

clearLimiter();
await browser.close();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
