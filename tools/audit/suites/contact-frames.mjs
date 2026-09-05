// tools/audit/suites/contact-frames.mjs — promoted from the session scratchpad. Run: node contact-frames.mjs [outDir]
// Fails without the fix it encodes. See ../README.md.
import p from 'puppeteer'; import { readdirSync } from 'fs';
import { mkdirSync } from 'fs';
const OUT = process.argv[2] || new URL('../out', import.meta.url).pathname; mkdirSync(OUT, { recursive: true });
const dir=`${process.env.HOME}/.cache/puppeteer/chrome`;
const ex=`${dir}/${readdirSync(dir).filter(d=>d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;
const b=await p.launch({executablePath:ex,args:['--no-sandbox','--ignore-certificate-errors','--host-resolver-rules=MAP v2design.ivmotorclass.com 127.0.0.1']});
const pg=await b.newPage(); await pg.setViewport({width:1440,height:900});
await pg.goto('https://v2design.ivmotorclass.com/contacto',{waitUntil:'networkidle2'});
await new Promise(r=>setTimeout(r,1500));
const out = await pg.evaluate(async ()=>{
  const a=document.getElementById('assemble'), vh=innerHeight, vw=innerWidth;
  const top=document.getElementById('panel-a').getBoundingClientRect().top-a.getBoundingClientRect().top;
  const RUN=Math.max(1,(top-vh*0.24)/0.84);
  const rows=[];
  for(let k=0;k<=26;k++){
    const t=k/26*1.04;
    scrollTo(0,a.getBoundingClientRect().top+scrollY+RUN*t);
    await new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r)));
    const cols=[...document.querySelectorAll('.ct-col')];
    let strip=0, photo=0;
    for(const c of cols){
      const r=c.getBoundingClientRect();
      const w=Math.max(0,Math.min(r.right,vw)-Math.max(r.left,0));
      const h=Math.max(0,Math.min(r.bottom,vh)-Math.max(r.top,0));
      const area=w*h; strip+=area;
      photo+=area*(+getComputedStyle(c.querySelector('.ct-col__pic')).opacity);
    }
    const cut=+getComputedStyle(document.getElementById('cut')).opacity;
    const stage=+getComputedStyle(document.getElementById('open')).opacity;
    rows.push({t:+t.toFixed(3),
      strip:+(strip/(vw*vh)).toFixed(3),
      photo:+(photo/(vw*vh)).toFixed(3),
      cut:+cut.toFixed(2), stage:+stage.toFixed(2)});
  }
  return rows;
});
console.log('   p     strips  photo   flat    cut   stage   what the screen is');
let empty=0;
for(const r of out){
  const flat=+(1-r.strip).toFixed(3);
  const dead = r.photo<0.02 && r.stage>0.5;
  if(dead) empty++;
  console.log(`  ${r.t.toFixed(2)}   ${r.strip.toFixed(3)}  ${r.photo.toFixed(3)}  ${flat.toFixed(3)}  ${r.cut.toFixed(2)}  ${r.stage.toFixed(2)}   ${dead?'—— no image at all ——':''}`);
}
console.log(`\n  frames with no photograph on screen while the stage is still up: ${empty}/${out.length} = ${(empty/out.length*100).toFixed(0)}% of the scroll`);
await b.close();
