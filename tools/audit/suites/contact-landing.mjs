// tools/audit/suites/contact-landing.mjs — promoted from the session scratchpad. Run: node contact-landing.mjs [outDir]
// Fails without the fix it encodes. See ../README.md.
import p from 'puppeteer'; import { readdirSync } from 'fs';
import { mkdirSync } from 'fs';
const OUT = process.argv[2] || new URL('../out', import.meta.url).pathname; mkdirSync(OUT, { recursive: true });
const dir=`${process.env.HOME}/.cache/puppeteer/chrome`;
const ex=`${dir}/${readdirSync(dir).filter(d=>d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;
const b=await p.launch({executablePath:ex,args:['--no-sandbox','--ignore-certificate-errors','--host-resolver-rules=MAP v2design.ivmotorclass.com 127.0.0.1']});
const pg=await b.newPage(); await pg.setViewport({width:1440,height:900});
const errs=[]; pg.on('pageerror',e=>errs.push(String(e))); pg.on('console',m=>{if(m.type()==='error')errs.push(m.text())});
await pg.goto('https://v2design.ivmotorclass.com/contacto',{waitUntil:'networkidle0'});
await new Promise(r=>setTimeout(r,900));

const probe = async (p_target) => pg.evaluate(async (pt) => {
  const asm=document.getElementById('assemble');
  const run = (() => { // recover RUN the same way the page does
    const vh=window.innerHeight;
    const top=document.getElementById('panel-a').getBoundingClientRect().top - asm.getBoundingClientRect().top;
    return Math.max(1,(top - vh*0.24)/0.84);
  })();
  const y = asm.getBoundingClientRect().top + window.scrollY + run*pt;
  window.scrollTo(0, y);
  await new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r)));
  const cols=[...document.querySelectorAll('.ct-col')];
  const rects=cols.map(c=>c.getBoundingClientRect());
  const pa=document.getElementById('panel-a').getBoundingClientRect();
  const pb=document.getElementById('panel-b').getBoundingClientRect();
  const grp=(a,z)=>{const r=rects.slice(a,z);return {l:Math.min(...r.map(x=>x.left)),t:Math.min(...r.map(x=>x.top)),
    r:Math.max(...r.map(x=>x.right)),b:Math.max(...r.map(x=>x.bottom))};};
  const st=getComputedStyle(document.getElementById('open'));
  return {
    run:Math.round(run),
    stage:{op:+st.opacity, vis:st.visibility},
    picOp:cols.map(c=>+getComputedStyle(c.querySelector('.ct-col__pic')).opacity.slice(0,4)),
    gap:Math.round(rects[1].left-rects[0].right),
    A:grp(0,6), B:grp(6,12),
    pa:{l:pa.left,t:pa.top,r:pa.right,b:pa.bottom},
    pb:{l:pb.left,t:pb.top,r:pb.right,b:pb.bottom},
    seam:+getComputedStyle(cols[2].querySelector('.ct-col__edge--r')).opacity,
    endEdge:+getComputedStyle(cols[5].querySelector('.ct-col__edge--r')).opacity,
    words:+getComputedStyle(document.getElementById('open-words')).opacity,
    drawn:document.getElementById('far').classList.contains('is-drawn'),
  };
}, p_target);

const f=n=>Math.round(n);
for (const t of [0, 0.18, 0.35, 0.55, 0.80, 0.94, 0.99]) {
  const r = await probe(t);
  const dA = [f(r.A.l-r.pa.l), f(r.A.t-r.pa.t), f(r.A.r-r.pa.r), f(r.A.b-r.pa.b)];
  const dB = [f(r.B.l-r.pb.l), f(r.B.t-r.pb.t), f(r.B.r-r.pb.r), f(r.B.b-r.pb.b)];
  console.log(`p=${t.toFixed(2)} RUN=${r.run} stage=${r.stage.op.toFixed(2)}/${r.stage.vis} words=${r.words.toFixed(2)} gap=${r.gap}px `+
    `pic[0,5,11]=${[r.picOp[0],r.picOp[5],r.picOp[11]].map(v=>v.toFixed(2))} seam=${r.seam.toFixed(2)} end=${r.endEdge.toFixed(2)} drawn=${r.drawn}`);
  console.log(`         ΔA(l,t,r,b)=${dA}  ΔB=${dB}`);
}
const ov = await pg.evaluate(()=>document.documentElement.scrollWidth - window.innerWidth);
console.log('overflow@1440:', ov, '| errors:', errs.length ? errs : 'none');
await b.close();
