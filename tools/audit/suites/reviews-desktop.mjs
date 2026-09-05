// tools/audit/suites/reviews-desktop.mjs — promoted from the session scratchpad. Run: node reviews-desktop.mjs [outDir]
// Fails without the fix it encodes. See ../README.md.
import p from 'puppeteer'; import { readdirSync } from 'fs';
import { mkdirSync } from 'fs';
const OUT = process.argv[2] || new URL('../out', import.meta.url).pathname; mkdirSync(OUT, { recursive: true });
const dir=`${process.env.HOME}/.cache/puppeteer/chrome`;
const ex=`${dir}/${readdirSync(dir).filter(d=>d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;
const b=await p.launch({executablePath:ex,args:['--no-sandbox','--ignore-certificate-errors','--host-resolver-rules=MAP v2design.ivmotorclass.com 127.0.0.1']});
const PAGE='https://v2design.ivmotorclass.com/inicio';
const out=[];
const ok=(name,cond,detail='')=>out.push(`${cond?'✓':'✗'} ${name}${detail?'  — '+detail:''}`);

// ---- desktop, motion allowed ----
{
  const pg=await b.newPage(); await pg.setViewport({width:1440,height:900});
  const errs=[]; pg.on('pageerror',e=>errs.push(String(e)));
  await pg.goto(PAGE,{waitUntil:'networkidle2'});
  await pg.evaluate(()=>{const s=document.getElementById('reviews'); scrollTo(0,s.getBoundingClientRect().top+scrollY-60);});
  await pg.mouse.move(20,20); await new Promise(r=>setTimeout(r,2500));
  const L=()=>pg.evaluate(()=>document.getElementById('fb-rail').scrollLeft);
  const centred=()=>pg.evaluate(()=>{const rail=document.getElementById('fb-rail'),mid=rail.getBoundingClientRect().left+rail.clientWidth/2;
    let best=null,bd=1e9; document.querySelectorAll('.fb').forEach(f=>{const r=f.getBoundingClientRect();const d=r.left+r.width/2-mid;if(Math.abs(d)<Math.abs(bd)){bd=d;best=f;}});
    return {name:best.querySelector('.fb__by').textContent.trim(),dist:Math.round(bd),flip:parseInt(best.style.getPropertyValue('--flip'))||0};});
  // 1. direction
  const a=await L(); await new Promise(r=>setTimeout(r,1200)); const c=await L();
  ok('travels left → right', c<a, `${(c-a).toFixed(0)}px`);
  // 2. what sits in the slow zone is text, not an edge
  let textAtCentre=0, samples=0;
  for(let i=0;i<10;i++){ const s=await centred(); if(Math.abs(s.dist)<80){samples++; if(s.flip<=10) textAtCentre++;} await new Promise(r=>setTimeout(r,700)); }
  ok('the card in the middle is the photograph (not turned)', samples>0 && textAtCentre===samples, `${textAtCentre}/${samples}`);
  // 3. buttons: styled, centred, 44px
  const btn=await pg.evaluate(()=>{const bs=[...document.querySelectorAll('.hm-fb__step .hm-fb__nav')].map(b=>b.getBoundingClientRect());
    const c=(bs[0].left+bs[1].right)/2; return {n:bs.length,w:Math.round(bs[0].width),h:Math.round(bs[0].height),centreOff:Math.round(c-innerWidth/2)};});
  ok('two buttons, 48px, centred under the row', btn.n===2&&btn.w===48&&btn.h===48&&Math.abs(btn.centreOff)<4, JSON.stringify(btn));
  // 4. a mouse click on a button does not park the row
  const before=await centred();
  await pg.evaluate(()=>{const b=document.querySelector('[data-go="1"]'); b.focus(); b.click();});
  await new Promise(r=>setTimeout(r,1300)); const after=await centred();
  ok('button brings the next photograph to the middle', after.name!==before.name && Math.abs(after.dist)<12 && after.flip<=10, `${before.name} → ${after.name} (${after.dist}px)`);
  const s1=await L(); await new Promise(r=>setTimeout(r,2500)); const s2=await L();
  ok('row resumes after a mouse press on the button', Math.abs(s2-s1)>5, `${Math.abs(s2-s1).toFixed(0)}px in 2.5s`);
  // 5. click a photograph → it comes to the middle
  const target=await pg.evaluate(()=>{const mid=innerWidth/2; let pick=null;
    document.querySelectorAll('.fb').forEach(f=>{const r=f.getBoundingClientRect(); const fl=parseInt(f.style.getPropertyValue('--flip'))||0;
      if(!pick && fl<10 && r.right>140 && r.right<mid-120) pick=f;});
    if(!pick) return null; const r=pick.getBoundingClientRect(); const x=(Math.max(r.left,140)+Math.min(r.right,mid-200))/2; return {name:pick.querySelector('.fb__by').textContent.trim(),x,y:r.top+r.height/2};});
  if(target){ await pg.mouse.click(target.x,target.y); await new Promise(r=>setTimeout(r,1300)); const got=await centred();
    ok('clicking a photograph brings it to the middle', got.name===target.name&&Math.abs(got.dist)<12&&got.flip<=10, `${target.name}: ${got.dist}px, ${got.flip}°`); }
  else ok('clicking a photograph centres it', false, 'no photo card found to click');
  // 6. geometry
  const g=await pg.evaluate(()=>{const cells=[...document.querySelectorAll('.fb')];const R=cells.map(f=>f.getBoundingClientRect());
    let ov=0;for(let i=0;i<R.length-1;i++) if(Math.min(R[i].right,R[i+1].right)-Math.max(R[i].left,R[i+1].left)>1) ov++;
    const rows=[...document.querySelectorAll('.hm-fb__row')];
    return {h:[...new Set(R.map(r=>Math.round(r.height)))],ov,seam:Math.round((cells[24].offsetLeft-cells[0].offsetLeft)-rows[0].scrollWidth),copies:rows.length,
      framed:getComputedStyle(document.querySelector('.fb__face')).borderTopWidth};});
  ok('uniform height, no overlap, exact seam, no frame', g.h.length===1&&g.ov===0&&g.seam===0&&g.framed==='0px', JSON.stringify(g));
  ok('no script errors', errs.length===0, errs.join(' | '));
  const el=await pg.$('.hm-fb'); await el.screenshot({path:OUT+'/cases.png'});
  await pg.close();
}
// ---- reduced motion ----
{
  const pg=await b.newPage(); await pg.setViewport({width:1440,height:900});
  await pg.emulateMediaFeatures([{name:'prefers-reduced-motion',value:'reduce'}]);
  await pg.goto(PAGE,{waitUntil:'networkidle2'});
  await pg.evaluate(()=>{const s=document.getElementById('reviews'); scrollTo(0,s.getBoundingClientRect().top+scrollY-60);});
  await new Promise(r=>setTimeout(r,1500));
  const a=await pg.evaluate(()=>document.getElementById('fb-rail').scrollLeft); await new Promise(r=>setTimeout(r,2500));
  const c=await pg.evaluate(()=>document.getElementById('fb-rail').scrollLeft);
  const st=await pg.evaluate(()=>{const f=document.querySelector('.fb'); const fr=f.querySelector('.fb__face--front').getBoundingClientRect(), bk=f.querySelector('.fb__face--back').getBoundingClientRect();
    return {static:document.getElementById('reviews').classList.contains('is-static'), stacked:bk.top>=fr.bottom-1, bothVisible:fr.height>50&&bk.height>50};});
  ok('reduced motion: nothing moves', a===c);
  ok('reduced motion: photograph and words both visible, stacked', st.static&&st.stacked&&st.bothVisible, JSON.stringify(st));
  await pg.close();
}
// ---- no javascript ----
{
  const pg=await b.newPage(); await pg.setViewport({width:1440,height:900}); await pg.setJavaScriptEnabled(false);
  await pg.goto(PAGE,{waitUntil:'networkidle2'});
  const st=await pg.evaluate(()=>{const f=document.querySelector('.fb'); const fr=f.querySelector('.fb__face--front').getBoundingClientRect(), bk=f.querySelector('.fb__face--back').getBoundingClientRect();
    const step=document.querySelector('.hm-fb__step'); return {stacked:bk.top>=fr.bottom-1, bothVisible:fr.height>50&&bk.height>50, buttonsHidden:!step||getComputedStyle(step).display==='none',
      overflow:document.documentElement.scrollWidth-innerWidth};});
  ok('no-JS: both faces visible, stacked, buttons hidden, no overflow', st.stacked&&st.bothVisible&&st.buttonsHidden&&st.overflow===0, JSON.stringify(st));
  await pg.close();
}
// ---- phone ----
{
  const pg=await b.newPage(); await pg.setViewport({width:390,height:844,isMobile:true,hasTouch:true});
  await pg.goto(PAGE,{waitUntil:'networkidle2'}); await new Promise(r=>setTimeout(r,1500));
  const m=await pg.evaluate(()=>{const cells=[...document.querySelectorAll('.fb')].slice(0,24); const R=cells.map(f=>f.getBoundingClientRect());
    return {h:[...new Set(R.map(r=>Math.round(r.height)))], maxW:Math.max(...R.map(r=>Math.round(r.width))), vw:innerWidth, overflow:document.documentElement.scrollWidth-innerWidth};});
  ok('phone: uniform height, no card wider than the screen, no page overflow', m.h.length===1&&m.maxW<=m.vw*0.88+1&&m.overflow===0, JSON.stringify(m));
  await pg.close();
}
await b.close();
console.log(out.join('\n'));
