/* /por-que-nosotros — the scroll drives the films and the words.

   Each [data-scene] is a tall section whose stage sticks for one screen. Its
   progress p runs 0→1 across the scroll that keeps it stuck. From p:
   - a film's currentTime is eased toward p × duration (the films are encoded
     for this: 24 fps, a keyframe every 12 frames, no B-frames, so a seek lands
     fast on any phone);
   - every .wy-beat is .is-on while p is inside [data-in, data-out), .is-past
     after; the film dims a little under words so they stay legible;
   - a still gets --p (the portrait settles) and the welcome --o (it opens).
   Phones get the upright files, anything wider the 16:9 ones; turning the
   phone swaps them at the same moment of the film.
   Without IntersectionObserver or with reduced motion nothing runs and the CSS
   shows the plain page (html:not(.wy-on)). */
(function () {
  'use strict';
  var root = document.documentElement;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var scenes = Array.prototype.slice.call(document.querySelectorAll('[data-scene]'));
  if (!scenes.length) return;

  var upright = window.matchMedia('(max-aspect-ratio: 1/1)');
  function pick(v) {
    var k = upright.matches ? 'phone' : 'desk';
    return { src: v.getAttribute('data-' + k), poster: v.getAttribute('data-poster-' + k) };
  }
  // posters always (also in the plain version)
  scenes.forEach(function (s) {
    var v = s.querySelector('video'); if (v) v.poster = pick(v).poster;
  });
  if (reduce || !('IntersectionObserver' in window) || !window.requestAnimationFrame) return;
  root.classList.add('wy-on');

  var state = scenes.map(function (s) {
    var v = s.querySelector('video');
    return {
      el: s, v: v, loaded: false, t: 0, p: -1, near: false,
      beats: Array.prototype.slice.call(s.querySelectorAll('.wy-beat')).map(function (b) {
        return { el: b, a: parseFloat(b.getAttribute('data-in')), z: parseFloat(b.getAttribute('data-out')), st: '' };
      })
    };
  });

  function load(st) {
    if (!st.v || st.loaded) return;
    st.loaded = true;
    var f = pick(st.v);
    st.v.preload = 'auto';
    st.v.src = f.src;
    st.v.load();
    // iOS only lets a muted inline video seek once it has been played
    var p = st.v.play && st.v.play();
    if (p && p.then) p.then(function () { st.v.pause(); }, function () {});
  }

  // load the next film well before it arrives (one screen and a half)
  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      var st = state[scenes.indexOf(e.target)];
      st.near = e.isIntersecting;
      if (e.isIntersecting) load(st);
    });
  }, { rootMargin: '150% 0px 150% 0px' });
  scenes.forEach(function (s) { io.observe(s); });

  upright.addEventListener && upright.addEventListener('change', function () {
    state.forEach(function (st) {
      if (!st.v) return;
      var f = pick(st.v); st.v.poster = f.poster;
      if (st.loaded) { var t = st.v.currentTime; st.v.src = f.src; st.v.load(); st.v.currentTime = t; }
    });
  });

  function progress(s) {
    var r = s.getBoundingClientRect(), run = r.height - window.innerHeight;
    if (run <= 0) return r.top <= 0 ? 1 : 0;
    return Math.min(1, Math.max(0, -r.top / run));
  }

  function frame() {
    state.forEach(function (st) {
      if (!st.near) return;
      var p = progress(st.el);
      if (Math.abs(p - st.p) > 0.0005) {
        st.p = p;
        st.el.style.setProperty('--p', p.toFixed(4));
        st.el.style.setProperty('--o', Math.min(1, p / 0.35).toFixed(4));
        var lit = false;
        st.beats.forEach(function (b) {
          var s = p < b.a ? '' : (p < b.z ? 'on' : 'past');
          if (s === 'on') lit = true;
          if (s !== b.st) {
            b.el.classList.toggle('is-on', s === 'on');
            b.el.classList.toggle('is-past', s === 'past');
            b.st = s;
          }
        });
        if (st.v) st.el.style.setProperty('--dim', lit ? '.66' : '1');
      }
      // the film follows the scroll, eased, one seek at a time
      var v = st.v;
      if (v && st.loaded && v.readyState >= 1 && v.duration) {
        var target = st.p * (v.duration - 0.05);
        st.t += (target - st.t) * 0.2;
        if (Math.abs(target - st.t) < 0.004) st.t = target;
        if (!v.seeking && Math.abs(v.currentTime - st.t) > 0.03) {
          try { v.currentTime = st.t; } catch (e) {}
        }
      }
    });
    requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);

  // the record counts up once, when it is seen
  var nums = document.querySelectorAll('.wy-proof [data-count]');
  var fmt = function (v) { return Math.round(v).toLocaleString('es-ES'); };
  Array.prototype.forEach.call(nums, function (n) { n.textContent = '0'; });
  var count = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return; count.unobserve(e.target);
      var el = e.target, to = +el.getAttribute('data-count'), t0 = null;
      (function tick(t) {
        if (!t0) t0 = t; var k = Math.min(1, (t - t0) / 1400);
        el.textContent = fmt(to * (1 - Math.pow(1 - k, 3)));
        if (k < 1) requestAnimationFrame(tick);
      })(performance.now());
    });
  }, { threshold: .6 });
  Array.prototype.forEach.call(nums, function (n) { count.observe(n); });
})();
