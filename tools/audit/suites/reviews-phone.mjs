// tools/audit/suites/reviews-phone.mjs — promoted from the session scratchpad. Run: node reviews-phone.mjs [outDir]
// Fails without the fix it encodes. See ../README.md.
import p from 'puppeteer'; import { readdirSync } from 'fs';
import { mkdirSync } from 'fs';
const OUT = process.argv[2] || new URL('../out', import.meta.url).pathname; mkdirSync(OUT, { recursive: true });
const dir=`${process.env.HOME}/.cache/puppeteer/chrome`;
const ex=`${dir}/${readdirSync(dir).filter(d=>d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;
const b=await p.launch({executablePath:ex,args:['--no-sandbox','--ignore-certificate-errors','--host-resolver-rules=MAP v2design.ivmotorclass.com 127.0.0.1']});
const pg=await b.newPage(); await pg.setViewport({width:390,height:844,isMobile:true,hasTouch:true});
const errs=[]; pg.on('pageerror',e=>errs.push(String(e)));
await pg.goto('https://v2design.ivmotorclass.com/inicio',{waitUntil:'networkidle2'});
await pg.evaluate(()=>{const s=document.getElementById('reviews'); scrollTo(0,s.getBoundingClientRect().top+scrollY-20);});
const wait=ms=>new Promise(r=>setTimeout(r,ms));
const st=()=>pg.evaluate(()=>{const rail=document.getElementById('fb-rail'),mid=rail.getBoundingClientRect().left+rail.clientWidth/2;
  let best=null,bd=1e9; document.querySelectorAll('.fb').forEach(f=>{const r=f.getBoundingClientRect();const d=r.left+r.width/2-mid;if(Math.abs(d)<Math.abs(bd)){bd=d;best=f;}});
  return {name:best.querySelector('.fb__by').textContent.trim(),dist:Math.round(bd),flip:parseInt(best.style.getPropertyValue('--flip'))||0,
    turned:[...document.querySelectorAll('.fb')].filter(f=>(parseInt(f.style.getPropertyValue('--flip'))||0)>0).length,
    x:Math.round(rail.scrollLeft), trans:getComputedStyle(best.querySelector('.fb__flip')).transitionDuration};});
const out=[]; const ok=(n,c,d='')=>out.push(`${c?'✓':'✗'} ${n}${d?'  — '+d:''}`);
const next=()=>pg.evaluate(()=>document.querySelector('.hm-fb__step [data-go="1"]').click());
const prev=()=>pg.evaluate(()=>document.querySelector('.hm-fb__step [data-go="-1"]').click());
await wait(1400); const s0=await st();
ok('starts with a photograph seated in the middle', Math.abs(s0.dist)<6&&s0.flip===0, `${s0.name} ${s0.dist}px ${s0.flip}°, transition ${s0.trans}`);
await wait(2600); const s1=await st();
ok('turns over by itself after the photo hold', s1.name===s0.name&&s1.flip===180&&Math.abs(s1.x-s0.x)<1, `${s1.name} ${s1.flip}°, moved ${Math.abs(s1.x-s0.x)}px`);
await pg.screenshot({path:OUT+'/phone-auto.png'});
await wait(4800); const s2=await st();
ok('advances by itself to the next photograph after the text hold', s2.name!==s1.name&&s2.flip===0&&Math.abs(s2.dist)<6&&s2.turned===0, `${s1.name} → ${s2.name} ${s2.dist}px ${s2.flip}°`);
await next(); await wait(300); const s3=await st();
ok('next on a photograph: turns it', s3.name===s2.name&&s3.flip===180, `${s3.flip}°`);
await next(); await wait(1100); const s4=await st();
ok('next on the words: the next photograph comes in', s4.name!==s3.name&&s4.flip===0&&Math.abs(s4.dist)<6&&s4.turned===0, `${s3.name} → ${s4.name} ${s4.dist}px`);
await prev(); await wait(1100); const s5=await st();
ok('previous on a photograph: the previous card comes in', s5.name===s3.name&&s5.flip===0&&Math.abs(s5.dist)<6, `${s4.name} → ${s5.name} ${s5.dist}px`);
await next(); await wait(300); await prev(); await wait(300); const s6=await st();
ok('previous on the words: back to its photograph, same card', s6.name===s5.name&&s6.flip===0, `${s6.name} ${s6.flip}°`);
// swipes ARE steps: left on a photograph turns it; left on the words advances; right on the words turns back
const swipe=async(dir)=>{const r=await pg.evaluate(()=>{const b=document.getElementById('fb-rail').getBoundingClientRect(); return {x:b.left+b.width*0.5,y:b.top+b.height/2};});
  await pg.touchscreen.touchStart(r.x, r.y); for(let i=1;i<=6;i++){ await pg.touchscreen.touchMove(r.x+dir*i*22, r.y); await wait(16);} await pg.touchscreen.touchEnd();};
const p0=await st(); await swipe(-1); await wait(900); const p1=await st();
ok('swipe left on a photograph: it turns, same card, seated', p1.name===p0.name&&p1.flip===180&&Math.abs(p1.dist)<6, `${p0.name} ${p0.flip}° → ${p1.flip}° at ${p1.dist}px`);
await swipe(-1); await wait(1200); const p2=await st();
ok('swipe left on the words: the next photograph comes in', p2.name!==p1.name&&p2.flip===0&&Math.abs(p2.dist)<6, `${p1.name} → ${p2.name} ${p2.dist}px ${p2.flip}°`);
await swipe(-1); await wait(900); await swipe(1); await wait(900); const p3=await st();
ok('swipe right on the words: back to its photograph', p3.name===p2.name&&p3.flip===0, `${p3.name} ${p3.flip}°`);
await swipe(1); await wait(1200); const p4=await st();
ok('swipe right on a photograph: the previous card comes in', p4.name===p1.name&&p4.flip===0&&Math.abs(p4.dist)<6, `${p3.name} → ${p4.name} ${p4.dist}px`);
await wait(2600); const p5=await st();
ok('...and the sequence carries on by itself after a swipe', p5.name===p4.name&&p5.flip===180, `${p5.flip}°`);
ok('no script errors', errs.length===0, errs.join(' | '));
await b.close(); console.log(out.join('\n'));
