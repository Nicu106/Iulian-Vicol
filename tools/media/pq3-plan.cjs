// /muestras/por-que/3 — what each screen sees of each photograph, from the camera
// itself (public/js/pq3.js, run under node). Feeds tools/media/pq3-media.py.
//
//   node tools/media/pq3-plan.cjs pairs [pq3.js]   the region of A on screen while a
//        zoom-through dissolves, per pair and screen shape (the match is fitted there)
//   node tools/media/pq3-plan.cjs cuts  [pq3.js]   per photograph and set (p: phones
//        upright; l: every other screen) the samples (region seen, zoom) of its path
//
// Prints JSON. Regions are [x0, y0, x1, y1] in fractions of the photograph.
const path = require('path');
const [mode, file] = process.argv.slice(2);
const MATH = require(path.resolve(file || path.join(__dirname, '../../public/js/pq3.js')));

// the screens each set is made for (stage sizes: browsers' bars already taken off)
const SETS = {
  p: [[320, 504], [320, 568], [360, 640], [360, 740], [375, 667], [375, 748], [390, 844], [390, 750],
      [393, 852], [412, 915], [414, 736], [430, 932], [428, 840]],
  l: [[844, 390], [932, 430], [768, 1024], [820, 1180], [1024, 1366], [1024, 768], [1280, 800],
      [1366, 768], [1440, 900], [1536, 864], [1920, 1080], [2560, 1440], [2560, 1080], [1280, 1024]],
};
const vpOf = ([vw, vh]) => ({ vw, vh, wc: Math.max(vw, vh / MATH.AR), mode: vw / vh < 1 ? 'P' : 'L' });
const STEP = 0.01;

function each(cb) {
  for (const [set, list] of Object.entries(SETS)) for (const s of list) {
    const v = vpOf(s);
    for (const id of Object.keys(MATH.REELS)) {
      const g = new MATH.Reel(MATH.REELS[id]); g.layout(v);
      cb(set, v, g);
    }
  }
}

if (mode === 'pairs') {
  // A's region on screen from the handover to the end of the dissolve
  const out = {};
  each((set, v, g) => g.def.shots.forEach(sh => {
    if (!sh.from) return;
    const key = sh.from + '>' + sh.id, o = (out[key] = out[key] || {}), m = (o[v.mode] = o[v.mode] || [9, 9, -9, -9]);
    for (let u = sh.h - 0.02; u <= sh.h + MATH.LOCK / 2 + 1e-9; u += STEP) {
      g.memo = {};
      const a = MATH.seen(g.M(sh.from, u), v);
      m[0] = Math.min(m[0], a[0]); m[1] = Math.min(m[1], a[1]); m[2] = Math.max(m[2], a[2]); m[3] = Math.max(m[3], a[3]);
    }
    // the point of A at the middle of the screen at the handover: where the eye is
    g.memo = {};
    const c = MATH.seen(g.M(sh.from, sh.h), v), n = (m.n || 0) + 1;
    m[4] = ((m[4] || 0) * (n - 1) + (c[0] + c[2]) / 2) / n; m[5] = ((m[5] || 0) * (n - 1) + (c[1] + c[3]) / 2) / n; m.n = n;
  }));
  for (const o of Object.values(out)) for (const m of Object.values(o)) m.length = 6;
  console.log(JSON.stringify(out));
} else if (mode === 'cuts') {
  // every (region, zoom, css px of the cover box) a photograph is seen at, while it is visible
  const out = {};
  each((set, v, g) => g.def.shots.forEach(sh => {
    const sp = g.span(sh), o = (out[sh.id] = out[sh.id] || {}), list = (o[set] = o[set] || []);
    // on, and for LOCK screens after (fading down, or under the next photograph)
    for (let u = Math.max(sp[0], -0.6); u <= Math.min(sp[1] + MATH.LOCK, g.U + 1); u += STEP) {
      g.memo = {};
      const M = g.M(sh.id, u), a = MATH.seen(M, v);
      list.push([+a[0].toFixed(4), +a[1].toFixed(4), +a[2].toFixed(4), +a[3].toFixed(4), +(M.s / v.wc).toFixed(4), Math.round(v.wc)]);
    }
  }));
  console.log(JSON.stringify(out));
} else {
  console.error('usage: node pq3-plan.cjs pairs|cuts [pq3.js]'); process.exit(1);
}
