/* /por-que-nosotros — the record rolls in like an odometer.
   Every figure is a drum. When a number comes into view its drums roll up to
   the value together: the right-hand figure spins the most turns and lands
   first, each one to its left a little later, and each stops with a small
   mechanical give (a few hundredths of a figure past, then back). The dots
   don't move. Its label then rises in. Screen readers get the plain number.
   Reduced motion or no Web Animations: the numbers stay as printed. */
(function () {
  'use strict';
  var nums = Array.prototype.slice.call(document.querySelectorAll('.wy-proof [data-count]'));
  if (!nums.length) return;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce || !('IntersectionObserver' in window) || !Element.prototype.animate) return;

  var EASE = 'cubic-bezier(.22,.61,.36,1)';   // --e-out, the site's curve
  document.documentElement.classList.add('wy-odo-on');

  function build(old) {
    // a fresh node, so nothing else that held the old one can write into it
    var b = old.cloneNode(false);
    // from the value, not the text: an older script may have reset the text
    var text = String(+old.getAttribute('data-count') || 0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    b.removeAttribute('data-count');
    var sr = document.createElement('span'); sr.className = 'wy-odo__sr'; sr.textContent = text;
    var odo = document.createElement('span'); odo.className = 'wy-odo'; odo.setAttribute('aria-hidden', 'true');
    var drums = [];
    var digits = text.replace(/\D/g, '').length, seen = 0;
    text.split('').forEach(function (ch) {
      if (!/\d/.test(ch)) {
        var s = document.createElement('span'); s.className = 'wy-odo__sep'; s.textContent = ch;
        odo.appendChild(s); return;
      }
      var d = +ch, fromRight = digits - 1 - seen++;
      var turns = Math.max(0, 3 - fromRight);          // 3, 2, 1, then straight to the figure
      var c = document.createElement('span'); c.className = 'wy-odo__c';
      var strip = document.createElement('span'); strip.className = 'wy-odo__s';
      var n = turns * 10 + d, html = '';
      for (var i = 0; i <= n; i++) html += '<i>' + (i % 10) + '</i>';
      strip.innerHTML = html;
      c.appendChild(strip); odo.appendChild(c);
      drums.push({ el: strip, to: n, fromRight: fromRight });
    });
    b.appendChild(sr); b.appendChild(odo);
    old.parentNode.replaceChild(b, old);
    // each window is as wide as the figure it stops on (proportional figures,
    // like the printed number); a wider figure passing through is cut at the
    // sides for a few milliseconds, which reads as speed
    var probe = document.createElement('span'); probe.style.cssText = 'position:absolute;visibility:hidden;white-space:pre;letter-spacing:0';
    b.appendChild(probe);
    Array.prototype.forEach.call(odo.querySelectorAll('.wy-odo__c'), function (c, k) {
      probe.textContent = String(drums[k].to % 10);
      c.style.width = probe.getBoundingClientRect().width / parseFloat(getComputedStyle(b).fontSize) + 'em';
    });
    b.removeChild(probe);
    return { b: b, odo: odo, li: b.closest('li'), drums: drums };
  }

  var items = nums.map(build);

  function roll(it, delay) {
    it.odo.classList.add('is-rolling');
    var last = 0;
    it.drums.forEach(function (dr) {
      if (!dr.to) { dr.el.style.transform = 'none'; return; }
      // right-hand figures land first; the whole number reads in ~1.6–2.2 s
      var dur = 1250 + (dr.fromRight * 190);
      var end = -dr.to, give = .07;
      dr.el.animate([
        { transform: 'translateY(0)' },
        { transform: 'translateY(' + (end - give) + 'em)', offset: .9, easing: 'cubic-bezier(.3,.6,.4,1)' },
        { transform: 'translateY(' + end + 'em)' }
      ], { duration: dur, delay: delay, easing: EASE, fill: 'both' });
      last = Math.max(last, dur);
    });
    setTimeout(function () {
      it.odo.classList.remove('is-rolling');
      if (it.li) it.li.classList.add('is-landed');
    }, delay + last - 120);
  }

  // all figures start at 0 (the strip's first figure) until they roll
  items.forEach(function (it) {
    it.drums.forEach(function (dr) { dr.el.style.transform = 'translateY(0)'; });
  });

  // a row that enters together rolls together, a beat apart
  var queue = [], timer = 0;
  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      io.unobserve(e.target);
      queue.push(items.filter(function (it) { return it.b === e.target; })[0]);
    });
    if (!timer) timer = setTimeout(function () {
      queue.sort(function (a, b) { return items.indexOf(a) - items.indexOf(b); });
      queue.forEach(function (it, i) { roll(it, i * 140); });
      queue = []; timer = 0;
    }, 60);
  }, { threshold: 1, rootMargin: '0px 0px -12% 0px' });
  items.forEach(function (it) { io.observe(it.b); });
})();
