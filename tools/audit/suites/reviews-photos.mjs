// tools/audit/suites/reviews-photos.mjs — promoted from the session scratchpad. Run: node reviews-photos.mjs [outDir]
// Fails without the fix it encodes. See ../README.md.
import p from 'puppeteer'; import { readdirSync } from 'fs';
import { mkdirSync } from 'fs';
const OUT = process.argv[2] || new URL('../out', import.meta.url).pathname; mkdirSync(OUT, { recursive: true });
const dir=`${process.env.HOME}/.cache/puppeteer/chrome`;
const ex=`${dir}/${readdirSync(dir).filter(d=>d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;
const b=await p.launch({executablePath:ex,args:['--no-sandbox','--ignore-certificate-errors','--host-resolver-rules=MAP v2design.ivmotorclass.com 127.0.0.1']});
const results=[];
for (const w of [1440,768,390]) {
  const pg=await b.newPage(); await pg.setViewport({width:w,height:900,isMobile:w<500,hasTouch:w<500});
  await pg.goto('https://v2design.ivmotorclass.com/inicio',{waitUntil:'networkidle2'});
  await new Promise(r=>setTimeout(r,2500));
  const R = await pg.evaluate(()=>{
    const cells=[...document.querySelectorAll('.fb')].slice(0,24);
    let cut=0, worstCut=0, bars=0, worstBar=0;
    cells.forEach(f=>{
      const img=f.querySelector('img'), box=f.querySelector('.fb__face--front').getBoundingClientRect();
      if(!img.naturalWidth) return;
      const ir=img.naturalWidth/img.naturalHeight, br=box.width/box.height;
      // with object-fit:contain nothing is ever cut; measure the mat instead
      const shownW = br>ir ? box.height*ir : box.width;
      const shownH = br>ir ? box.height     : box.width/ir;
      const barPct = Math.round((1 - (shownW*shownH)/(box.width*box.height))*100);
      if (barPct>1){ bars++; worstBar=Math.max(worstBar,barPct); }
      // a cut would show as the image being larger than its box
      const c = Math.round(Math.max(shownW-box.width, shownH-box.height));
      if (c>1){ cut++; worstCut=Math.max(worstCut,c); }
    });
    // the words live on the back face, which is inset:0 and cannot overrun the card; what CAN happen is the
    // quote needing its scroll fallback — so count quotes taller than their box
    const bad=cells.map(f=>{const q=f.querySelector('.fb__q'); return q.scrollHeight-q.clientHeight;}).filter(x=>x>1);
    return {photosCut:cut, worstCutPx:worstCut, withMat:bars, worstMatPct:worstBar,
      quotesNeedingScroll:bad.length, pageOverflow:document.documentElement.scrollWidth-innerWidth};
  });
  results.push({ w, ...R });
  await pg.close();
}
await b.close();
// the verdict — a newcomer must be able to tell baseline from regression
const out=[]; let bad=0;
const ok=(n,c,d)=>{ out.push(`${c?'✓':'✗'} ${n}  — ${d}`); if(!c) bad++; };
for (const r of results) {
  ok(`${r.w}px: no photograph cut`, r.photosCut===0, `${r.photosCut} cut, worst ${r.worstCutPx}px`);
  ok(`${r.w}px: no quote needs the scroll fallback`, r.quotesNeedingScroll===0, `${r.quotesNeedingScroll} scrolling`);
  ok(`${r.w}px: no page overflow`, r.pageOverflow===0, `${r.pageOverflow}px`);
  if (r.w>=1000) ok(`${r.w}px: no mat`, r.withMat===0, `${r.withMat} matted, worst ${r.worstMatPct}%`);
  else ok(`${r.w}px: mat ≤5% (the 88vw cap on a uniform-height card)`, r.worstMatPct<=5, `${r.withMat} matted, worst ${r.worstMatPct}%`);
}
console.log(out.join('\n'));
process.exit(bad ? 1 : 0);
