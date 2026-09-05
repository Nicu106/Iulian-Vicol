// tools/audit/suites/reviews-seam.mjs — promoted from the session scratchpad. Run: node reviews-seam.mjs [outDir]
// Fails without the fix it encodes. See ../README.md.
import p from 'puppeteer'; import { readdirSync } from 'fs';
import { mkdirSync } from 'fs';
const OUT = process.argv[2] || new URL('../out', import.meta.url).pathname; mkdirSync(OUT, { recursive: true });
const dir=`${process.env.HOME}/.cache/puppeteer/chrome`;
const ex=`${dir}/${readdirSync(dir).filter(d=>d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;
const b=await p.launch({executablePath:ex,args:['--no-sandbox','--ignore-certificate-errors','--host-resolver-rules=MAP v2design.ivmotorclass.com 127.0.0.1']});
const pg=await b.newPage(); await pg.setViewport({width:1440,height:900});
await pg.goto('https://v2design.ivmotorclass.com/inicio',{waitUntil:'networkidle2'});
await new Promise(r=>setTimeout(r,2500));
console.log(await pg.evaluate(()=>{
  const rail=document.getElementById('fb-rail');
  const rows=[...rail.querySelectorAll('.hm-fb__row')];
  const cells=[...rail.querySelectorAll('.fb')];
  const n=cells.length/2;
  const rowW=rows[0].scrollWidth;
  const stride=cells[n].offsetLeft-cells[0].offsetLeft;
  // every corresponding pair must be exactly one stride apart
  let worst=0;
  for(let i=0;i<n;i++) worst=Math.max(worst, Math.abs((cells[n+i].offsetLeft-cells[i].offsetLeft)-stride));
  return {rowW, stride, seamError: +(stride-rowW).toFixed(2), worstPairDrift: +worst.toFixed(2),
    gapBetweenRows: cells[n].offsetLeft - (cells[n-1].offsetLeft + cells[n-1].offsetWidth),
    gapInsideRow: cells[1].offsetLeft - (cells[0].offsetLeft + cells[0].offsetWidth)};
}));
await b.close();
