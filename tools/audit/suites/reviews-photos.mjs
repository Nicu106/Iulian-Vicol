// tools/audit/suites/reviews-photos.mjs — promoted from the session scratchpad. Run: node reviews-photos.mjs [outDir]
// Fails without the fix it encodes. See ../README.md.
import p from 'puppeteer'; import { readdirSync } from 'fs';
import { mkdirSync } from 'fs';
const OUT = process.argv[2] || new URL('../out', import.meta.url).pathname; mkdirSync(OUT, { recursive: true });
const dir=`${process.env.HOME}/.cache/puppeteer/chrome`;
const ex=`${dir}/${readdirSync(dir).filter(d=>d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;
const b=await p.launch({executablePath:ex,args:['--no-sandbox','--ignore-certificate-errors','--host-resolver-rules=MAP v2design.ivmotorclass.com 127.0.0.1']});
for (const w of [1440,768,390]) {
  const pg=await b.newPage(); await pg.setViewport({width:w,height:900,isMobile:w<500,hasTouch:w<500});
  await pg.goto('https://v2design.ivmotorclass.com/inicio',{waitUntil:'networkidle2'});
  await new Promise(r=>setTimeout(r,2500));
  console.log(w, await pg.evaluate(()=>{
    const cells=[...document.querySelectorAll('.fb')].slice(0,24);
    let cut=0, worstCut=0, bars=0, worstBar=0;
    cells.forEach(f=>{
      const img=f.querySelector('img'), box=f.querySelector('.fb__ph').getBoundingClientRect();
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
    const bad=cells.map(f=>Math.round(f.querySelector('.fb__t').getBoundingClientRect().bottom-f.getBoundingClientRect().bottom)).filter(x=>x>1);
    return {photosCut:cut, worstCutPx:worstCut, withMat:bars, worstMatPct:worstBar,
      textOverflowing:bad.length, pageOverflow:document.documentElement.scrollWidth-innerWidth};
  }));
  await pg.close();
}
await b.close();
