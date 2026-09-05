// tools/audit/suites/reviews-speed.mjs — promoted from the session scratchpad. Run: node reviews-speed.mjs [outDir]
// Fails without the fix it encodes. See ../README.md.
import p from 'puppeteer'; import { readdirSync } from 'fs';
import { mkdirSync } from 'fs';
const OUT = process.argv[2] || new URL('../out', import.meta.url).pathname; mkdirSync(OUT, { recursive: true });
const dir=`${process.env.HOME}/.cache/puppeteer/chrome`;
const ex=`${dir}/${readdirSync(dir).filter(d=>d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;
const b=await p.launch({executablePath:ex,args:['--no-sandbox','--ignore-certificate-errors','--host-resolver-rules=MAP v2design.ivmotorclass.com 127.0.0.1']});
const pg=await b.newPage(); await pg.setViewport({width:1440,height:900});
await pg.goto('https://v2design.ivmotorclass.com/inicio',{waitUntil:'networkidle2'});
await pg.evaluate(()=>{const s=document.getElementById('reviews'); scrollTo(0,s.getBoundingClientRect().top+scrollY-80);});
await new Promise(r=>setTimeout(r,2500));   // let v settle at target
const r = await pg.evaluate(()=>new Promise(res=>{
  const r=document.getElementById('fb-rail');
  let n=0; const t0=performance.now(), x0=r.scrollLeft;
  function f(){ n++; if (performance.now()-t0 < 3000) requestAnimationFrame(f);
    else res({frames:n, fps:+(n/((performance.now()-t0)/1000)).toFixed(1),
              px:+(r.scrollLeft-x0).toFixed(1),
              pxPerSec:+((r.scrollLeft-x0)/((performance.now()-t0)/1000)).toFixed(1)}); }
  requestAnimationFrame(f);
}));
console.log({frames:r.frames, fps:r.fps, pxPerSec:Math.abs(r.pxPerSec),
  direction:r.pxPerSec<0?'left → right (content moves right) ✓':'right → left ✗',
  expected:'one 3s sample of the profile — between 24 (a card in the middle) and 110 (between cards) px/s'});
await b.close();
