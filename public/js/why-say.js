/* /por-que-nosotros — "Lo cuentan ellos" shows there are many more than three.
   When a card comes into view its photo panel flips, fast and then slower,
   through the other buyers' delivery photos (with their names under it), and
   lands on its own review; the quote then rises in. The pool comes from the
   page (data-pool on the list: small 480 px files, decoded before the first
   flip so no flip ever shows an empty panel). Cards that are off screen in
   the phone's sideways row wait until they are swiped in.
   Reduced motion, no pool or no Web Animations: the cards are simply there. */
(function () {
  'use strict';
  var list = document.querySelector('.wy-say__l');
  if (!list) return;
  var pool = [];
  try { pool = JSON.parse(list.getAttribute('data-pool') || '[]'); } catch (e) {}
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce || pool.length < 6 || !('IntersectionObserver' in window) || !Element.prototype.animate) return;

  var cards = Array.prototype.slice.call(list.querySelectorAll('.wy-q'));
  var FLIPS = Math.min(9, pool.length);
  document.documentElement.classList.add('wy-say-on');
  cards.forEach(function (c) { c.classList.add('is-waiting'); });

  // each card gets its own run of faces, no face twice on the page
  for (var i = pool.length - 1; i > 0; i--) { var j = Math.floor(Math.random() * (i + 1)), t = pool[i]; pool[i] = pool[j]; pool[j] = t; }
  var per = Math.max(4, Math.min(FLIPS, Math.floor(pool.length / cards.length)));
  var runs = cards.map(function (c, k) {
    var r = [];
    for (var n = 0; n < per; n++) r.push(pool[(k * per + n) % pool.length]);
    return r;
  });

  // fetch and decode the faces a screen before the section arrives
  var ready = {};
  function warm(run) {
    return Promise.all(run.map(function (f) {
      if (ready[f.s]) return ready[f.s];
      var im = new Image(); im.decoding = 'async'; im.src = f.s;
      ready[f.s] = (im.decode ? im.decode() : new Promise(function (ok) { im.onload = ok; }))
        .then(function () { return im; }, function () { return null; });
      return ready[f.s];
    }));
  }
  var near = new IntersectionObserver(function (es) {
    if (!es.some(function (e) { return e.isIntersecting; })) return;
    near.disconnect();
    runs.forEach(warm);
  }, { rootMargin: '100% 0px 100% 0px' });
  near.observe(list);

  // half a turn: 0 → 90° (edge on), change the face, -90° → 0
  function half(el, from, to, ms, ease) {
    return el.animate([{ transform: 'rotateY(' + from + 'deg)' }, { transform: 'rotateY(' + to + 'deg)' }],
      { duration: ms, easing: ease }).finished;
  }

  function play(card, k) {
    var ph = card.querySelector('.wy-q__ph'), by = card.querySelector('.wy-q__by');
    var own = by.textContent;
    warm(runs[k]).then(function (faces) {
      faces = faces.filter(Boolean);
      if (!faces.length) { land(); return; }
      card.classList.add('is-flipping');
      var flip = document.createElement('img');
      flip.className = 'wy-q__flip'; flip.alt = ''; flip.setAttribute('aria-hidden', 'true');
      flip.src = faces[0].src; ph.appendChild(flip);
      by.setAttribute('aria-hidden', 'true');
      var n = faces.length, step = 0;
      (function next() {
        // fast at first, slower and slower: the deck runs out
        var k2 = step / n, ms = 55 + 230 * k2 * k2;
        half(ph, 0, 90, ms, 'cubic-bezier(.5,0,1,1)').then(function () {
          step++;
          if (step < n) {
            flip.src = faces[step].src;
            by.textContent = runs[k][step].n;
          } else {
            flip.remove(); card.classList.remove('is-flipping');
            by.textContent = own; by.removeAttribute('aria-hidden');
          }
          return half(ph, -90, 0, step < n ? ms : 520, step < n ? 'cubic-bezier(0,0,.5,1)' : 'cubic-bezier(.22,.61,.36,1)');
        }).then(function () { if (step < n) next(); else land(); });
      })();
    });
    function land() { card.classList.remove('is-waiting'); }
  }

  var seen = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      seen.unobserve(e.target);
      var k = cards.indexOf(e.target);
      setTimeout(function () { play(e.target, k); }, (k % 3) * 160);
    });
  }, { threshold: .55 });
  cards.forEach(function (c) { seen.observe(c); });
})();
