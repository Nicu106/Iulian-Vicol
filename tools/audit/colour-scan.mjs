// tools/audit/colour-scan.mjs — how much colour a page actually carries, band by band.
// Run: node tools/audit/colour-scan.mjs /contacto
// For when the client says something is missing and cannot name it. It turns a
// feeling into a number: mean saturation, and the share of pixels above 15% and
// 35% saturation, per 100px band of the full page.
import p from 'puppeteer'; import { readdirSync } from 'fs';
const HOST = process.env.AUDIT_HOST || 'v2design.ivmotorclass.com';
const URL = `https://${HOST}${(process.argv[2] || '/contacto').replace(/^(?!\/)/, '/')}`;
const dir=`${process.env.HOME}/.cache/puppeteer/chrome`;
const ex=`${dir}/${readdirSync(dir).filter(d=>d.startsWith('linux-')).sort().pop()}/chrome-linux64/chrome`;
const b=await p.launch({executablePath:ex,args:['--no-sandbox','--ignore-certificate-errors',`--host-resolver-rules=MAP ${HOST} 127.0.0.1`]});
const pg=await b.newPage(); await pg.setViewport({width:1440,height:900});
await pg.goto(URL,{waitUntil:'networkidle0'});
// reveal everything so the arrival animation is not measured as blank page
await pg.evaluate(async()=>{const H=document.documentElement.scrollHeight;
  for(let y=0;y<=H;y+=400){window.scrollTo(0,y);await new Promise(r=>setTimeout(r,70));}
  window.scrollTo(0,0);});
await new Promise(r=>setTimeout(r,900));
const marks = await pg.evaluate(()=>{
  const o={}; for (const s of ['.ct-open','.ct-where__canvas','.ct-where__say','.ct-far','.ct-write','.mc-foot']){
    const e=document.querySelector(s); if(e){const r=e.getBoundingClientRect();
      o[s]={top:Math.round(r.top+scrollY), h:Math.round(r.height)};}}
  o._doc=document.documentElement.scrollHeight; return o;
});
const shot = await pg.screenshot({fullPage:true, encoding:'base64'});
await pg.close();

const an=await b.newPage(); await an.setViewport({width:400,height:400});
await an.goto('about:blank');
const res = await an.evaluate(async (b64)=>{
  const im=new Image(); im.src='data:image/png;base64,'+b64; await im.decode();
  const W=im.naturalWidth, H=im.naturalHeight;
  const c=document.createElement('canvas'); c.width=W; c.height=H;
  const x=c.getContext('2d',{willReadFrequently:true}); x.drawImage(im,0,0);
  const band=100, out=[];
  for(let y0=0;y0<H;y0+=band){
    const h=Math.min(band,H-y0);
    const d=x.getImageData(0,y0,W,h).data;
    let n=0, satSum=0, coloured=0, strong=0;
    for(let i=0;i<d.length;i+=16){ // every 4th pixel
      const r=d[i],g=d[i+1],bl=d[i+2];
      const mx=Math.max(r,g,bl), mn=Math.min(r,g,bl);
      const s=mx? (mx-mn)/mx : 0;
      satSum+=s; n++;
      if(s>0.15) coloured++;
      if(s>0.35) strong++;
    }
    out.push({y:y0, meanSat:+(satSum/n*100).toFixed(1), pctColoured:+(coloured/n*100).toFixed(1), pctStrong:+(strong/n*100).toFixed(1)});
  }
  return {W,H,out};
}, shot);
console.log('page', res.W+'x'+res.H);
console.log('landmarks', JSON.stringify(marks));
console.log('\n  y     meanSat%  coloured%  strong%');
for(const r of res.out) console.log(String(r.y).padStart(5), String(r.meanSat).padStart(9), String(r.pctColoured).padStart(10), String(r.pctStrong).padStart(8));
const tot=res.out.reduce((a,r)=>a+r.pctColoured,0)/res.out.length;
const dead=res.out.filter(r=>r.pctColoured<2).length;
console.log('\nwhole page: mean coloured', tot.toFixed(1)+'%');
console.log('bands with under 2% colour:', dead, 'of', res.out.length, '=', (dead/res.out.length*100).toFixed(0)+'% of the page height');
await b.close();
