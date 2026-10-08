/* /por-que-nosotros — Iulian's text comes in as it is reached: the heading,
   then each paragraph a moment after the one before it, once. Reduced motion
   or no IntersectionObserver: the text is simply there. */
(function () {
  'use strict';
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce || !('IntersectionObserver' in window)) return;
  var els = Array.prototype.slice.call(document.querySelectorAll('.wy-txt__h, .wy-txt__b > p'));
  if (!els.length) return;
  els.forEach(function (el) { el.setAttribute('data-r', ''); });
  document.documentElement.classList.add('wy-txt-on');
  var io = new IntersectionObserver(function (es) {
    var k = 0;
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      io.unobserve(e.target);
      e.target.style.setProperty('--d', (k++ * 90) + 'ms');
      e.target.classList.add('is-in');
    });
  }, { rootMargin: '0px 0px -8% 0px', threshold: .15 });
  els.forEach(function (el) { io.observe(el); });
})();
