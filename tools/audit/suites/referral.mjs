// tools/audit/suites/referral.mjs
// Recommendations without accounts, end to end on the dev host.
//
// The rules (App\Support\Referral): a person asks for a link with their own name
// and phone and nothing else; the same phone always gets the same link; opening
// a link remembers the code for 90 days and the FIRST link opened wins; while it
// is remembered every WhatsApp link on the site carries the code; a reward
// exists only for a car sold, never for the person's own purchase. The traps on
// the form are the same as /vende's.
//
// Leaves nothing behind: its referrers use two test phones and are deleted
// before and after, and the one test car it creates is deleted.
import p from 'puppeteer';
import { readdirSync } from 'fs';
import { execFileSync } from 'child_process';

const HOST = process.env.AUDIT_HOST || 'v2design.ivmotorclass.com';
const B    = `https://${HOST}`;
const ROOT = '/var/www/motorclass-v2';
const dir  = `${process.env.HOME}/.cache/puppeteer/chrome`;
const exe  = `${dir}/${readdirSync(dir).filter(d => d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;

let bad = 0;
const ok   = (t, d) => console.log(`✓ ${t}${d ? '  — ' + d : ''}`);
const fail = (t, d) => { bad++; console.log(`✗ ${t}${d ? '  — ' + d : ''}`); };
const is   = (c, t, d) => (c ? ok : fail)(t, d);

const php = code => execFileSync('php', ['artisan', 'tinker', '--execute', code], { cwd: ROOT })
  .toString().trim().split('\n').pop();

const A = { name: 'Prueba Auditoría', phone: '600 000 111', norm: '34600000111' };
const Bp = { name: 'Otra Prueba', phone: '+34 600 000 222', norm: '34600000222' };
const cleanup = () => php(`App\\Models\\Vehicle::where('slug','audit-referral-test')->delete();
  App\\Models\\Referrer::whereIn('phone',['${A.norm}','${Bp.norm}'])->delete(); echo 'clean';`);
const countFor = norm => Number(php(`echo App\\Models\\Referrer::where('phone','${norm}')->count();`));

cleanup();

const browser = await p.launch({ executablePath: exe,
  args: ['--no-sandbox', '--ignore-certificate-errors', `--host-resolver-rules=MAP ${HOST} 127.0.0.1`] });

// every visitor gets their own cookie jar
const fresh = async (w = 390) => {
  const ctx = await browser.createBrowserContext();
  const pg = await ctx.newPage();
  await pg.setViewport({ width: w, height: 844 });
  return { ctx, pg };
};

async function submit(pg, who, { wait = 3600, trap = false } = {}) {
  await pg.goto(`${B}/recomienda`, { waitUntil: 'networkidle0' });
  if (wait) await new Promise(r => setTimeout(r, wait));
  await pg.type('input[name=name]', who.name);
  await pg.type('input[name=phone]', who.phone);
  if (trap) await pg.$eval('#apellido_2', el => { el.value = 'bot'; });
  await Promise.all([pg.waitForNavigation({ waitUntil: 'networkidle0' }), pg.click('.rf-form button[type=submit]')]);
}

/* --- the page ------------------------------------------------------------- */
{
  const { ctx, pg } = await fresh();
  const res = await pg.goto(`${B}/recomienda`, { waitUntil: 'networkidle0' });
  const m = await pg.evaluate(() => ({
    title: document.title,
    fields: [...document.querySelectorAll('.rf-form input')].map(i => i.name).filter(Boolean),
    footer: !!document.querySelector('.mc-foot a[href="/recomienda"]'),
    asksFriend: /amigo.*tel|tel.*amigo/i.test([...document.querySelectorAll('.rf-form label')].map(l => l.textContent).join(' ')),
  }));
  is(res.status() === 200 && /Recomienda/.test(m.title), '/recomienda is its own page, not the holding page', m.title);
  is(['name', 'phone'].every(f => m.fields.includes(f)), 'it asks for a name and a phone', m.fields.join(', '));
  is(m.fields.includes('apellido_2') && m.fields.includes('t'), 'with the trap and the signed clock');
  is(!m.asksFriend, "and never for the friend's data");
  is(m.footer, 'the footer links to it');
  await ctx.close();
}

/* --- the traps ------------------------------------------------------------- */
{
  const { ctx, pg } = await fresh();
  await submit(pg, A, { wait: 0 });
  const err = await pg.$eval('.mc-err', e => e.textContent).catch(() => '');
  is(err !== '' && countFor(A.norm) === 0, 'posted faster than a person can: refused, nothing stored', err);
  await ctx.close();
}
{
  const { ctx, pg } = await fresh();
  await submit(pg, A, { trap: true });
  const err = await pg.$eval('.mc-err', e => e.textContent).catch(() => '');
  is(err !== '' && countFor(A.norm) === 0, 'the hidden field filled: refused, nothing stored', err);
  await ctx.close();
}

/* --- asking for a link ----------------------------------------------------- */
let codeA = null, codeB = null;
{
  const { ctx, pg } = await fresh();
  await submit(pg, A);
  const m = await pg.evaluate(() => {
    const link = (document.getElementById('rf-link')?.textContent || '').trim();
    const wa = document.querySelector('.rf-go a[href*="wa.me"]');
    return { link, anchor: location.hash, wa: wa?.getAttribute('href') || '', noRef: wa?.hasAttribute('data-no-ref') };
  });
  codeA = (m.link.match(/\/r\/([A-Z0-9]{4,16})$/) || [])[1] || null;
  is(!!codeA, 'a person gets a personal link', m.link);
  is(countFor(A.norm) === 1, 'stored once, under the normalised phone', A.norm);
  is(/^PRUEBA|^PRUEB/.test(codeA || ''), 'the code starts with their name, so it can be said aloud', codeA);
  is(m.wa.includes(encodeURIComponent(m.link)) && m.noRef, 'with a WhatsApp button that sends that link, and only that link');
  const clip = await pg.$eval('#rf-link', e => e.scrollWidth > e.clientWidth + 1);
  is(!clip, 'the link is shown whole, not clipped', m.link);
  await ctx.close();
}
{
  const { ctx, pg } = await fresh();
  await submit(pg, { ...A, name: 'Otro Nombre', phone: '+34 600000111' });
  const link = await pg.$eval('#rf-link', e => e.textContent.trim()).catch(() => '');
  is(link.endsWith('/r/' + codeA) && countFor(A.norm) === 1, 'the same phone, written differently, gets the same link back', link);
  await ctx.close();
}
{
  const { ctx, pg } = await fresh();
  await submit(pg, Bp);
  const link = await pg.$eval('#rf-link', e => e.textContent.trim()).catch(() => '');
  codeB = (link.match(/\/r\/([A-Z0-9]{4,16})$/) || [])[1] || null;
  is(!!codeB && codeB !== codeA, 'another phone, another link', codeB);
  await ctx.close();
}

/* --- the friend ------------------------------------------------------------ */
{
  const { ctx, pg } = await fresh(1440);
  await pg.goto(`${B}/r/${codeA.toLowerCase()}`, { waitUntil: 'networkidle0' });
  const url = pg.url();
  const jar = await pg.cookies();
  const c = jar.find(k => k.name === 'mc_ref');
  const days = c ? (c.expires * 1000 - Date.now()) / 864e5 : 0;
  is(/\/inicio$/.test(url), 'opening a link lands on the home page', url);
  is(c && c.value === codeA, 'and remembers the code (typed in lower case, stored as the code)', c?.value);
  is(c && !c.httpOnly && c.sameSite === 'Lax', 'in a readable, SameSite=Lax cookie');
  is(days > 89 && days <= 90.01, 'for 90 days', days.toFixed(2));

  const wa = await pg.evaluate(() => [...document.querySelectorAll('a[href*="wa.me"]')]
    .map(a => new URL(a.href).searchParams.get('text') || ''));
  is(wa.length > 0 && wa.every(t => t.includes(codeA)), 'every WhatsApp link on the page now carries the code', `${wa.length} links`);

  await pg.goto(`${B}/r/${codeB}`, { waitUntil: 'networkidle0' });
  const after = (await pg.cookies()).find(k => k.name === 'mc_ref');
  is(after && after.value === codeA, 'a second link opened later does not take the visitor over', after?.value);

  await pg.goto(`${B}/contacto`, { waitUntil: 'networkidle0' });
  const note = await pg.evaluate(() => window.mcRefNote || '');
  is(note.includes(codeA), 'the contact page, which opens WhatsApp from script, has the code to add', note.trim());

  const visits = php(`echo App\\Models\\Referrer::where('code','${codeA}')->first()->visits()->count().','.App\\Models\\Referrer::where('code','${codeB}')->first()->visits()->count();`);
  is(visits === '1,1', 'both openings are counted, once each', visits);
  await ctx.close();
}
{
  const { ctx, pg } = await fresh();
  const r1 = await pg.goto(`${B}/r/NOPE9999`, { waitUntil: 'networkidle0' });
  const r2 = await pg.goto(`${B}/r/%3Cx%3E`, { waitUntil: 'networkidle0' });
  const jar = await pg.cookies();
  is(/\/inicio$/.test(pg.url()) && r1.status() === 200 && r2.status() < 500 && !jar.some(k => k.name === 'mc_ref'),
     'an unknown or malformed code lands on the home page and remembers nothing');
  await ctx.close();
}

/* --- rewards --------------------------------------------------------------- */
{
  const out = php(`
    $a = App\\Models\\Referrer::where('code','${codeA}')->first();
    $v = App\\Models\\Vehicle::create(['slug'=>'audit-referral-test','brand'=>'Audit','model'=>'Test','title'=>'Audit Test','year'=>2020,'price'=>1,'status'=>'available','referred_by'=>$a->id]);
    $has = fn() => App\\Models\\ReferralReward::where('vehicle_id',$v->id)->value('status') ?? 'none';
    $r = [];
    $r[] = $has();                                         // not sold yet
    $v->status='sold'; $v->save(); $r[] = $has();          // sold with a referrer
    $v->referred_by=null; $v->save(); $r[] = $has();       // referrer removed
    $v->referred_by=$a->id; $v->buyer_phone='+34 600 000 111'; $v->save(); $r[] = $has();   // buyer is the referrer
    $v->buyer_phone=null; $v->save(); $r[] = $has();       // a different buyer again
    App\\Models\\ReferralReward::where('vehicle_id',$v->id)->update(['status'=>'paid']);
    $v->status='available'; $v->save(); $r[] = $has();     // sale undone after it was paid
    echo implode(',', $r);`);
  const [unsold, sold, noRef, self, other, paidKept] = out.split(',');
  is(unsold === 'none', 'no reward while the car is not sold', unsold);
  is(sold === 'pending', 'sold with "Vino de parte de": a pending reward', sold);
  is(noRef === 'none', 'the referrer removed: the pending reward goes', noRef);
  is(self === 'none', "never for a person's own purchase (buyer's phone = referrer's)", self);
  is(other === 'pending', 'a real buyer again: pending again', other);
  is(paidKept === 'paid', 'a reward already paid is left alone when the sale is undone', paidKept);
}

await browser.close();
cleanup();
console.log(bad ? `\n${bad} failing` : '\nall good');
process.exit(bad ? 1 : 0);
