/* /muestras/por-que/2 — GALERÍA. Quiet motion, transform and opacity only.

   1  arrival: each [data-r] gets .is-in once, as it comes on screen (CSS does
      the rest: the picture rises 12% into its frame and settles).
   2  drift: every .pq-px on screen moves ±3% of its frame's height as the
      frame crosses the viewport. Scrubbed, so linear, and dt-free: it is a
      function of the scroll position, nothing accumulates.
   3  the welcome opens: its window scales from 90% to 100% as it rises into
      the screen, the picture inside by the inverse (it stands still).
   4  the record rolls in like an odometer (the live page's drums).
   The loop runs only while something on screen can move. Reduced motion, or
   no IntersectionObserver: nothing runs and .pq-on comes off (a still page). */
(function () {
  'use strict';
  var root = document.documentElement;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce || !('IntersectionObserver' in window) || !window.requestAnimationFrame) { root.classList.remove('pq-on'); return; }
  root.classList.add('pq-on');
  window.pq2 = true;

  /* 1 · arrival ------------------------------------------------------------- */
  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      io.unobserve(e.target);
      // a swipe row arrives as one: the slides still off to the side come with it,
      // 70 ms apart, instead of rising in the middle of a swipe
      var kids = e.target.__pqKids;
      if (kids) kids.forEach(function (k, i) { setTimeout(function () { k.classList.add('is-in'); }, i * 70); });
      else e.target.classList.add('is-in');
    });
  }, { rootMargin: '0px 0px -6% 0px', threshold: 0.08 });
  Array.prototype.forEach.call(document.querySelectorAll('[data-r]'), function (el) {
    var row = el.closest('.pq-gal');
    if (!row) return io.observe(el);
    if (!row.__pqKids) { row.__pqKids = []; io.observe(row); }
    row.__pqKids.push(el);
  });

  /* 2 + 3 · drift and the opening ------------------------------------------- */
  // on a phone a plate is a stretched wall over a whole photograph: nothing may move inside it
  var phonePlate = matchMedia('(max-width: 999px) and (max-aspect-ratio: 1/1)');
  var AMP = 0.03;
  var items = [];
  Array.prototype.forEach.call(document.querySelectorAll('.pq-px'), function (px) {
    items.push({ el: px, frame: px.parentNode.closest('.pq-f') || px.parentNode, plate: !!px.closest('.pq-plate'), on: false, last: null });
  });
  var open = document.querySelector('.pq-open'), openIn = open && open.querySelector('.pq-open__in');
  var openItem = open ? { el: open, on: false, last: null } : null;

  var seen = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      var it = e.target.__pq; if (!it) return;
      it.on = e.isIntersecting; kick();
    });
  }, { rootMargin: '10% 0px 10% 0px' });
  items.forEach(function (it) { it.frame.__pq = it; seen.observe(it.frame); });
  if (openItem) { open.__pq = openItem; seen.observe(open); }

  var raf = 0;
  function kick() { if (!raf) raf = requestAnimationFrame(frame); }
  function frame() {
    raf = 0;
    var vh = window.innerHeight, still = phonePlate.matches;
    items.forEach(function (it) {
      if (!it.on) return;
      if (it.plate && still) { if (it.last !== 0) { it.el.style.transform = ''; it.last = 0; } return; }
      var r = it.frame.getBoundingClientRect();
      // -1 as the frame enters at the foot of the screen, +1 as it leaves at the top
      var t = ((vh + r.height) / 2 - (r.top + r.height / 2)) / ((vh + r.height) / 2);
      t = t < -1 ? -1 : t > 1 ? 1 : t;
      var y = Math.round(-t * AMP * r.height * 10) / 10;
      if (y !== it.last) { it.el.style.transform = 'translate3d(0,' + y + 'px,0)'; it.last = y; }
    });
    if (openItem && openItem.on) {
      var r = open.getBoundingClientRect();
      // closed (90%) when its top is at the foot of the screen, open by the time its top reaches 15%
      var p = (vh - r.top) / (vh * 0.85);
      p = p < 0 ? 0 : p > 1 ? 1 : p;
      var s = Math.round((0.9 + 0.1 * p) * 10000) / 10000;
      if (s !== openItem.last) {
        open.style.transform = s === 1 ? '' : 'scale(' + s + ')';
        openIn.style.transform = s === 1 ? '' : 'scale(' + (1 / s) + ')';
        openItem.last = s;
      }
    }
  }
  addEventListener('scroll', kick, { passive: true });
  addEventListener('resize', kick);
  kick();

  /* 4 · the record ---------------------------------------------------------- */
  var nums = Array.prototype.slice.call(document.querySelectorAll('.pq-proof [data-count]'));
  if (!nums.length || !Element.prototype.animate) return;
  var EASE = 'cubic-bezier(.22,.61,.36,1)';   // --e-out
  root.classList.add('pq-odo-on');
  function build(old) {
    var b = old.cloneNode(false);
    var text = (old.getAttribute('data-prefix') || '') + String(+old.getAttribute('data-count') || 0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    b.removeAttribute('data-count');
    var sr = document.createElement('span'); sr.className = 'pq-sr'; sr.textContent = text;
    var odo = document.createElement('span'); odo.className = 'pq-odo'; odo.setAttribute('aria-hidden', 'true');
    var drums = [], digits = text.replace(/\D/g, '').length, seenD = 0;
    text.split('').forEach(function (ch) {
      if (!/\d/.test(ch)) { var s = document.createElement('span'); s.className = 'pq-odo__sep'; s.textContent = ch; odo.appendChild(s); return; }
      var d = +ch, fromRight = digits - 1 - seenD++;
      var n = Math.max(0, 3 - fromRight) * 10 + d, html = '';
      for (var i = 0; i <= n; i++) html += '<i>' + (i % 10) + '</i>';
      var c = document.createElement('span'); c.className = 'pq-odo__c';
      var strip = document.createElement('span'); strip.className = 'pq-odo__s'; strip.innerHTML = html;
      c.appendChild(strip); odo.appendChild(c);
      drums.push({ el: strip, to: n, fromRight: fromRight });
    });
    b.appendChild(sr); b.appendChild(odo);
    old.parentNode.replaceChild(b, old);
    var probe = document.createElement('span'); probe.style.cssText = 'position:absolute;visibility:hidden;white-space:pre;letter-spacing:0';
    b.appendChild(probe);
    var fs = parseFloat(getComputedStyle(b).fontSize);
    Array.prototype.forEach.call(odo.querySelectorAll('.pq-odo__c'), function (c, k) {
      probe.textContent = String(drums[k].to % 10);
      c.style.width = probe.getBoundingClientRect().width / fs + 'em';
    });
    b.removeChild(probe);
    drums.forEach(function (dr) { dr.el.style.transform = 'translateY(0)'; });
    return { b: b, odo: odo, li: b.closest('li'), drums: drums };
  }
  var odos = nums.map(build);
  function roll(it, delay) {
    it.odo.classList.add('is-rolling');
    var last = 0;
    it.drums.forEach(function (dr) {
      if (!dr.to) { dr.el.style.transform = 'none'; return; }
      var dur = 1250 + dr.fromRight * 190, end = -dr.to, give = .07;
      dr.el.animate([
        { transform: 'translateY(0)' },
        { transform: 'translateY(' + (end - give) + 'em)', offset: .9, easing: 'cubic-bezier(.3,.6,.4,1)' },
        { transform: 'translateY(' + end + 'em)' }
      ], { duration: dur, delay: delay, easing: EASE, fill: 'both' });
      last = Math.max(last, dur);
    });
    setTimeout(function () { it.odo.classList.remove('is-rolling'); if (it.li) it.li.classList.add('is-landed'); }, delay + last - 120);
  }
  var queue = [], timer = 0;
  var oio = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      oio.unobserve(e.target);
      queue.push(odos.filter(function (it) { return it.b === e.target; })[0]);
    });
    if (!timer) timer = setTimeout(function () {
      queue.sort(function (a, b) { return odos.indexOf(a) - odos.indexOf(b); });
      queue.forEach(function (it, i) { roll(it, i * 140); });
      queue = []; timer = 0;
    }, 60);
  }, { threshold: 1, rootMargin: '0px 0px -12% 0px' });
  odos.forEach(function (it) { oio.observe(it.b); });
})();
