/* /muestras/por-que/3 · Inmersiva — travelling through the photographs.

   THE CAMERA. A reel is one sticky screen; u is how many screens of scroll have
   passed since its stage reached the top. Every photograph has a path of
   keyframes [u, fx, fy, qx, qy, z, roll°] for upright screens (P) and wide ones
   (L): the point (fx, fy) of the photograph (fractions of its width and height)
   sits at (qx, qy) of the screen, at z times the size that just covers the
   screen. Between keyframes z moves geometrically (a constant feel of speed),
   the rest linearly — scrubbed motion is linear on this site; the feel is in
   the smoothed scroll. A frame that would show an edge is slid (or, rolled,
   scaled about the centre) until it covers. Every tenth of a screen of scroll
   moves the camera by about the same amount (2.5-4% of the diagonal on a phone,
   measured by the session's flow tool): no photograph is ever held still.

   THE ENDS. The opening fades up from his wall's own grey and settles from 3%
   closer, in time, with the header lying on that wall. A reel's first photograph rises from black
   once its stage has arrived; its last one settles and goes down to black
   (scrubbed), its last words arrive on that black, and the stage leaves as black
   so the reading after it rises on the same night.

   THE ZOOM-THROUGH. Pairs of photographs of the same car were matched on the
   originals: SIFT + RANSAC first, then refined per screen shape on the part of
   the photo that is on screen at the handover (the similarity that best overlays
   the two photographs' edges, weighted to the middle of the screen; tools/media/
   pq3-media.py). The camera pushes into A until B, placed by that match, covers
   the screen by itself (A's framing there is solved per viewport). From that
   moment the two are locked together — B moves on its own path and A follows it
   through the match — and B dissolves in over A, whole, in T_X seconds (in time,
   not scrubbed: a pause never freezes a double image). No mask, no window: by
   then everything on screen is the detail both photographs share. Where the two
   camera positions still disagree on screen (DIP), the same locked handover goes
   through black instead. Between cars, a cut: down to the story's black and up
   again, also in time.

   LAYERS. Only the cuts on screen, or about to be, are displayed; each is one
   layer the size of its cut, so a frame holds one photograph layer, two while
   they dissolve. A cut is decoded before it is shown and placed at opacity 0 a
   quarter of a screen before its first frame.

   SHARPNESS. A phone sees an upright slice of each photograph, so it is sent
   that slice, cut from the original at the density its deepest zoom needs; and
   where the camera goes deeper than that density allows, a second, smaller cut
   (level 1) of just the region seen there takes over (same pixels of the same
   photograph: the switch cannot be seen, only the sharpness). Wide screens get
   the whole photograph (2400) and the same level-1 cuts. CROPS is written by
   tools/media/pq3-media.py; a cut that would not cover this screen's path is not
   used (the whole photograph is).

   THE WORDS arrive on time (0.8 s, line by line), not scrubbed, so a pause
   never leaves a line half there; while on, they drift with the camera by 12%
   of the movement of the point of the photograph under them. One type scale
   (pq3.css): W, the single words, measured here to fill the measure; every
   statement one major third below. A beat may be timed per screen shape.

   The portrait and the welcome scene are the live page's stills, driven as
   why.js drives them (it is not loaded here: it also drives the films).
   Reduced motion, no IntersectionObserver: nothing runs; the CSS shows the essay.
   Debug: #pq-debug exposes window.__pq (seek a reel, read the state). */
(function () {
  'use strict';

  // ---- data -------------------------------------------------------------------
  // pB = k·R(φ)·pA + t   (k, φ rad, tx, ty in photo widths); per screen shape when
  // the fit differs (P upright, L wide)
  var PAIRS = /*PAIRS*/{
    '14>15': { P: [1.79636, 0.00813, -0.74552, -0.47265], L: [1.80583, 0.00052, -0.75786, -0.46623] },
    '40>41': { P: [1.807, 0.04418, -0.38181, -0.42683], L: [1.80079, 0.04352, -0.37914, -0.42339] },
    '43>44': { P: [1.32683, -0.0141, -0.18126, -0.24951], L: [1.35449, 0.00296, -0.18465, -0.27182] },
    '19>21': { P: [1.04642, 0.00834, -0.01338, -0.04559], L: [1.05283, 0.00958, -0.01589, -0.04858] }
  }/*/PAIRS*/;
  // the cuts: per shot, per set (p: phones upright, l: everything else)
  // [level-0 rect, level-1 rect or 0, the zoom it was planned to take over at,
  //  level 0's and level 1's file pixels per photo width]; rects [x0, y0, x1, y1]
  // in fractions of the photograph
  var CROPS = /*CROPS*/{
    '14': { p: [[0.5764, 0.0, 1.0, 0.8858], 0, 2.463, 4032, 0], l: [[0.0, 0.0, 1.0, 1.0], [0.2514, 0.0578, 1.0, 0.9083], 1.389, 2400, 4032] },
    '15': { p: [[0.4428, 0.0, 0.9271, 0.9577], 0, 2.1, 3915, 0], l: [[0.0, 0.0, 1.0, 1.0], [0.2247, 0.0577, 0.9817, 0.8481], 1.389, 2400, 3441] },
    '19': { p: [[0.2419, 0.0, 0.7581, 1.0], 0, 1.448, 2699, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '21': { p: [[0.2968, 0.0808, 0.7067, 0.9621], 0, 1.8, 3357, 0], l: [[0.0, 0.0, 1.0, 1.0], [0.1214, 0.1048, 0.8786, 0.9275], 1.389, 2400, 2950] },
    '22': { p: [[0.2419, 0.0, 0.8552, 1.0], 0, 1.22, 2275, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '24': { p: [[0.3216, 0.14, 0.7688, 1.0], 0, 1.6, 2983, 0], l: [[0.0, 0.0, 1.0, 1.0], [0.1528, 0.2533, 0.9119, 1.0], 1.389, 2400, 2623] },
    '26': { p: [[0.1419, 0.0, 0.6581, 1.0], 0, 1.45, 2704, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '27': { p: [[0.3619, 0.0, 0.9604, 1.0], 0, 1.32, 2461, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '29': { p: [[0.2011, 0.0, 0.6989, 1.0], 0, 1.42, 2648, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '30': { p: [[0.4954, 0.0, 0.9932, 0.9927], 0, 1.3, 2425, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '40': { p: [[0.1819, 0.0, 0.6981, 1.0], [0.2987, 0.1841, 0.6568, 0.9055], 1.495, 2766, 4032], l: [[0.0, 0.0, 1.0, 1.0], [0.1303, 0.1344, 0.8891, 0.9238], 1.389, 2400, 4031] },
    '41': { p: [[0.225, 0.0, 0.7489, 1.0], 0, 1.75, 3262, 0], l: [[0.0, 0.0, 1.0, 1.0], [0.1347, 0.1289, 0.8918, 0.916], 1.389, 2400, 3111] },
    '43': { p: [[0.2519, 0.0, 0.7681, 1.0], 0, 1.691, 3152, 0], l: [[0.0, 0.0, 1.0, 1.0], [0.0723, 0.1499, 0.8307, 0.97], 1.389, 2400, 3668] },
    '44': { p: [[0.2912, 0.0, 0.7205, 0.9077], 0, 1.7, 3170, 0], l: [[0.0, 0.0, 1.0, 1.0], [0.0, 0.0905, 0.756, 0.9017], 1.389, 2400, 3197] }
  }/*/CROPS*/;

  // per reel: its length U (screens), the beats' [in, out, anchor shot], the shots
  // in order. A shot is the first, or comes in at a cut (in: u), or through a
  // zoom-through handing over at h (from, h); it may leave at a cut (out: u).
  // P / L keyframes. Cuts and dissolves happen at a point of the scroll and then
  // take their own time (T_*), so a pause never leaves two photographs half there.
  // Every step of scroll moves the camera by about the same amount (2.5–4% of the
  // screen's diagonal per tenth of a screen: tools flow.cjs); no shot is held.
  // enter: the first photograph rises from the story's black once the stage has
  // arrived (in time), so a reel never slides in as half a picture under text.
  // wall: [colour, u] the stage's ground until u (the opening: his wall, see drawReel).
  // dim: [a, b] the last photograph settles and goes down to black, scrubbed, and
  // the reel's last words arrive on that black; the stage then leaves as black
  // and the reading that follows rises on the same black.
  var REELS = {
    a: {
      U: 5.15, dim: [4.62, 5.1], wall: ['#C6D1D3', 0.45],
      beats: { a1: { P: [-9, 0.22, '14'], L: [-9, 0.1, '14'] }, a2: [0.82, 1.26, '15'], a3: [2.08, 2.54, '41'], a4: { P: [2.7, 3.1, '43'], L: [3.26, 3.64, '44'] }, a5: [4.82, 5.45, '21'] },
      shots: [
        { id: '14', first: true,
          P: [[0, .82, 0, .5, 0, 1.18, 0], [0.75, .80, .58, .5, .55, 1.95, 0]],
          L: [[0, .56, 0, .5, 0, 1, 0], [0.4, .70, .36, .60, .33, 1.4, 0], [0.75, .80, .58, .68, .56, 1.86, 0]] },
        { id: '15', from: '14', h: 0.75, out: 1.32,
          P: [[0.95, .68, .41, .5, .43, 1.28, 0], [1.6, .58, .45, .5, .47, 2.1, 0]],
          L: [[0.95, .66, .38, .55, .42, 1.25, 0], [1.6, .58, .43, .55, .46, 1.9, 0]] },
        { id: '40', in: 1.32,
          P: [[1.32, .44, .55, .5, .55, 1, 0], [2.02, .50, .58, .5, .58, 1.9, 0]],
          L: [[1.32, .52, .45, .5, .45, 1, 0], [2.02, .50, .57, .5, .57, 1.9, 0]] },
        { id: '41', from: '40', h: 2.02, out: 2.6,
          P: [[2.34, .54, .575, .5, .56, 1.4, 0], [2.85, .48, .54, .5, .54, 1.75, 0]],
          L: [[2.34, .515, .55, .5, .55, 1.38, 0], [2.85, .47, .55, .5, .55, 1.8, 0]] },
        { id: '43', in: 2.6,
          P: [[2.6, .51, .55, .5, .55, 1, 0], [3.15, .51, .62, .5, .60, 1.45, 0]],
          L: [[2.6, .50, .50, .5, .50, 1, 0], [3.15, .51, .62, .62, .50, 1.45, 0]] },
        { id: '44', from: '43', h: 3.15, out: 3.7,
          P: [[3.42, .51, .425, .5, .43, 1.25, 0], [3.9, .51, .47, .5, .49, 1.7, 0]],
          L: [[3.42, .51, .47, .74, .40, 1.55, 0], [3.9, .51, .48, .78, .40, 1.85, 0]] },
        { id: '19', in: 3.7,
          P: [[3.7, .50, .50, .5, .50, 1, 0], [4.12, .50, .55, .5, .55, 1.4, 0]],
          L: [[3.7, .50, .48, .5, .48, 1, 0], [4.12, .50, .55, .5, .55, 1.4, 0]] },
        { id: '21', from: '19', h: 4.12,
          P: [[4.3, .50, .42, .5, .33, 1.3, 0], [4.7, .50, .41, .5, .32, 1.6, 0], [5.4, .50, .40, .5, .30, 1.8, 0]],
          L: [[4.3, .50, .42, .5, .40, 1.25, 0], [4.7, .50, .41, .5, .39, 1.5, 0], [5.4, .50, .40, .5, .38, 1.65, 0]] }
      ]
    },
    b: {
      U: 3.45, enter: -0.12, dim: [2.95, 3.4],
      beats: { b1: [-0.05, 0.5, '22'], b2: [0.62, 1.06, '27'], b3: [1.18, 1.56, '26'], b4: [2.06, 2.5, '30'], b5: [3.15, 3.75, '24'] },
      shots: [
        { id: '22', first: true, out: 0.56,
          P: [[-0.12, .50, .55, .5, .55, 1, 0], [0.62, .64, .57, .5, .57, 1.22, 0]],
          L: [[-0.12, .50, .50, .5, .50, 1, 0], [0.62, .58, .53, .5, .53, 1.45, 0]] },
        { id: '27', in: 0.56, out: 1.12,
          P: [[0.56, .62, .50, .5, .50, 1, 0], [1.2, .76, .48, .5, .50, 1.32, 0]],
          L: [[0.56, .55, .45, .5, .45, 1, 0], [1.2, .70, .48, .5, .50, 1.4, 0]] },
        { id: '26', in: 1.12, out: 1.62,
          P: [[1.12, .40, .50, .5, .52, 1, 0], [1.7, .37, .56, .5, .55, 1.45, 0]],
          L: [[1.12, .40, .90, .40, .88, 1.1, 0], [1.7, .376, .558, .45, .55, 1.32, 0]] },
        { id: '29', in: 1.62, out: 2.0,
          P: [[1.62, .45, .50, .5, .50, 1.04, 0], [2.06, .43, .52, .5, .52, 1.42, 0]],
          L: [[1.62, .45, .50, .5, .50, 1.02, 0], [2.06, .44, .52, .5, .52, 1.24, 0]] },
        { id: '30', in: 2.0, out: 2.56,
          P: [[2.0, .74, .40, .5, .40, 1.06, 0], [2.62, .79, .44, .5, .44, 1.3, 0]],
          L: [[2.0, .80, .10, .75, .12, 1.22, 0], [2.62, .82, .12, .75, .12, 1.42, 0]] },
        { id: '24', in: 2.56,
          P: [[2.56, .54, 1, .5, 1, 1.2, 0], [3.0, .57, 1, .5, 1, 1.45, 0], [3.6, .60, 1, .5, 1, 1.6, 0]],
          L: [[2.56, .50, 1, .5, 1, 1.04, 0], [3.0, .52, 1, .5, 1, 1.3, 0], [3.6, .54, 1, .5, 1, 1.45, 0]] }
      ]
    }
  };
  // zoom-throughs that cut through black instead of dissolving, per screen shape:
  // where the two camera positions disagree on screen (wide: 14 and 15 on the wall
  // and the windscreen; 43 and 44 everywhere: the bonnet's star stands at two
  // heights), a dissolve would show both. There the camera dips to black on the
  // detail and comes back on the same detail (still placed by the match).
  var DIP = { '14>15': 'L', '43>44': 'PL' };

  var AR = 0.75;                        // every photograph is 4:3
  // seconds: a zoom-through's dissolve; a cut's fade down, and up after a breath of black
  var T_X = 0.6, T_DOWN = 0.4, T_UP = 0.7, T_GAP = 0.42;
  var LOCK = 0.3;                       // screens past h for which A is placed by B (tools check its cover there)
  var FOLLOW = 0.12;                    // words drift by this share of the camera's movement under them

  // ---- the camera, pure ---------------------------------------------------------
  // a matrix {s, r, ox, oy}: screen = o + s·R(r)·p, p in photo widths (y up to AR)
  function camM(c, v) {
    var s = c[5] * v.wc, r = c[6] * Math.PI / 180, cr = Math.cos(r), sr = Math.sin(r);
    var fx = c[1], fy = c[2] * AR;
    return { s: s, r: r, ox: c[3] * v.vw - s * (cr * fx - sr * fy), oy: c[4] * v.vh - s * (sr * fx + cr * fy) };
  }
  // the part of the photograph on screen: [x0, y0, x1, y1] in fractions
  function seen(M, v) {
    var cr = Math.cos(-M.r), sr = Math.sin(-M.r), q = [0, 0, v.vw, 0, 0, v.vh, v.vw, v.vh];
    var x0 = 9, y0 = 9, x1 = -9, y1 = -9;
    for (var i = 0; i < 8; i += 2) {
      var dx = q[i] - M.ox, dy = q[i + 1] - M.oy;
      var px = (cr * dx - sr * dy) / M.s, py = (sr * dx + cr * dy) / M.s / AR;
      if (px < x0) x0 = px; if (px > x1) x1 = px; if (py < y0) y0 = py; if (py > y1) y1 = py;
    }
    return [x0, y0, x1, y1];
  }
  function inside(a, c, e) { return a[0] >= c[0] - e && a[1] >= c[1] - e && a[2] <= c[2] + e && a[3] <= c[3] + e; }
  function covers(M, v) { return inside(seen(M, v), [0, 0, 1, 1], 0.75 / M.s); }
  // slide (and if rolled, grow about the centre) until no edge shows
  // (one rule for every roll, so a roll unwinding to 0 never switches rules mid-move)
  function fit(M, v) {
    var ox = Math.min(0, Math.max(v.vw - M.s, M.ox)), oy = Math.min(0, Math.max(v.vh - M.s * AR, M.oy));
    var S = { s: M.s, r: M.r, ox: ox, oy: oy, slid: ox !== M.ox || oy !== M.oy };
    if (covers(S, v)) return S;
    var cx = v.vw / 2, cy = v.vh / 2, lo = 1, hi = 2.5, g = function (f) {
      return { s: S.s * f, r: S.r, ox: cx + (S.ox - cx) * f, oy: cy + (S.oy - cy) * f, slid: S.slid };
    };
    for (var i = 0; i < 18; i++) { var mid = (lo + hi) / 2; if (covers(g(mid), v)) hi = mid; else lo = mid; }
    var out = g(hi); out.grown = hi; return out;
  }
  var shift = function (o, min) { return o > 0 ? -o : o < min ? min - o : 0; };
  // A's keyframe at the handover, moved (or pushed a little further) until B,
  // placed by the match, covers the screen by itself — and A too
  function solve(k, pr, v) {
    for (var n = 0; n < 30; n++) {
      var MA = camM(k, v), MB = mapM(MA, pr);
      MA.ox += shift(MB.ox, v.vw - MB.s); MA.oy += shift(MB.oy, v.vh - MB.s * AR);
      MA.ox += shift(MA.ox, v.vw - MA.s); MA.oy += shift(MA.oy, v.vh - MA.s * AR);
      MB = mapM(MA, pr);
      if (covers(MB, v) && covers(MA, v)) {
        var r = MA.r, x = k[1], y = k[2] * AR;
        k[3] = (MA.ox + MA.s * (Math.cos(r) * x - Math.sin(r) * y)) / v.vw;
        k[4] = (MA.oy + MA.s * (Math.sin(r) * x + Math.cos(r) * y)) / v.vh;
        return n;
      }
      k[5] *= 1.025;
    }
    return -1;
  }
  // B's matrix from A's (A placed, B where the match puts it), and back
  function mapM(MA, pr) {
    var s = MA.s / pr[0], r = MA.r - pr[1], cr = Math.cos(r), sr = Math.sin(r);
    return { s: s, r: r, ox: MA.ox - s * (cr * pr[2] - sr * pr[3]), oy: MA.oy - s * (sr * pr[2] + cr * pr[3]) };
  }
  function unmapM(MB, pr) {
    var cr = Math.cos(MB.r), sr = Math.sin(MB.r);
    return { s: MB.s * pr[0], r: MB.r + pr[1], ox: MB.ox + MB.s * (cr * pr[2] - sr * pr[3]), oy: MB.oy + MB.s * (sr * pr[2] + cr * pr[3]) };
  }
  // the camera of matrix M, expressed with focus (fx, fy) — for continuing a path from it
  function camOf(M, fx, fy, u, v) {
    var cr = Math.cos(M.r), sr = Math.sin(M.r), x = fx, y = fy * AR;
    return [u, fx, fy, (M.ox + M.s * (cr * x - sr * y)) / v.vw, (M.oy + M.s * (sr * x + cr * y)) / v.vh,
            M.s / v.wc, M.r * 180 / Math.PI];
  }
  function along(kf, u) {
    if (u <= kf[0][0]) return kf[0];
    var n = kf.length - 1;
    if (u >= kf[n][0]) return kf[n];
    for (var i = 0; i < n; i++) if (u < kf[i + 1][0]) break;
    var a = kf[i], b = kf[i + 1], t = (u - a[0]) / (b[0] - a[0]), o = [u];
    for (var j = 1; j < 7; j++) o.push(j === 5 ? a[5] * Math.pow(b[5] / a[5], t) : a[j] + (b[j] - a[j]) * t);
    return o;
  }
  var ramp = function (u, a, b) { return u <= a ? 0 : u >= b ? 1 : (u - a) / (b - a); };

  // a reel's geometry for one viewport: v = {vw, vh, wc, mode}
  function Reel(def) {
    this.def = def; this.U = def.U; this.by = {};
    var self = this;
    def.shots.forEach(function (sh) { self.by[sh.id] = sh; });
    def.shots.forEach(function (sh) { if (sh.from) self.by[sh.from].to = sh; });
  }
  Reel.prototype.layout = function (v) {
    var self = this;
    this.v = v; this.memo = {};
    this.def.shots.forEach(function (sh) { sh.kv = sh[v.mode].map(function (k) { return k.slice(); }); });
    this.def.shots.forEach(function (sh) {
      if (!sh.from) return;
      var p = PAIRS[sh.from + '>' + sh.id];
      sh.pr = p[v.mode] || p;
      self.by[sh.from].kv.forEach(function (k) { if (Math.abs(k[0] - sh.h) < 1e-6) sh.solved = solve(k, sh.pr, v); });
      self.memo = {};
    });
  };
  // the matrix of a shot at u (memoised for the current u)
  Reel.prototype.M = function (id, u) {
    var key = id + '@' + u, m = this.memo[key];
    if (m) return m;
    var sh = this.by[id], v = this.v;
    if (sh.from && u < sh.h) m = mapM(this.M(sh.from, u), sh.pr);
    else if (sh.to && u > sh.to.h) m = unmapM(this.M(sh.to.id, u), sh.to.pr);
    else {
      var kf = sh.kv;
      if (sh.from) kf = [camOf(mapM(this.M(sh.from, sh.h), sh.pr), kf[0][1], kf[0][2], sh.h, v)].concat(kf);
      m = fit(camM(along(kf, u), v), v);
    }
    return (this.memo[key] = m);
  };
  // where a shot is on, as a function of the scroll alone (0/1; the page fades it in time)
  Reel.prototype.on = function (sh, u) {
    var sp = this.span(sh);
    return u >= sp[0] && u < sp[1] ? 1 : 0;
  };
  // the span of u in which a shot is on
  Reel.prototype.span = function (sh) {
    return [sh.first ? -99 : sh.from ? sh.h : sh['in'], sh.out != null ? sh.out : sh.to ? sh.to.h : 99];
  };

  var MATH = { camM: camM, covers: covers, seen: seen, inside: inside, fit: fit, mapM: mapM, unmapM: unmapM, along: along,
               Reel: Reel, REELS: REELS, PAIRS: PAIRS, CROPS: CROPS, DIP: DIP, AR: AR, LOCK: LOCK };
  if (typeof document === 'undefined') { if (typeof module !== 'undefined') module.exports = MATH; return; }

  // ---- the page -----------------------------------------------------------------
  var root = document.documentElement;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var reelEls = Array.prototype.slice.call(document.querySelectorAll('[data-reel]'));
  function essay() {
    // the essay: each key photograph, whole
    root.classList.remove('pq-on', 'wy-on');
    Array.prototype.forEach.call(document.querySelectorAll('.pq-shot--key img[data-src]'), function (i) {
      i.loading = 'lazy'; i.sizes = i.dataset.sizes; i.srcset = i.dataset.srcset; i.src = i.dataset.src;
    });
  }
  if (!reelEls.length || reduce || !('IntersectionObserver' in window) || !window.requestAnimationFrame) { essay(); return; }
  root.classList.add('pq-on', 'wy-on');

  // words: in 0.8 s, line by line 0.14 s apart, rising 0.28 em; out 0.5 s, lifting
  var T_IN = 0.8, T_OUT = 0.5, T_STAG = 0.14, RISE = 0.28, LIFT = 0.16;
  // a reel's first photograph rising from black; the opening's settle (a 3% push-back, in time)
  var T_ENTER = 1.1, T_INTRO = 1.2, T_SETTLE = 2.8, SETTLE = 0.03;
  var TAU = 0.11, LAG = 0.35;
  var E = function (t) { t = t < 0 ? 0 : t > 1 ? 1 : t; return t * t * (3 - 2 * t); };
  var clamp = function (v) { return v < 0 ? 0 : v > 1 ? 1 : v; };
  var now = performance.now();
  function tween(v) { return { from: v, to: v, at: 0, dur: 1 }; }
  function aim(tw, to, dur, delay) { if (tw.to === to) return; tw.from = val(tw); tw.to = to; tw.at = now + (delay || 0) * 1000; tw.dur = dur; }
  function val(tw) { return now < tw.at ? tw.from : tw.from + (tw.to - tw.from) * E((now - tw.at) / 1000 / tw.dur); }
  function busy(tw) { return tw.from !== tw.to && (now - tw.at) / 1000 < tw.dur; }

  // THE PICTURE. Each photograph is one or two cuts (img.pq-i), absolutely placed
  // in the stage and moved by transform and opacity only. Only a cut on screen, or
  // about to be (the next photograph a quarter of a screen before it arrives, the
  // dense cut as the zoom nears it), is displayed and on its own layer; every other
  // is display:none. So a frame holds one photograph layer, two while they dissolve,
  // each the size of its cut's box; a new one is decoded before it is shown, and
  // rasterised (at opacity 0) before its first visible frame.
  var R = reelEls.map(function (el) {
    var id = el.getAttribute('data-reel'), def = REELS[id], g = new Reel(def);
    el.style.setProperty('--u', def.U);
    var shots = def.shots.map(function (sh) {
      var box = el.querySelector('[data-shot="' + sh.id + '"]'), i0 = box.querySelector('img');
      var i1 = document.createElement('img');
      i1.alt = ''; i1.decoding = 'async'; i1.className = 'pq-i pq-i--1'; i1.setAttribute('aria-hidden', 'true');
      i0.classList.add('pq-i');
      box.appendChild(i1);
      // [img, cut rect, its url, state 0 none 1 loading 2 ready 3 failed, generation, style key, displayed]
      return { sh: sh, el: box, L: [{ im: i0, c: null, url: '', st: 0, gen: 0, k: '', on: false }, { im: i1, c: null, url: '', st: 0, gen: 0, k: '', on: false }],
               want: false, cut: null, set: '', vt: tween(0), init: false, op: [0, 0] };
    });
    var words = Array.prototype.slice.call(el.querySelectorAll('[data-b]')).map(function (w) {
      var b = def.beats[w.getAttribute('data-b')];
      var lines = Array.prototype.slice.call(w.querySelectorAll('.pq-l'));
      return { el: w, beat: b, a: 0, z: 0, anchor: '', lines: lines, tin: lines.map(function () { return tween(0); }),
               sty: [], cx: 0, cy: 0, ref: null, off: '' };
    });
    return { el: el, id: id, def: def, g: g, stage: el.querySelector('.pq-stage'), shots: shots, words: words, top: 0, near: false, u: -9,
             intro: id === 'a' ? tween(0) : null, settle: id === 'a' ? tween(0) : null };
  });

  // ---- the stills (portrait, welcome): as why.js ---------------------------------
  var S = Array.prototype.slice.call(document.querySelectorAll('[data-scene="still"]')).map(function (el) {
    return {
      el: el, top: 0, run: 1, near: false, sv: {},
      welcome: el.classList.contains('wy-still--welcome'),
      pics: Array.prototype.slice.call(el.querySelectorAll('.wy-photo > img')),
      beats: Array.prototype.slice.call(el.querySelectorAll('.wy-beat')).map(function (b) {
        var whole = b.classList.contains('wy-beat--sub') || b.classList.contains('wy-beat--act');
        var lines = whole ? [b] : Array.prototype.slice.call(b.querySelectorAll('.wy-l'));
        return { el: b, a: parseFloat(b.getAttribute('data-in')), act: b.classList.contains('wy-beat--act'),
                 lines: lines, tin: lines.map(function () { return tween(0); }), sty: [],
                 // a beat that fades as a whole: its lines say so too while it is hidden, so a
                 // tool reading each line's own opacity never takes hidden words for shown ones
                 kids: whole ? Array.prototype.slice.call(b.querySelectorAll('.wy-l')) : [] };
      })
    };
  });

  // ---- measuring -------------------------------------------------------------------
  var vp = null;
  function measure() {
    var sy = window.pageYOffset;
    R.forEach(function (r) { r.top = r.el.getBoundingClientRect().top + sy; });
    S.forEach(function (st) {
      var b = st.el.getBoundingClientRect();
      st.top = b.top + sy; st.run = Math.max(1, b.height - window.innerHeight);
    });
  }
  // the cut each shot uses on this screen: the phone's own if it covers the whole
  // path here (checked every 0.02 screens), else the wide set's
  function chooseCuts(r) {
    var g = r.g, phone = vp.vw <= 600 && vp.vw < vp.vh;
    r.shots.forEach(function (s) {
      var c = CROPS[s.sh.id], d = s.el.dataset, has = function (k) { return c && c[k] && d[k + '0']; };
      var set = phone && has('p') ? 'p' : 'l', cut = has(set) ? c[set] : null;
      if (cut && set === 'p' && !pathIn(g, s.sh, cut)) { set = 'l'; cut = has('l') ? c.l : null; }
      if (cut && cut[1] && !d[set + '1']) cut = [cut[0], 0, 9, cut[3], 0];
      var was = s.set + (s.cut ? s.cut.join() : '');
      s.cut = cut || null; s.set = cut ? set : '';
      if (was !== s.set + (s.cut ? s.cut.join() : '')) s.L.forEach(drop1);
      s.L.forEach(function (l, i) {
        l.c = !cut ? (i ? null : [0, 0, 1, 1]) : (i ? cut[1] || null : cut[0]);
        l.k = '';
        if (!l.c) return;
        l.im.style.width = ((l.c[2] - l.c[0]) * vp.wc).toFixed(2) + 'px';
        l.im.style.height = ((l.c[3] - l.c[1]) * AR * vp.wc).toFixed(2) + 'px';
      });
    });
    g.memo = {};
  }
  // past level 0's density for this set (phones 1.5 file px per CSS px, the rest 0.9)
  function deep(cut, set, M) { return cut && cut[1] && cut[3] / M.s < (set === 'p' ? 1.5 : 0.9) && cut[4] > cut[3]; }
  function pathIn(g, sh, cut) {
    var sp = g.span(sh);
    for (var u = Math.max(sp[0], -1); u <= Math.min(sp[1] + LOCK, g.U + 1); u += 0.02) {
      g.memo = {};
      var M = g.M(sh.id, u), a = seen(M, vp);
      if (!inside(a, cut[0], 1e-3) && !(deep(cut, 'p', M) && inside(a, cut[1], 1e-3))) return false;
    }
    return true;
  }
  // the type: one scale. W, the single words, is the largest size at which the
  // longest of them fills the measure (and never more than a share of the height);
  // every statement is one step of a major third below it (S = W / 1.25), set in CSS.
  var wordEls = Array.prototype.slice.call(document.querySelectorAll('.pq-w--word:not(.pq-w--wrap) .pq-l'));
  var probeEl = null;
  function typeScale() {
    var pq = document.querySelector('.pq');
    if (!probeEl) {
      probeEl = document.createElement('div'); probeEl.setAttribute('aria-hidden', 'true');
      probeEl.style.cssText = 'position:absolute;left:0;top:0;height:0;visibility:hidden;width:var(--pq-mw)';
      pq.appendChild(probeEl);
    }
    var head = document.querySelector('.mc-head');
    if (head) pq.style.setProperty('--pq-head', head.offsetHeight + 'px');
    var meas = probeEl.offsetWidth || vp.vw * 0.8;
    var cap = vp.vh * (vp.mode === 'L' ? 0.125 : 0.085);
    var widest = 0;
    pq.style.setProperty('--pq-W', '100px');
    wordEls.forEach(function (sp) {
      var rg = document.createRange(); rg.selectNodeContents(sp);
      widest = Math.max(widest, rg.getBoundingClientRect().width);
    });
    if (widest > 0) pq.style.setProperty('--pq-W', Math.min(cap, 100 * meas / widest * 0.985).toFixed(2) + 'px');
    else pq.style.removeProperty('--pq-W');
  }
  function layout() {
    var st = R[0].stage, vw = st.clientWidth, vh = st.clientHeight;
    if (!vw || !vh) return;
    vp = { vw: vw, vh: vh, wc: Math.max(vw, vh / AR), mode: vw / vh < 1 ? 'P' : 'L' };
    typeScale();
    R.forEach(function (r) {
      r.g.layout(vp);
      chooseCuts(r);
      r.words.forEach(function (w) {
        w.el.style.transform = '';
        w.cx = w.el.offsetLeft + w.el.offsetWidth / 2; w.cy = w.el.offsetTop + w.el.offsetHeight / 2;
        w.ref = null; w.off = '';
        // a beat may differ per screen shape ({ P: [...], L: [...] })
        var bt = w.beat[vp.mode] || w.beat; w.a = bt[0]; w.z = bt[1]; w.anchor = bt[2];
      });
    });
    measure();
  }

  // ---- the files -------------------------------------------------------------------
  // a shot's cuts are asked for and decoded when it is near (a phone fetches what it
  // will see), and let go once it is far behind or ahead
  function get(l, url) {
    if (l.url === url && l.st) return;
    l.gen++; l.url = url; l.st = 1;
    var my = l.gen, im = l.im;
    var ok = function () { if (my === l.gen) { l.st = 2; kick(); } }, bad = function () { if (my === l.gen) { l.st = 3; kick(); } };
    im.loading = 'eager';
    if (im.getAttribute('srcset')) { im.removeAttribute('srcset'); im.removeAttribute('sizes'); }
    im.src = url;
    if (im.decode) im.decode().then(ok, function () { if (im.complete && im.naturalWidth) ok(); else im.addEventListener('load', ok); im.addEventListener('error', bad); });
    else if (im.complete && im.naturalWidth) ok();
    else { im.addEventListener('load', ok); im.addEventListener('error', bad); }
  }
  function drop1(l) {
    l.gen++; l.st = 0; l.url = ''; l.k = '';
    if (l.im.getAttribute('src')) l.im.removeAttribute('src');
    if (l.on) { l.on = false; l.im.classList.remove('is-on'); }
  }
  function load(s) {
    s.want = true;
    s.L.forEach(function (l, i) {
      if (!l.c) return;
      get(l, s.cut ? s.el.dataset[s.set + i] : l.im.dataset.src);
    });
  }
  function unload(s) { s.want = false; s.L.forEach(drop1); }

  // ---- drawing -----------------------------------------------------------------------
  var moving = false;
  // a cut placed by M: its transform (origin top-left)
  function mstr(M, c) {
    var r = M.r, cr = Math.cos(r), sr = Math.sin(r), x = c[0], y = c[1] * AR;
    var ox = M.ox + M.s * (cr * x - sr * y), oy = M.oy + M.s * (sr * x + cr * y);
    return 'translate3d(' + ox.toFixed(2) + 'px,' + oy.toFixed(2) + 'px,0)' +
      (Math.abs(r) > 1e-5 ? ' rotate(' + (r * 180 / Math.PI).toFixed(4) + 'deg)' : '') + ' scale(' + (M.s / vp.wc).toFixed(5) + ')';
  }
  // show a cut at op (0: displayed and placed, so it is rasterised, but not seen), or
  // take it off (display:none, no layer)
  function put(l, op, M) {
    if (op == null) { if (l.on) { l.on = false; l.k = ''; l.im.classList.remove('is-on'); } return; }
    if (!l.on) { l.on = true; l.im.classList.add('is-on'); }
    var t = mstr(M, l.c), k = op.toFixed(3) + t;
    if (k === l.k) return;
    l.k = k; l.im.style.opacity = op.toFixed(3); l.im.style.transform = t;
  }
  // the opening settles: the first photograph arrives 3% closer and eases back, in time
  function settled(r, M) {
    if (!r.settle) return M;
    var f = 1 + SETTLE * (1 - val(r.settle));
    if (f === 1) return M;
    if (busy(r.settle)) moving = true;
    var cx = vp.vw / 2, cy = vp.vh / 2;
    return { s: M.s * f, r: M.r, ox: cx + (M.ox - cx) * f, oy: cy + (M.oy - cy) * f };
  }
  function dipped(sh) { var p = sh.from && DIP[sh.from + '>' + sh.id]; return !!p && p.indexOf(vp.mode) >= 0; }
  function drawReel(r) {
    var u = r.u, g = r.g, def = r.def;
    g.memo = {};
    // first where each shot should be (cuts and dissolves run in time), then draw
    r.shots.forEach(function (s) {
      var sh = s.sh, on = g.on(sh, u);
      if (sh.first && def.enter != null) on = u >= def.enter ? on : 0;
      if (!s.init) { s.init = true; s.vt.from = s.vt.to = on; }
      if (sh.to && u >= sh.to.h && !dipped(sh.to)) { s.vt.from = s.vt.to = 1; s.vt.at = 0; return; }   // under B: see below
      if (sh.from && !dipped(sh)) aim(s.vt, on, T_X, 0);
      else if (sh.first && def.enter != null && on && u < 0.5) aim(s.vt, on, T_ENTER, 0);
      else aim(s.vt, on, on ? T_UP : T_DOWN, on ? T_GAP : 0);
    });
    var dim = def.dim ? 1 - ramp(u, def.dim[0], def.dim[1]) : 1;
    // the opening's ground is his wall (under the header and the first words, and
    // what the first photograph fades up from); once the camera is in, the story's black
    if (def.wall) {
      var gr = u < def.wall[1] ? def.wall[0] : '#05080F';
      if (gr !== r.ground) { r.ground = gr; r.stage.style.backgroundColor = gr; }
    }
    r.shots.forEach(function (s, i) {
      var sh = s.sh, sp = g.span(sh), op;
      if (sh.to && u >= sh.to.h && !dipped(sh.to)) {
        // under the photograph it dissolves into: whole until that one is whole
        var b = r.shots[i + 1].vt;
        op = b.to === 1 && val(b) < 0.999 ? 1 : 0;
      } else {
        op = val(s.vt);
        if (busy(s.vt) || now < s.vt.at) moving = true;
      }
      if (r.intro && sh.first) { op *= val(r.intro); if (busy(r.intro)) moving = true; }
      if (sp[1] > 90) op *= dim;
      // its files: wanted from 1.4 screens before it, let go 1.2 after (or 2 before)
      if (!s.want && u > sp[0] - 1.4 && u < sp[1] + 0.6) load(s);
      else if (s.want && (u < sp[0] - 2 || u > sp[1] + 1.2)) unload(s);
      s.op[0] = s.op[1] = 0;
      var L0 = s.L[0], L1 = s.L[1];
      // warm: about to be seen (a quarter of a screen before it comes in, or while a cut
      // fades it in after a pause), so it is placed at opacity 0 ahead of its first frame
      var warm = s.want && (op >= 0.002 || (u > sp[0] - 0.25 && u < sp[1]) || (s.vt.to === 1 && now < s.vt.at));
      if (!warm) { put(L0); put(L1); return; }
      var M = g.M(sh.id, u), hi = false, near = false;
      if (sh.first) M = settled(r, M);
      // level 1 once level 0 has fewer file pixels per CSS pixel than this set asks for
      // (phones 1.5, the rest 0.9), level 1 has more, and it covers the screen; warmed
      // from 15% before that
      if (L1.c && L1.st === 2 && inside(seen(M, vp), L1.c, 1e-3)) {
        hi = deep(s.cut, s.set, M);
        near = !hi && deep(s.cut, s.set, { s: M.s * 1.15 });
      }
      var a = hi ? L1 : L0, b = hi ? L0 : L1;
      if (a.st !== 2) { a = L0.st === 2 ? L0 : null; b = a === L0 ? L1 : L0; }
      var o = op < 0.002 ? 0 : op;
      if (a) { put(a, o, M); s.op[a === L1 ? 1 : 0] = o; }
      if (b.c && b.st === 2 && (near || (hi && b === L0 && !deep(s.cut, s.set, { s: M.s / 1.15 })))) put(b, 0, M); else put(b);
    });
    r.words.forEach(function (w) {
      var show = u >= w.a && u < w.z && !(r.intro && w.a < -1 && r.intro.to < 1);
      w.tin.forEach(function (tw, j) { aim(tw, show ? 1 : 0, show ? T_IN : T_OUT, show ? j * T_STAG : 0); });
      var vis = 0;
      for (var j = 0; j < w.lines.length; j++) {
        var tw = w.tin[j], i = val(tw);
        if (busy(tw) || now < tw.at) moving = true;
        var leaving = tw.to === 0;
        var ty = leaving ? -(1 - i) * LIFT : (1 - i) * RISE;
        var s = i.toFixed(3) + '|' + ty.toFixed(3);
        if (w.sty[j] !== s) {
          w.sty[j] = s;
          w.lines[j].style.opacity = i.toFixed(3);
          w.lines[j].style.transform = Math.abs(ty) > 1e-4 ? 'translate3d(0,' + ty.toFixed(3) + 'em,0)' : 'none';
        }
        vis = Math.max(vis, i);
      }
      // the drift: the point of the photograph under the words, from where it was
      if (vis > 0.001) {
        var ru = Math.min(Math.max(w.a, -0.2) + 0.25, w.z);
        if (!w.ref || w.refU !== ru) {
          var M0 = g.M(w.anchor, ru), cr = Math.cos(-M0.r), sr = Math.sin(-M0.r);
          var dx = w.cx - M0.ox, dy = w.cy - M0.oy;
          w.ref = [(cr * dx - sr * dy) / M0.s, (sr * dx + cr * dy) / M0.s]; w.refU = ru;
        }
        var M = g.M(w.anchor, Math.min(u, def.dim ? def.dim[0] : u)), c2 = Math.cos(M.r), s2 = Math.sin(M.r);
        var X = M.ox + M.s * (c2 * w.ref[0] - s2 * w.ref[1]), Y = M.oy + M.s * (s2 * w.ref[0] + c2 * w.ref[1]);
        var lim = 0.04 * vp.vh;
        var ox = Math.max(-lim, Math.min(lim, (X - w.cx) * FOLLOW)), oy = Math.max(-lim, Math.min(lim, (Y - w.cy) * FOLLOW));
        var os = 'translate3d(' + ox.toFixed(2) + 'px,' + oy.toFixed(2) + 'px,0)';
        if (os !== w.off) { w.off = os; w.el.style.transform = os; }
      }
    });
  }
  function setLine(b, j, op, i) {
    var ty = (1 - i) * RISE, s = op.toFixed(3) + '|' + ty.toFixed(3);
    if (b.sty[j] === s) return; b.sty[j] = s;
    var el = b.lines[j];
    el.style.opacity = op.toFixed(3);
    if (b.kids.length) b.kids.forEach(function (k) { k.style.opacity = op < 0.005 ? '0' : ''; });
    el.style.transform = ty ? 'translate3d(0,' + ty.toFixed(3) + 'em,0)' : 'none';
  }
  function drawStill(st) {
    var p = clamp((x - st.top) / st.run);
    st.beats.forEach(function (b) {
      var show = p >= b.a || b.el.contains(document.activeElement);
      b.tin.forEach(function (tw, j) { aim(tw, show ? 1 : 0, show ? T_IN : T_OUT, show ? j * T_STAG : 0); });
      var vis = 0;
      for (var j = 0; j < b.lines.length; j++) {
        var i = val(b.tin[j]);
        if (busy(b.tin[j]) || now < b.tin[j].at) moving = true;
        setLine(b, j, i, i); vis = Math.max(vis, i);
      }
      if (b.act) b.el.style.pointerEvents = vis > 0.6 ? 'auto' : 'none';
    });
    var sc = 'scale(' + (1 + 0.06 * (1 - E(st.welcome ? p / 0.6 : p))).toFixed(4) + ')';
    if (st.sv.sc !== sc) { st.sv.sc = sc; st.pics.forEach(function (el) { el.style.transform = sc; }); }
    if (st.welcome) {
      var o = E(p / 0.35).toFixed(4);
      if (st.sv.o !== o) { st.sv.o = o; st.el.style.setProperty('--o', o); }
    }
  }

  // ---- one smoothed scroll position --------------------------------------------------
  var x = window.pageYOffset, y = x, last = now, running = false, lastScroll = 0, snapped = false;
  function follow(cur, to, dt) {
    var d = to - cur, r = d / (LAG * window.innerHeight);
    return cur + d * (1 - Math.exp(-dt * (1 + r * r) / TAU));
  }
  function render() {
    moving = false;
    var vh = window.innerHeight;
    R.forEach(function (r) {
      var b = r.top - x;
      r.near = b < 2.5 * vh && b + (r.g.U + 1) * vp.vh > -1.5 * vh;
      if (!r.near) {
        // far: its files go (they come back 1.4 screens before they are needed)
        if (r.shots.some(function (s) { return s.want; })) r.shots.forEach(unload);
        return;
      }
      r.u = (x - r.top) / vp.vh;
      drawReel(r);
    });
    S.forEach(function (st) {
      var b = st.top - x;
      st.near = b < 2 * vh && b + st.run + vh > -vh;
      if (st.near) drawStill(st);
    });
  }
  function tick(t) {
    now = t;
    var dt = Math.min(0.05, Math.max(0, (t - last) / 1000)); last = t;
    var T = window.pageYOffset;
    if (snapped) { x = y = T; snapped = false; }
    y = follow(y, T, dt); x = follow(x, y, dt);
    var settledNow = Math.abs(T - x) < 0.25 && Math.abs(T - y) < 0.25;
    if (settledNow) x = y = T;
    render();
    if (settledNow && !moving && t - lastScroll > 200) { running = false; return; }
    requestAnimationFrame(tick);
  }
  function kick() {
    if (running) return;
    running = true; last = performance.now();
    requestAnimationFrame(tick);
  }
  window.addEventListener('scroll', function () { lastScroll = performance.now(); kick(); }, { passive: true });
  var lastW = window.innerWidth, lastH = window.innerHeight;
  window.addEventListener('resize', function () {
    // a phone's toolbar coming and going changes only the height by a little: the
    // stage is 100svh and does not move, so nothing is laid out again
    if (window.innerWidth === lastW && Math.abs(window.innerHeight - lastH) < 120 && R[0].stage.clientHeight === vp.vh) { kick(); return; }
    lastW = window.innerWidth; lastH = window.innerHeight;
    layout(); kick();
  });
  document.addEventListener('visibilitychange', function () { if (!document.hidden) kick(); });

  layout();
  // the opening photograph is asked for at once, and the page fades up from night
  // once it is decoded (at most 1.5 s after this script: night is a good enough
  // ground to land the words on), arriving 3% closer and settling back
  (function () {
    var r = R[0], s = r.shots[0];
    if (!r.intro) return;
    load(s);
    var done = false, t0 = performance.now();
    var go = function () {
      if (done) return; done = true; now = performance.now();
      aim(r.intro, 1, T_INTRO); aim(r.settle, 1, T_SETTLE); root.classList.add('pq-lit'); kick();
    };
    (function wait() { if (s.L[0].st >= 2 || performance.now() - t0 > 1500) go(); else setTimeout(wait, 30); })();
  })();
  // the words are measured with the page's own font: until then they are not laid
  // out in view (visibility), so their size settling is never a shift on screen
  var typed = function () { if (root.classList.contains('pq-typed')) return; layout(); root.classList.add('pq-typed'); kick(); };
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { layout(); typed(); kick(); });
  setTimeout(typed, 2500);
  window.addEventListener('load', function () { measure(); kick(); });
  kick();

  // ---- debug: #pq-debug ------------------------------------------------------------------
  if (/pq-debug/.test(location.hash)) {
    var finish = function (tw) { tw.from = tw.to; tw.at = 0; };
    window.__pq = {
      MATH: MATH, R: R, vp: function () { return vp; },
      // put reel id at u, with the words and fades settled in their final state
      seek: function (id, u, settle) {
        var r = R.filter(function (q) { return q.id === id; })[0];
        measure();
        window.scrollTo(0, Math.round(r.top + u * vp.vh));
        x = y = window.pageYOffset; snapped = true;
        now = performance.now();
        render();
        if (settle) {
          R.forEach(function (q) { q.words.forEach(function (w) { w.tin.forEach(finish); }); q.shots.forEach(function (s) { finish(s.vt); }); if (q.intro) { finish(q.intro); finish(q.settle); } });
          S.forEach(function (st) { st.beats.forEach(function (b) { b.tin.forEach(finish); }); });
          render();
        }
        return { u: (x - r.top) / vp.vh };
      },
      // hold a shot's fade at v (for tools that look at a dissolve half-way)
      hold: function (id, shot, v) {
        var r = R.filter(function (q) { return q.id === id; })[0], s = r.shots.filter(function (q) { return q.sh.id === shot; })[0];
        s.vt.from = v; s.vt.to = 1; s.vt.at = 1e15; now = performance.now(); render();
      },
      // files of the shots now near that are still loading
      pending: function () {
        var n = 0;
        R.forEach(function (q) { if (q.near) q.shots.forEach(function (s) { if (s.want) s.L.forEach(function (l) { if (l.c && l.st === 1) n++; }); }); });
        return n;
      },
      redraw: function () { R.forEach(function (q) { q.shots.forEach(function (s) { s.L.forEach(function (l) { l.k = ''; }); }); }); now = performance.now(); render(); },
      state: function (id) {
        var r = R.filter(function (q) { return q.id === id; })[0], g = r.g;
        g.memo = {};
        return r.shots.map(function (s) {
          var on = s.op[0] || s.op[1], M = on ? g.M(s.sh.id, r.u) : null;
          return { id: s.sh.id, live: !!on, set: s.set, op0: s.op[0], op1: s.op[1], want: s.want,
                   z: M ? M.s / vp.wc : null, r: M ? M.r * 180 / Math.PI : null, slid: M && M.slid, grown: M && M.grown,
                   // file pixels per CSS pixel of the level on screen
                   dens: M ? s.L.map(function (l) { return l.c && l.im.naturalWidth ? l.im.naturalWidth / ((l.c[2] - l.c[0]) * M.s) : null; }) : null,
                   layers: s.L.filter(function (l) { return l.on; }).length,
                   src: s.L.map(function (l) { return l.url; }) };
        });
      }
    };
  }
})();
