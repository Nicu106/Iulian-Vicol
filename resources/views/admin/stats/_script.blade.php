{{-- What the statistics need from JavaScript, for the page and the popup alike:
     the cars table sorts (header or the phone's select) and folds after ten, and
     a bar tells its day on hover or tap. Without it the table is complete and in
     order of views, and the numbers are in the hidden table under each chart. --}}
<script>
window.mcStats = window.mcStats || {
  wire: function (root) {
    root.querySelectorAll('[data-st-cars]').forEach(function (table) {
      if (table.dataset.wired) return;
      table.dataset.wired = '1';
      table.classList.add('is-js');
      var body = table.tBodies[0], more = table.parentNode.parentNode.querySelector('[data-st-more]');
      var sel = table.parentNode.parentNode.querySelector('[data-st-sort]');
      var key = 'views', dir = -1;
      function fold() {
        Array.prototype.forEach.call(body.rows, function (r, i) { r.classList.toggle('is-more', i >= 10); });
      }
      function sort(k, d) {
        key = k; dir = d;
        var rows = Array.prototype.slice.call(body.rows);
        rows.sort(function (a, b) {
          return dir * ((+a.dataset[k]) - (+b.dataset[k])) || ((+b.dataset.views) - (+a.dataset.views));
        });
        rows.forEach(function (r) { body.appendChild(r); });
        table.querySelectorAll('th[aria-sort]').forEach(function (th) { th.removeAttribute('aria-sort'); });
        var b = table.querySelector('[data-st-key="' + k + '"]');
        if (b) b.parentNode.setAttribute('aria-sort', dir < 0 ? 'descending' : 'ascending');
        if (sel && sel.querySelector('option[value="' + k + '"]')) sel.value = k;
        fold();
      }
      table.querySelectorAll('[data-st-key]').forEach(function (b) {
        b.addEventListener('click', function () { var k = b.dataset.stKey; sort(k, k === key ? -dir : -1); });
      });
      if (sel) sel.addEventListener('change', function () { sort(sel.value, -1); });
      if (more) {
        more.hidden = false;
        more.addEventListener('click', function () { table.classList.add('is-all'); more.remove(); });
      }
    });

    root.querySelectorAll('.ad-st-bars__plot').forEach(function (plot) {
      if (plot.dataset.wired) return;
      plot.dataset.wired = '1';
      var tip = document.createElement('span');
      tip.className = 'ad-st-bars__tip';
      tip.hidden = true;
      plot.appendChild(tip);
      function show(e) {
        var bar = e.target.closest && e.target.closest('.ad-st-bar');
        if (!bar || !plot.contains(bar)) return;
        plot.querySelectorAll('.is-hot').forEach(function (b) { b.classList.remove('is-hot'); });
        bar.classList.add('is-hot');
        tip.textContent = bar.dataset.tip;
        tip.hidden = false;
        var p = plot.getBoundingClientRect(), r = bar.getBoundingClientRect();
        var x = r.left - p.left + r.width / 2, w = tip.offsetWidth;
        tip.style.left = Math.max(0, Math.min(p.width - w, x - w / 2)) + 'px';
      }
      function hide() { tip.hidden = true; plot.querySelectorAll('.is-hot').forEach(function (b) { b.classList.remove('is-hot'); }); }
      plot.addEventListener('pointermove', show);
      plot.addEventListener('pointerdown', show);
      plot.addEventListener('pointerleave', function (e) { if (e.pointerType === 'mouse') hide(); });
    });

    // the "no contar" link: one tap selects all of it, ready to copy
    root.querySelectorAll('[data-st-copy]').forEach(function (i) {
      i.addEventListener('focus', function () { i.select(); });
    });
  }
};
</script>
