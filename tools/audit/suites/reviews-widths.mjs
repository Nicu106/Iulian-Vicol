// tools/audit/suites/reviews-widths.mjs — promoted from the session scratchpad. Run: node reviews-widths.mjs [outDir]
// Fails without the fix it encodes. See ../README.md.
import p from 'puppeteer'; import { readdirSync } from 'fs';
import { mkdirSync } from 'fs';
const OUT = process.argv[2] || new URL('../out', import.meta.url).pathname; mkdirSync(OUT, { recursive: true });
const dir=`${process.env.HOME}/.cache/puppeteer/chrome`;
const ex=`${dir}/${readdirSync(dir).filter(d=>d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;
const b=await p.launch({executablePath:ex,args:['--no-sandbox','--ignore-certificate-errors','--host-resolver-rules=MAP v2design.ivmotorclass.com 127.0.0.1']});
for (const w of [1920,1440,1200,1024]) {
  const pg=await b.newPage(); await pg.setViewport({width:w,height:900});
  await pg.goto('https://v2design.ivmotorclass.com/inicio',{waitUntil:'networkidle2'});
  await new Promise(r=>setTimeout(r,1500));
  console.log(w, await pg.evaluate(()=>{
    const cells=[...document.querySelectorAll('.fb')];
    let worst=0, bad=0;
    cells.forEach(f=>{
      const want=parseFloat(getComputedStyle(f).getPropertyValue('--cell'));
      const got=f.getBoundingClientRect().width;
      const d=Math.abs(got-want); if(d>1.5){bad++; worst=Math.max(worst,Math.round(d));}
    });
    const R=cells.map(f=>f.getBoundingClientRect());
    let ov=0; for(let i=0;i<R.length-1;i++) if(Math.min(R[i].right,R[i+1].right)-Math.max(R[i].left,R[i+1].left)>1) ov++;
    return {cardsNotAtTheirWidth:bad, worstPx:worst, overlappingPairs:ov,
      rowShrunk: Math.round(document.querySelector('.hm-fb__row').getBoundingClientRect().width)};
  }));
  await pg.close();
}
await b.close();
