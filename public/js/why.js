/* /por-que-nosotros — the films play in chapters; the scroll decides which.

   Scrubbing a film with the scroll (seeking a video, then a sequence of stills)
   steps: ~0.2 s of footage per step, and the eye sees every one. So the films
   are not scrubbed. Each is cut into chapters at the cars, one per line of
   text (<video data-ends>: the end of each chapter, in seconds; the encode
   puts a keyframe on each). Scrolling into a line PLAYS the film natively on
   to the end of its chapter, where it holds on a composed frame; the line
   arrives with it. Scrolling back, or forward past more than one line: a
   short dissolve (the current frame copied to the <canvas> above the video,
   faded out once the video stands on the new frame) to that chapter.
   Phones (taller than wide) get the upright file, wider screens the 16:9 one;
   turning the phone swaps them at the same moment of the film. Film A loads at
   once (its first frame is the preloaded poster underneath), film B when it is
   a screen and a half away. If a phone refuses to play (iOS Low Power Mode),
   the film still dissolves from held frame to held frame.

   Everything else follows one smoothed scroll position (two exponential
   stages, time-based, critically damped) on one curve (smoothstep): films
   rise out of the page white and dissolve back into it, every picture settles
   from 106% to 100% across its scene, the welcome photograph opens. The words
   and the shade under them move on time, on the same curve: a line rises
   0.32em and sharpens from a 6px blur in 0.7 s, leaves in 0.45 s. The
   opening fades up from night inside a 2.39 scope frame, the title settles in
   the black beneath it (its tracking closes to its own; no rise), and the
   frame opens (.wy-open, CSS) as film A's first chapter rolls. Film A's
   chapters end on held frames the encode ramps down into, so a pause a few
   frames late still holds the same picture.
   The loop runs only while something moves.
   Without IntersectionObserver, or with reduced motion, nothing runs and the
   CSS shows the plain page (html:not(.wy-on)). */
(function () {
  'use strict';
  var root = document.documentElement;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var scenes = Array.prototype.slice.call(document.querySelectorAll('[data-scene]'));
  if (!scenes.length || reduce || !('IntersectionObserver' in window) || !window.requestAnimationFrame) { root.classList.remove('wy-on'); return; }
  root.classList.add('wy-on');

  var TAU = 0.11, LAG = 0.35;                  // the follow: s per stage; screens before it tightens
  var T_IN = 0.7, T_OUT = 0.45, T_STAG = 0.12; // s: a line arrives, leaves; the next line after
  var RISE = 0.32, LIFT = 0.22, BLUR = 6;      // em up on arrival, em up on leaving, px of blur arriving
  var PUSH = 0.06;                             // every picture eases from 106% to 100% across its scene
  var DIP_IN = 0.8, DIP_OUT = 0.6;             // screens: a film rises from / dissolves to the page
  var ENTER = 0.15;                            // screens before its top a film starts its first chapter
  var XF = 0.32;                               // s: the dissolve between chapters
  var INTRO = [0.9, 0.45, 1.05];               // s: night fades; first line starts; the film rolls after
  // the title (film A's h1) settles like a film's main title: it arrives in its
  // place and its tracking closes from +.02em to its own -.04em, in focus, with no
  // rise; slower than a line (T_TITLE). Never if the wider tracking would re-wrap a line.
  var T_TITLE = 1.3, TRACK = 0.06, T_STAG_T = 0.18;

  var E = function (t) { t = t < 0 ? 0 : t > 1 ? 1 : t; return t * t * (3 - 2 * t); };
  var clamp = function (v) { return v < 0 ? 0 : v > 1 ? 1 : v; };
  var upright = window.matchMedia('(max-aspect-ratio: 1/1)');
  var kind = function () { return upright.matches ? 'p' : 'd'; };
  var DIM = { p: [608, 1080], d: [1600, 900] };
  var now = performance.now(), t0 = now, intro = 0;
  // the opening's clock starts when its first frame can be painted, not when this
  // script runs: on a phone the script often ran before the first paint and the
  // fade from night was over before anyone saw it (veil at 0.09 in the first
  // painted frame). It waits at most 1.2 s for the poster; night is dark enough
  // to land the words on if the picture is late.
  var started = false, introStart = function () { if (started) return; started = true; t0 = performance.now(); kick(); };
  (function () {
    var im = document.querySelector('.wy-film .wy-poster img');
    if (!im || (im.complete && im.naturalWidth)) return requestAnimationFrame(introStart);
    im.addEventListener('load', function () { requestAnimationFrame(introStart); });
    im.addEventListener('error', introStart);
    setTimeout(introStart, 1200);
  })();
  var introEnd = INTRO[1] + T_TITLE + T_STAG_T;
  var vh = window.innerHeight;

  // a value that eases on time towards whatever it is told
  function tween(v) { return { from: v, to: v, at: 0, dur: 1 }; }
  function aim(tw, to, dur) {
    if (tw.to === to) return;
    tw.from = val(tw); tw.to = to; tw.at = now; tw.dur = dur;
  }
  function val(tw) { return tw.from + (tw.to - tw.from) * E((now - tw.at) / 1000 / tw.dur); }
  function busy(tw) { return (now - tw.at) / 1000 < tw.dur && tw.from !== tw.to; }

  var S = scenes.map(function (el, si) {
    var v = el.querySelector('video.wy-video');
    var st = {
      el: el, top: 0, run: 1, near: false, sv: {}, key: '',
      first: si === 0 && !!v,
      veil: el.querySelector('.wy-veil'), shade: el.querySelector('.wy-shade'),
      pics: Array.prototype.slice.call(el.querySelectorAll('.wy-video, .wy-canvas, .wy-poster, .wy-photo > img')),
      still: el.classList.contains('wy-still--welcome') ? 'o' : null,
      beats: Array.prototype.slice.call(el.querySelectorAll('.wy-beat')).map(function (b) {
        var whole = b.classList.contains('wy-beat--sub') || b.classList.contains('wy-beat--act');
        var lines = whole ? [b] : Array.prototype.slice.call(b.querySelectorAll('.wy-l'));
        return { el: b, a: parseFloat(b.getAttribute('data-in')), z: parseFloat(b.getAttribute('data-out')),
                 act: b.classList.contains('wy-beat--act'), lines: lines, sty: [],
                 title: si === 0 && b.classList.contains('wy-beat--open'),
                 tin: lines.map(function () { return tween(0); }), tout: tween(0) };
      })
    };
    if (v) {
      st.v = v; st.c = el.querySelector('canvas.wy-canvas');
      st.ctx = st.c && st.c.getContext && st.c.getContext('2d');
      st.ends = v.getAttribute('data-ends').split(',').map(Number);
      st.cur = -1;          // the chapter on screen (-1: the first frame, before the first chapter)
      st.stop = 0;          // where the playing film stops
      st.playing = false; st.loaded = false; st.xf = tween(0); st.pend = null;
    }
    return st;
  });
  var films = S.filter(function (s) { return s.v; });

  // ---- the films ---------------------------------------------------------------
  function load(st) {
    if (st.loaded) return;
    st.loaded = true; st.kind = kind();
    var v = st.v;
    v.poster = st.el.querySelector('.wy-poster img').currentSrc || '';
    v.preload = 'auto';
    v.src = v.getAttribute('data-' + st.kind);
    v.addEventListener('seeked', function () { if (st.pend) { var p = st.pend; st.pend = null; p(); } kick(); });
    v.addEventListener('loadeddata', kick);
    v.addEventListener('pause', function () { st.playing = false; kick(); });
    // the browser may start it again by itself (a tab shown again): the loop must
    // be watching, or it plays on through every chapter with the wrong words
    v.addEventListener('play', function () { st.playing = true; kick(); });
    try { v.load(); } catch (e) {}
  }
  function startOf(st, k) { return k <= 0 ? 0 : st.ends[k - 1]; }
  function play(st, until) {
    var v = st.v;
    st.stop = until;
    if (v.currentTime >= until - 0.004) { return; }
    st.playing = true;
    // the stop is kept by a timer too, not only by the frame loop: a busy page
    // (or a slow phone) can starve requestAnimationFrame and the film ran on
    // 0.6 s into the next move before the loop saw it (measured)
    clearTimeout(st.stopT);
    (function hold() {
      if (st.stop !== until) return;
      var left = until - v.currentTime;
      if (left <= 0.02) { if (!v.paused) v.pause(); return; }
      st.stopT = setTimeout(hold, Math.max(8, left * 1000 / (v.playbackRate || 1) - 12));
    })();
    var p = v.play();
    if (p && p.catch) p.catch(function () {
      // no playing allowed: dissolve to the chapter's held frame instead
      st.playing = false; jump(st, until, false);
    });
  }
  // the current frame onto the canvas, then the video to t; fade when it stands there
  function snap(st) {
    var v = st.v, c = st.c, x = st.ctx;
    if (!x || v.readyState < 2) return false;
    var cw = c.clientWidth, ch = c.clientHeight, d = DIM[st.kind || kind()];
    var k = Math.min(Math.min(window.devicePixelRatio || 1, 2), 1 / Math.max(cw / d[0], ch / d[1]));
    var w = Math.round(cw * k), h = Math.round(ch * k);
    if (!w || !h) return false;
    if (c.width !== w || c.height !== h) { c.width = w; c.height = h; }
    var iw = v.videoWidth, ih = v.videoHeight, sc = Math.max(w / iw, h / ih), sw = w / sc, sh = h / sc;
    try { x.drawImage(v, (iw - sw) / 2, (ih - sh) / 2, sw, sh, 0, 0, w, h); } catch (e) { return false; }
    return true;
  }
  function jump(st, t, thenPlay) {
    var v = st.v;
    if (!v.paused) v.pause();
    st.playing = false;
    if (thenPlay === false) st.stop = t;   // held here: nothing left to play to
    var had = snap(st);
    st.xf = tween(had ? 1 : 0);
    css(st, st.c, 'opacity', had ? '1' : '0');
    st.pend = function () {
      // the new frame is decoded: dissolve the old one away
      // (a paused video may already have shown it: a timer as well, whichever comes first)
      var done = false, go = function () { if (done) return; done = true; aim(st.xf, 0, XF); if (thenPlay !== false) play(st, thenPlay); kick(); };
      if (v.requestVideoFrameCallback) v.requestVideoFrameCallback(go);
      setTimeout(go, 90);
    };
    try { v.currentTime = t; } catch (e) { st.pend = null; }
    if (Math.abs(v.currentTime - t) < 0.001 && !v.seeking && st.pend) { var p = st.pend; st.pend = null; p(); }
  }
  // which chapter the scroll asks for
  function wanted(st, p, ready) {
    var k = -1;
    if (st.first ? (intro >= INTRO[1] + INTRO[2] || p > 0.02) : (x >= st.top - ENTER * vh)) k = 0;
    if (!ready) return k;
    for (var i = 1; i < st.beats.length && i < st.ends.length; i++) if (p >= st.beats[i].a) k = i;
    return k;
  }
  function steer(st, p) {
    var v = st.v;
    if (!st.loaded || v.readyState < 1) return;
    var want = wanted(st, p, true);
    if (!v.paused && v.currentTime >= st.stop - 0.008) { v.pause(); st.playing = false; }
    // stopped short of its chapter's end (paused while far away, or while the tab
    // was hidden) and back on screen: play on to it, never hold a frame mid-move
    if (want === st.cur && st.cur >= 0 && v.paused && !v.seeking && !st.pend && !document.hidden &&
        v.currentTime < st.stop - 0.05 && Math.abs(x - st.top) < st.run + vh) { play(st, st.stop); return; }
    if (want === st.cur || v.seeking || st.pend) return;
    if (want > st.cur && v.currentTime >= startOf(st, want) - 0.5) {   // the next line: play on
      st.cur = want; play(st, st.ends[want]);
    } else if (want > st.cur) {              // further on: dissolve to its start, then play
      st.cur = want; jump(st, startOf(st, want), st.ends[want]);
    } else {                                 // back: dissolve to its held frame
      st.cur = want; jump(st, want < 0 ? 0 : st.ends[want], false);
    }
  }
  function swap() {
    films.forEach(function (st) {
      if (!st.loaded || st.kind === kind()) return;
      var v = st.v, t = v.currentTime, was = st.playing;
      st.kind = kind(); st.playing = false;
      v.poster = st.el.querySelector('.wy-poster img').currentSrc || '';
      v.src = v.getAttribute('data-' + st.kind);
      v.addEventListener('loadedmetadata', function once() {
        v.removeEventListener('loadedmetadata', once);
        try { v.currentTime = t; } catch (e) {}
        if (was) play(st, st.stop);
      });
      try { v.load(); } catch (e) {}
    });
    kick();
  }
  if (upright.addEventListener) upright.addEventListener('change', swap); else if (upright.addListener) upright.addListener(swap);

  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      var st = S[scenes.indexOf(e.target)];
      st.near = e.isIntersecting;
      if (st.near && st.v) load(st);
      if (!st.near && st.v && st.playing) st.v.pause();
    });
    kick();
  }, { rootMargin: '150% 0px 150% 0px' });
  scenes.forEach(function (s) { io.observe(s); });

  // ---- one smoothed scroll position --------------------------------------------
  var x = window.pageYOffset, y = x, last = now, running = false, lastScroll = 0;
  function measure() {
    var sy = window.pageYOffset;
    S.forEach(function (st) {
      var r = st.el.getBoundingClientRect();
      st.top = r.top + sy; st.run = Math.max(1, r.height - vh);
    });
    var lead = Math.round(S[0].top) + 'px';    // the opening words sit above the header's height
    if (lead !== S[0].lead) { S[0].lead = lead; S[0].el.style.setProperty('--wy-lead', lead); }
    // the opening's scope frame sits above the title: its lower edge clears the
    // h1 by 12px (shifted up only where a centred band would cross it)
    var st0 = S[0], h1 = st0.first && !st0.open && st0.beats[0] && st0.beats[0].el;
    if (h1) {
      var stage = st0.el.querySelector('.wy-stage').getBoundingClientRect(), H = stage.height,
          band = Math.min(H, window.innerWidth / 2.39), room = h1.getBoundingClientRect().top - stage.top - 12,
          shift = Math.max(0, Math.min((H - band) / 2, (H + band) / 2 - room));
      var sh = Math.round(shift) + 'px';
      if (sh !== st0.shift) { st0.shift = sh; st0.el.style.setProperty('--wy-band-shift', sh); }
    }
  }
  function follow(cur, to, dt) {
    var d = to - cur, r = d / (LAG * vh);
    return cur + d * (1 - Math.exp(-dt * (1 + r * r) / TAU));
  }
  function css(st, el, k, v) {
    var key = k + '|' + st.pics.indexOf(el) + '|' + (el.className || '');
    if (st.sv[key] === v) return; st.sv[key] = v;
    if (k.charAt(0) === '-') el.style.setProperty(k, v); else el.style[k] = v;
  }
  // would the title's wider tracking re-wrap any of its lines? (measured, per width)
  var trackOk = null, trackW = -1;
  function canTrack(b) {
    if (trackW === window.innerWidth && trackOk !== null) return trackOk;
    trackW = window.innerWidth; trackOk = true;
    b.lines.forEach(function (el) {
      var keep = el.style.letterSpacing, h0;
      el.style.letterSpacing = ''; h0 = el.getBoundingClientRect().height;
      el.style.letterSpacing = (-0.04 + TRACK) + 'em';
      if (el.getBoundingClientRect().height > h0 + 1) trackOk = false;
      el.style.letterSpacing = keep;
    });
    return trackOk;
  }
  function setLine(b, j, op, i, o) {
    var tr = b.title && canTrack(b) ? (1 - i) * TRACK : 0;
    var ty = (b.title ? 0 : (1 - i) * RISE) - o * LIFT, bl = (1 - i) * BLUR;
    var s = op.toFixed(3) + '|' + ty.toFixed(3) + '|' + bl.toFixed(1) + '|' + tr.toFixed(4);
    if (b.sty[j] === s) return;
    b.sty[j] = s;
    var el = b.lines[j];
    if (b.title) el.style.letterSpacing = tr ? (-0.04 + tr).toFixed(4) + 'em' : '';
    el.style.opacity = op.toFixed(3);
    el.style.transform = ty ? 'translate3d(0,' + ty.toFixed(3) + 'em,0)' : 'none';
    el.style.filter = bl >= 0.1 ? 'blur(' + bl.toFixed(1) + 'px)' : 'none';
  }
  var moving = false;
  function render() {
    moving = false;
    S.forEach(function (st) {
      if (!st.near) return;
      var p = clamp((x - st.top) / st.run);
      if (st.v) steer(st, p);
      // the opening's scope frame opens as the camera starts to move (CSS transition)
      if (st.first && !st.open && st.cur >= 0) { st.open = true; st.el.classList.add('wy-open'); }
      // which lines are on: a film's line is its chapter's; a still's by scroll
      var on = st.v ? st.cur : -1;
      // all of a film's lines share one place: one that arrives while another is
      // still leaving waits for it (two lines a third visible over each other read
      // as a smudge — measured 250 ms of it on every chapter change)
      var wait = 0;
      if (st.v) st.beats.forEach(function (b, bi) {
        if (bi !== on && !(bi === st.beats.length - 1 && on >= bi) && b.lines.some(function (l) { return +l.style.opacity > 0.05; })) wait = T_OUT * 0.65;
      });
      st.beats.forEach(function (b, bi) {
        var show;
        if (st.v) show = bi === on || (bi === st.beats.length - 1 && on >= bi);
        else show = p >= b.a || b.el.contains(document.activeElement);
        if (st.v && bi === 0 && st.first && on < 0) show = intro >= INTRO[1];
        if (st.v && on < 0 && bi === 0 && !st.first) show = false;
        var gone = st.v ? (on > bi && bi !== st.beats.length - 1) : false;
        b.tin.forEach(function (tw, j) {
          aim(tw, show || gone ? 1 : 0, (show || gone) ? (b.title ? T_TITLE : T_IN) : T_OUT);
          if (tw.to === 1 && tw.from === 0 && tw.at === now) tw.at = now + (j * (b.title ? T_STAG_T : T_STAG) + (show && st.v ? wait : 0)) * 1000;
        });
        aim(b.tout, gone ? 1 : 0, T_OUT);
        // back to an earlier line: it waits for the later one to leave, too
        if (st.v && b.tout.to === 0 && b.tout.from > 0.5 && b.tout.at === now) b.tout.at = now + wait * 1000;
      });
      var dipIn = st.first ? 0 : 1 - E((x - (st.top - vh)) / (DIP_IN * vh));
      var dipOut = E((x - (st.top + st.run)) / (DIP_OUT * vh));
      var night = st.first && x < 0.3 * vh ? 1 - E(intro / INTRO[0]) : 0;
      var dip = Math.max(dipIn, dipOut);
      if (st.veil) {
        css(st, st.veil, 'opacity', Math.max(dip, night).toFixed(3));
        css(st, st.veil, 'backgroundColor', night > dip ? '#05080F' : 'var(--mc-surface)');
      }
      var lit = 0;
      st.beats.forEach(function (b) {
        var o = val(b.tout), vis = 0;
        if (busy(b.tout)) moving = true;
        for (var j = 0; j < b.lines.length; j++) {
          var tw = b.tin[j], i = now < tw.at ? tw.from : val(tw);
          if (busy(tw) || now < tw.at) moving = true;
          var op = i * (1 - o) * (st.v ? 1 - E(dip / 0.5) : 1);
          setLine(b, j, op, i, o);
          vis = Math.max(vis, op);
        }
        if (b.act) b.el.style.pointerEvents = vis > 0.6 ? 'auto' : 'none';
        lit = Math.max(lit, vis);
      });
      if (st.shade) css(st, st.shade, 'opacity', lit.toFixed(3));
      if (st.v) {
        if (busy(st.xf)) moving = true;
        css(st, st.c, 'opacity', val(st.xf).toFixed(3));
        if (st.playing || st.pend) moving = true;
      }
      var sc = 'scale(' + (1 + PUSH * (1 - E(st.still === 'o' ? p / 0.6 : p))).toFixed(4) + ')';
      st.pics.forEach(function (el) { css(st, el, 'transform', sc); });
      if (st.still === 'o') css(st, st.el, '--o', E(p / 0.35).toFixed(4));
    });
  }
  function tick(t) {
    now = t;
    var dt = Math.min(0.05, Math.max(0, (t - last) / 1000)); last = t;
    intro = started ? (t - t0) / 1000 : 0;
    measure();
    var T = window.pageYOffset;
    y = follow(y, T, dt); x = follow(x, y, dt);
    var settled = Math.abs(T - x) < 0.25 && Math.abs(T - y) < 0.25;
    if (settled) x = y = T;
    render();
    if (settled && !moving && t - lastScroll > 200 && started && intro > introEnd + 1) { running = false; return; }
    requestAnimationFrame(tick);
  }
  function kick() {
    if (running) return;
    running = true; last = performance.now();
    requestAnimationFrame(tick);
  }
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) films.forEach(function (st) { if (st.loaded && !st.v.paused) st.v.pause(); });
    else kick();
  });
  // where in which scene the reader is, so a turned phone lands on the same line:
  // the scroll keeps its pixels, but every scene is a multiple of the screen's
  // height (turned on "Revisados", the page landed past the end of the film)
  var anchor = null, lastW = window.innerWidth, turnedAt = -1e9;
  function place() {
    var sy = window.pageYOffset; anchor = null;
    for (var i = 0; i < S.length; i++) {
      var r = S[i].el.getBoundingClientRect();
      if (r.top <= 0 && r.bottom > 0) { anchor = { el: S[i].el, p: -r.top / Math.max(1, r.height - window.innerHeight) }; break; }
    }
  }
  window.addEventListener('scroll', function () {
    lastScroll = performance.now();
    if (lastScroll - turnedAt > 400 && window.innerWidth === lastW) place();
    kick();
  }, { passive: true });
  window.addEventListener('resize', function () {
    vh = window.innerHeight;
    if (window.innerWidth !== lastW) {
      lastW = window.innerWidth; turnedAt = performance.now();
      var a = anchor;
      if (a) requestAnimationFrame(function () {
        var r = a.el.getBoundingClientRect();
        window.scrollTo(0, Math.round(r.top + window.pageYOffset + a.p * Math.max(1, r.height - window.innerHeight)));
        x = y = window.pageYOffset;
      });
    }
    kick();
  });
  // a phone that only lets a video start after a touch: try again on the first one
  window.addEventListener('touchstart', function retry() {
    window.removeEventListener('touchstart', retry);
    films.forEach(function (st) { if (st.loaded && st.cur >= 0 && st.v.paused && st.v.currentTime < st.stop - 0.01) play(st, st.stop); });
  }, { passive: true });

  measure(); place();
  S.forEach(function (st) { var r = st.el.getBoundingClientRect(); st.near = r.bottom > -vh && r.top < 2.5 * vh; if (st.near && st.v) load(st); });
  kick();
})();
