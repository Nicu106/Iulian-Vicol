/* The map draws itself — footer band and /contacto.

   A [data-map-draw] box starts as a plain <img> of our SVG map (App\Support
   \StaticMap), which is what anyone without JavaScript, or with reduced motion,
   keeps: a finished map. For everyone else, when the box comes within 300px of
   the screen the same SVG is fetched as text and put inline in place of the
   image, so its lines can be animated; when a third of it is on screen it is
   drawn: the coast as one stroke, the motorways stroke by stroke from wherever
   OSM starts them, the sea filling in behind, the minor roads arriving as
   texture, and last the point. Once, never again on that page.

   Nothing is fetched until it is near, and if the fetch fails the image simply
   stays: the map can only ever be missing its animation, never itself. */
(function () {
  var boxes = document.querySelectorAll('[data-map-draw]');
  if (!boxes.length || !('IntersectionObserver' in window) || !window.fetch || !window.DOMParser) return;
  if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  function inline(box) {
    var wide = box.getAttribute('data-src-wide'), media = box.getAttribute('data-media');
    var src = wide && media && matchMedia(media).matches ? wide : box.getAttribute('data-src');
    var old = box.querySelector('picture') || box.querySelector('img');
    return fetch(src, { credentials: 'same-origin' }).then(function (r) {
      if (!r.ok) throw new Error(r.status);
      return r.text();
    }).then(function (text) {
      var svg = new DOMParser().parseFromString(text, 'image/svg+xml').documentElement;
      if (!svg || svg.nodeName.toLowerCase() !== 'svg') throw new Error('not svg');
      var img = old && (old.tagName === 'IMG' ? old : old.querySelector('img'));
      svg.setAttribute('class', 'mp-svg ' + (img ? img.className : ''));
      svg.setAttribute('aria-hidden', 'true');
      svg.setAttribute('focusable', 'false');
      // the motorways leave one after another, not all on the same frame
      Array.prototype.forEach.call(svg.querySelectorAll('.mp-r0 path, .mp-river path'), function (p, i) {
        p.style.setProperty('--i', i);
      });
      box.classList.add('is-armed');
      if (old) old.replaceWith(svg); else box.insertBefore(svg, box.firstChild);
    });
  }

  var draw = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      draw.unobserve(e.target);
      // two frames: the armed (undrawn) state must be painted before it changes
      requestAnimationFrame(function () { requestAnimationFrame(function () { e.target.classList.add('is-drawn'); }); });
    });
  }, { threshold: 0.35 });

  var near = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      near.unobserve(e.target);
      inline(e.target).then(function () { draw.observe(e.target); }, function () {});
    });
  }, { rootMargin: '300px 0px' });

  Array.prototype.forEach.call(boxes, function (b) { near.observe(b); });
})();
