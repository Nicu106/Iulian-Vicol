{{-- The coches.net sheet into the form (markup: _sheet.blade.php). The PDF goes to
     POST /admin/fichas/leer the moment it is chosen; what was read opens in a
     window FIRST (2026-10-24, the client: "first a popup where I see the data, then
     I confirm"): the warnings, a car already in the list, every value it would put
     in the form (ticked where the field is empty; where he already wrote something
     else, "Ahora: …" and unticked, his to choose), the photograph, and what is kept
     with the car (extras, price new, its past). "Rellenar el formulario" puts the
     ticked values in, marked "De la ficha"; "Cancelar" changes nothing. Nothing is
     saved until Guardar. Without <dialog>, the old way: empty fields filled, the
     rest offered under each field with "Usar". --}}
@push('js')
<script>
(function () {
  var box = document.getElementById('ad-cnet'), form = document.getElementById('ad-car');
  if (!box || !form || !window.fetch || !window.FormData) return;
  var input = document.getElementById('ad-cnet-file'), out = document.getElementById('ad-cnet-out'),
      keyIn = document.getElementById('ad-cnet-key'), F = window.mcCarForm || {};

  var NAMES = { brand: 'Marca', model: 'Modelo', year: 'Año', mileage: 'Kilómetros', price: 'Precio',
    fuel: 'Combustible', transmission: 'Cambio', engine: 'Motor', power: 'Potencia', color: 'Color',
    body_type: 'Carrocería', description: 'Descripción', vin: 'Etiqueta', sheet_doors: 'Puertas',
    sheet_seats: 'Plazas', sheet_trunk: 'Maletero', sheet_co2: 'Emisiones', sheet_warranty: 'Garantía' };

  function els(name) { return [].slice.call(form.elements).filter(function (e) { return e.name === name; }); }
  function current(name) {
    var e = els(name);
    if (!e.length) return null;
    if (e[0].type === 'radio') { var c = e.filter(function (r) { return r.checked; })[0]; return c ? c.value : ''; }
    return e[0].value;
  }
  // "19.900" and "19900", "Gris plata" and "gris  plata" are the same thing
  function same(a, b) {
    var n = function (v) { return String(v == null ? '' : v).toLowerCase().replace(/[\s.]/g, '').replace(/cv$/, ''); };
    return n(a) === n(b);
  }
  function setVal(name, v) {
    var e = els(name);
    if (!e.length) return false;
    if (e[0].type === 'radio') {
      var r = e.filter(function (x) { return x.value === String(v); })[0];
      if (!r) return false;
      r.checked = true; r.dispatchEvent(new Event('change', { bubbles: true }));
      return true;
    }
    if (e[0].tagName === 'SELECT' && ![].some.call(e[0].options, function (o) { return o.value === String(v); })) return false;
    e[0].value = v;
    e[0].dispatchEvent(new Event(e[0].tagName === 'SELECT' ? 'change' : 'input', { bubbles: true }));
    return true;
  }
  // 1.657 €, not 1657 € (es-ES leaves four-digit numbers ungrouped; the site does not)
  function eu(x) { return String(Math.round(Number(x))).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
  function show(name, v) {
    var n = eu;
    if (name === 'price') return n(v) + ' €';
    if (name === 'mileage') return n(v) + ' km';
    if (name === 'sheet_trunk') return v + ' l';
    if (name === 'sheet_co2') return v + ' g/km';
    if (name === 'sheet_warranty') return (v / 12) + (v / 12 === 1 ? ' año' : ' años');
    if (name === 'description') { var t = String(v); return '«' + t.slice(0, 110) + (t.length > 110 ? '…' : '') + '»'; }
    return String(v);
  }
  function holder(name) {
    var e = els(name)[0];
    return e ? (e.closest('.ad-field') || e.parentNode) : null;
  }
  function tag(f, text, cls) {
    var s = document.createElement('span');
    s.className = 'ad-sug ' + (cls || '');
    s.textContent = text;
    f.appendChild(s);
    return s;
  }
  function clearMarks() {
    [].forEach.call(form.querySelectorAll('.ad-sug'), function (s) { s.parentNode.removeChild(s); });
    [].forEach.call(form.querySelectorAll('.is-filled'), function (f) { f.classList.remove('is-filled'); });
  }
  function filled(name) {
    var f = holder(name);
    if (!f) return;
    f.classList.add('is-filled');
    tag(f, 'De la ficha', 'ad-sug--ok');
  }
  function offer(name, v) {
    var f = holder(name);
    if (!f) return;
    var s = tag(f, '', 'ad-sug--use');
    var t = document.createElement('span');
    t.textContent = 'En la ficha: ' + show(name, v);
    var b = document.createElement('button');
    b.type = 'button'; b.className = 'ad-sug__b'; b.textContent = 'Usar';
    b.addEventListener('click', function () {
      if (setVal(name, v)) { s.parentNode.removeChild(s); filled(name); }
    });
    s.appendChild(t); s.appendChild(b);
  }

  function say(kind, nodes) {
    out.hidden = false;
    out.className = 'ad-cnet__out is-' + kind;
    out.textContent = '';
    nodes.forEach(function (n) { out.appendChild(typeof n === 'string' ? document.createTextNode(n) : n); });
  }
  function el(t, cls, text) { var e = document.createElement(t); if (cls) e.className = cls; if (text != null) e.textContent = text; return e; }

  input.addEventListener('change', function () {
    var file = input.files && input.files[0];
    if (!file) return;
    if (!/\.pdf$/i.test(file.name || '') && file.type !== 'application/pdf') {
      input.value = '';
      return say('bad', [el('p', 'ad-cnet__t', 'Esto no es un PDF.'), el('p', '', 'Sube la ficha que descargas en coches.net con «Imprimir vehículo» → «Guardar como PDF».')]);
    }
    say('busy', [el('p', 'ad-cnet__t', 'Leyendo la ficha…')]);
    box.classList.add('is-busy');
    var fd = new FormData();
    fd.append('pdf', file);
    fd.append('token', F.token ? F.token() : '');
    fd.append('slug', box.getAttribute('data-slug') || '');
    fd.append('photos', F.photos ? F.photos() : 1);
    fetch(box.getAttribute('data-read'), {
      method: 'POST', body: fd, credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': F.csrf ? F.csrf() : '' }
    }).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (j) { return { code: r.status, j: j }; });
    }).then(function (res) {
      box.classList.remove('is-busy');
      input.value = '';
      if (res.code === 401 || res.code === 419) return say('bad', [el('p', 'ad-cnet__t', 'La sesión ha caducado.'), el('p', '', 'Entra otra vez (en otra pestaña) y vuelve a subir la ficha. Lo que has escrito sigue aquí.')]);
      if (res.code === 422) {
        var m = res.j.message || '';
        if (res.j.errors) { var k = Object.keys(res.j.errors)[0]; if (k) m = res.j.errors[k][0]; }
        return say('bad', [el('p', 'ad-cnet__t', 'No he podido usar este PDF.'), el('p', '', m || 'Vuelve a intentarlo.')]);
      }
      if (res.code !== 200 || !res.j.key) return say('bad', [el('p', 'ad-cnet__t', 'No he podido leer la ficha.'), el('p', '', 'Algo ha fallado en el servidor (' + res.code + '). Vuelve a intentarlo; si sigue, rellena el coche a mano.')]);
      review(res.j);
    }, function () {
      box.classList.remove('is-busy');
      input.value = '';
      say('bad', [el('p', 'ad-cnet__t', 'Sin conexión.'), el('p', '', 'Vuelve a subir la ficha cuando tengas cobertura.')]);
    });
  });

  /* ---- the review window ------------------------------------------------- */
  var ORDER = ['brand', 'model', 'year', 'price', 'mileage', 'fuel', 'transmission', 'engine', 'power', 'body_type',
               'color', 'vin', 'sheet_doors', 'sheet_seats', 'sheet_trunk', 'sheet_co2', 'sheet_warranty', 'description'];
  var dlg = document.getElementById('ad-cnet-dlg');
  var photosUrl = form.getAttribute('data-photos');

  function canSet(name, v) {
    var e = els(name);
    if (!e.length) return false;
    if (e[0].type === 'radio') return e.some(function (x) { return x.value === String(v); });
    if (e[0].tagName === 'SELECT') return [].some.call(e[0].options, function (o) { return o.value === String(v); });
    return true;
  }
  function rowsOf(j) {
    var s = j.sheet || {}, f = {};
    Object.keys(j.fields || {}).forEach(function (k) { f[k] = j.fields[k]; });
    if (s.doors) f.sheet_doors = s.doors;
    if (s.seats) f.sheet_seats = s.seats;
    if (s.trunk) f.sheet_trunk = s.trunk;
    if (s.co2) f.sheet_co2 = s.co2;
    if (s.warranty) f.sheet_warranty = String(s.warranty);
    var fresh = !box.classList.contains('has-sheet'), rows = [];
    ORDER.forEach(function (name) {
      var v = f[name], cur = current(name);
      if (v == null || v === '' || cur === null) return;
      var empty = cur === '' || (name === 'sheet_warranty' && fresh && cur === '12');
      rows.push({ name: name, v: v, cur: cur, kind: same(cur, v) ? 'same' : (empty ? 'new' : 'diff'), ok: canSet(name, v) });
    });
    return rows;
  }
  function unstage(path) {
    if (!path || !photosUrl) return;
    fetch(photosUrl, { method: 'DELETE', credentials: 'same-origin', keepalive: true,
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': F.csrf ? F.csrf() : '' },
      body: JSON.stringify({ token: F.token ? F.token() : '', path: path }) }).catch(function () {});
  }
  function row(r) {
    var l = el('label', 'ad-cnet-row is-' + r.kind + (r.ok ? '' : ' is-off'));
    var c = el('input'); c.type = 'checkbox'; c.checked = r.ok && r.kind === 'new'; c.disabled = !r.ok;
    c.setAttribute('data-name', r.name);
    var t = el('span', 'ad-cnet-row__t');
    t.appendChild(el('span', 'ad-cnet-row__k', NAMES[r.name]));
    t.appendChild(el('span', 'ad-cnet-row__v', show(r.name, r.v)));
    if (r.kind === 'diff') t.appendChild(el('span', 'ad-cnet-row__was', 'Ahora: ' + (r.name === 'description' ? show(r.name, r.cur) : show(r.name, r.cur))));
    if (!r.ok) t.appendChild(el('span', 'ad-cnet-row__was', 'No está en las opciones del formulario: ponlo a mano.'));
    l.appendChild(c); l.appendChild(t);
    return l;
  }
  function review(j) {
    if (!dlg || typeof dlg.showModal !== 'function') return applyInline(j);
    var s = j.sheet || {}, rows = rowsOf(j);
    var news = rows.filter(function (r) { return r.kind === 'new'; }),
        diffs = rows.filter(function (r) { return r.kind === 'diff'; }),
        sames = rows.filter(function (r) { return r.kind === 'same'; });
    var photo = j.photo && j.photo.path;
    dlg.textContent = '';

    var head = el('header', 'ad-cnet-dlg__head');
    head.appendChild(el('h2', 'ad-cnet-dlg__h', 'Datos de la ficha'));
    head.lastChild.id = 'ad-cnet-dlg-h';
    head.lastChild.tabIndex = -1;
    head.appendChild(el('p', 'ad-cnet-dlg__p', s.title || ''));
    dlg.appendChild(head);

    var body = el('div', 'ad-cnet-dlg__body');
    var warn = j.warnings || [], matches = j.matches || [];
    if (warn.length || matches.length) {
      var w = el('div', 'ad-cnet-dlg__warn');
      w.appendChild(el('p', 'ad-cnet-dlg__wh', warn.length + matches.length === 1 ? 'Revisa esto antes de confirmar:' : 'Revisa estas ' + (warn.length + matches.length) + ' cosas antes de confirmar:'));
      var ul = el('ul');
      warn.forEach(function (x) { ul.appendChild(el('li', '', x)); });
      matches.forEach(function (m) {
        var li = el('li', '', (m.status === 'sold' ? 'Parece el mismo coche que vendiste: ' : 'Parece que ya tienes este coche: ') + m.name + '. ');
        var a = el('a', '', 'Abrirlo'); a.href = m.url; a.target = '_blank'; a.rel = 'noopener';
        li.appendChild(a); ul.appendChild(li);
      });
      w.appendChild(ul);
      body.appendChild(w);
    }
    if (photo) {
      var ph = el('label', 'ad-cnet-dlg__photo');
      var pc = el('input'); pc.type = 'checkbox'; pc.checked = true; pc.id = 'ad-cnet-photo';
      var im = el('img'); im.src = photo; im.alt = '';
      var pt = el('span', '', 'Poner esta foto como primera foto');
      ph.appendChild(pc); ph.appendChild(im); ph.appendChild(pt);
      body.appendChild(ph);
    }
    if (news.length) {
      body.appendChild(el('h3', 'ad-cnet-dlg__g', diffs.length || sames.length ? 'Campos vacíos que rellena' : 'Lo que rellena'));
      news.forEach(function (r) { body.appendChild(row(r)); });
    }
    if (diffs.length) {
      var gh = el('div', 'ad-cnet-dlg__gh');
      gh.appendChild(el('h3', 'ad-cnet-dlg__g', diffs.length === 1 ? 'Distinto de lo que tienes' : 'Distintos de lo que tienes'));
      var all = el('button', 'ad-cnet-dlg__all', 'Usar los de la ficha'); all.type = 'button';
      gh.appendChild(all);
      body.appendChild(gh);
      body.appendChild(el('p', 'ad-cnet-dlg__note', 'Marca los que quieras cambiar; los que dejes sin marcar se quedan como están.'));
      var diffRows = diffs.map(function (r) { var x = row(r); body.appendChild(x); return x; });
      all.addEventListener('click', function () {
        var boxes = diffRows.map(function (x) { return x.querySelector('input'); }).filter(function (c) { return !c.disabled; });
        var on = boxes.some(function (c) { return !c.checked; });
        boxes.forEach(function (c) { c.checked = on; });
        all.textContent = on ? 'Dejar los míos' : 'Usar los de la ficha';
      });
    }
    if (sames.length) {
      body.appendChild(el('p', 'ad-cnet-dlg__same', 'Ya coinciden: ' + sames.map(function (r) { return NAMES[r.name].toLowerCase(); }).join(', ') + '.'));
    }

    // what goes with the car on save, whatever is ticked above
    var keep = el('section', 'ad-cnet-dlg__keep');
    keep.appendChild(el('h3', 'ad-cnet-dlg__g', 'Se guarda con el coche'));
    if (s.new_total) keep.appendChild(el('p', 'ad-cnet-dlg__big', 'Precio de nuevo' + (s.new_year ? ' en ' + s.new_year : '') + ': ' + eu(s.new_total) + ' €'));
    if ((s.lines || []).length) keep.appendChild(el('p', '', s.lines.join(' · ')));
    if ((s.extras_top || []).length) {
      var ex = el('ul', 'ad-cnet-dlg__ex');
      s.extras_top.forEach(function (x) {
        var li = el('li'); li.appendChild(el('span', '', x.name)); li.appendChild(el('span', '', eu(x.price) + ' €')); ex.appendChild(li);
      });
      keep.appendChild(ex);
      if (s.extras > s.extras_top.length) keep.appendChild(el('p', 'ad-cnet-dlg__note', 'y ' + (s.extras - s.extras_top.length) + ' extras más.'));
    }
    if (keep.childNodes.length > 1) body.appendChild(keep);
    dlg.appendChild(body);

    var foot = el('footer', 'ad-cnet-dlg__foot');
    var no = el('button', 'ad-btn ad-btn--q', 'Cancelar'); no.type = 'button';
    var yes = el('button', 'ad-btn', 'Rellenar el formulario'); yes.type = 'button';
    foot.appendChild(no); foot.appendChild(yes);
    dlg.appendChild(foot);

    var done = false;
    function finish(ok) {
      if (done) return; done = true;
      var usePhoto = photo && ok && document.getElementById('ad-cnet-photo') && document.getElementById('ad-cnet-photo').checked;
      if (photo && !usePhoto) unstage(photo);
      if (dlg.open) dlg.close();
      if (!ok) return say('none', [el('p', '', 'No se ha cambiado nada. Puedes volver a subir la ficha cuando quieras.')]);
      clearMarks();
      var put = 0;
      [].forEach.call(body.querySelectorAll('.ad-cnet-row input:checked'), function (c) {
        var name = c.getAttribute('data-name'), r = rows.filter(function (x) { return x.name === name; })[0];
        if (r && setVal(name, r.v)) { filled(name); put++; }
      });
      keyIn.value = j.key;
      if (usePhoto && F.addPhotos) F.addPhotos([{ path: photo, tag: 'exterior' }]);
      var kept = diffs.length - [].filter.call(body.querySelectorAll('.ad-cnet-row.is-diff input'), function (c) { return c.checked; }).length;
      var msg = [el('p', 'ad-cnet__t', 'Ficha aplicada: ' + (s.title || 'el coche'))];
      msg.push(el('p', '', (put ? put + (put === 1 ? ' dato puesto en el formulario' : ' datos puestos en el formulario') + ' (marcados «De la ficha»)' : 'Ningún dato cambiado en el formulario') +
        (kept > 0 ? '; ' + kept + (kept === 1 ? ' se queda' : ' se quedan') + ' como lo tenías' : '') + '.'));
      if (s.extras || s.new_total) msg.push(el('p', '', 'Los extras, el precio de nuevo y su historia se guardan con el coche.'));
      msg.push(el('p', 'ad-cnet__n', 'Nada se guarda hasta que pulses «Guardar».'));
      say('ok', msg);
      var first = form.querySelector('.is-filled');
      if (first) first.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }
    no.addEventListener('click', function () { finish(false); });
    yes.addEventListener('click', function () { finish(true); });
    dlg.addEventListener('cancel', function () { finish(false); }, { once: true });   // Escape, the back gesture
    out.hidden = true;
    dlg.showModal();
    body.scrollTop = 0;
    // focus on the title, not on a button: nothing is pressed by a stray tap or Enter
    head.firstChild.focus({ preventScroll: true });
  }

  /* ---- without <dialog>: fill the empty fields, offer the rest under each --- */
  function applyInline(j) {
    clearMarks();
    keyIn.value = j.key;
    var s = j.sheet || {}, fields = {};
    Object.keys(j.fields || {}).forEach(function (k) { fields[k] = j.fields[k]; });
    if (s.doors) fields.sheet_doors = s.doors;
    if (s.seats) fields.sheet_seats = s.seats;
    if (s.trunk) fields.sheet_trunk = s.trunk;
    if (s.co2) fields.sheet_co2 = s.co2;
    if (s.warranty) fields.sheet_warranty = String(s.warranty);

    var done = [], differ = [], cannot = [];
    var fresh = !box.classList.contains('has-sheet');
    Object.keys(fields).forEach(function (name) {
      var v = fields[name];
      if (v == null || v === '') return;
      var cur = current(name);
      if (cur === null || same(cur, v)) return;
      // the warranty select always has a value: on a car without a sheet, one year is "not set"
      var empty = cur === '' || (name === 'sheet_warranty' && fresh && cur === '12');
      if (empty) {
        if (setVal(name, v)) { filled(name); done.push(name); } else cannot.push(NAMES[name] + ': ' + show(name, v));
      } else {
        offer(name, v); differ.push(name);
      }
    });
    var photo = j.photo && j.photo.path;
    if (photo && F.addPhotos) F.addPhotos([{ path: photo, tag: 'exterior' }]);

    var nodes = [el('p', 'ad-cnet__t', 'Ficha leída: ' + (s.title || 'el coche'))];
    var line = [];
    if (done.length) line.push(done.length + (done.length === 1 ? ' dato rellenado' : ' datos rellenados'));
    if (differ.length) line.push(differ.length + (differ.length === 1 ? ' es distinto' : ' son distintos') + ' de lo que tenías: abajo, «En la ficha… Usar»');
    if (!done.length && !differ.length) line.push('Todo coincide con lo que ya tenías');
    nodes.push(el('p', '', line.join('. ') + '.'));
    var keep = [];
    if (s.extras) keep.push(s.extras + ' extras de fábrica con su precio');
    if (s.new_total) keep.push('el precio de nuevo (' + eu(s.new_total) + ' €' + (s.new_year ? ' en ' + s.new_year : '') + ')');
    if (s.facts && Object.keys(s.facts).length) keep.push('su historia (matrícula, ITV, llaves…)');
    if (keep.length) nodes.push(el('p', '', 'Al guardar se quedan con el coche ' + keep.join(', ') + '.'));
    if (photo) nodes.push(el('p', '', 'He puesto la foto de la ficha como primera foto. Cuando subas las tuyas, quítala si quieres.'));
    if (cannot.length) nodes.push(el('p', '', 'No tengo dónde ponerlo, revísalo a mano: ' + cannot.join(' · ') + '.'));

    var warn = (j.warnings || []).slice();
    if (warn.length) {
      var ul = el('ul', 'ad-cnet__w');
      warn.forEach(function (w) { ul.appendChild(el('li', '', w)); });
      nodes.push(el('p', 'ad-cnet__wh', warn.length === 1 ? 'Una cosa que revisar:' : warn.length + ' cosas que revisar:'));
      nodes.push(ul);
    }
    (j.matches || []).forEach(function (m) {
      var p = el('p', 'ad-cnet__m');
      p.appendChild(document.createTextNode((m.status === 'sold' ? 'Parece el mismo coche que vendiste: ' : 'Parece que ya tienes este coche: ') + m.name + '. '));
      var a = el('a', '', 'Abrirlo');
      a.href = m.url; a.target = '_blank'; a.rel = 'noopener';
      p.appendChild(a);
      nodes.push(p);
    });
    nodes.push(el('p', 'ad-cnet__n', 'Nada se guarda hasta que pulses «Guardar».'));
    say(warn.length || (j.matches || []).length ? 'warn' : 'ok', nodes);
    var first = form.querySelector('.ad-sug--use');
    if (!first) out.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  }
})();
</script>
@endpush
