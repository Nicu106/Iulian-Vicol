{{-- The coches.net sheet into the form (markup: _sheet.blade.php). The PDF goes to
     POST /admin/fichas/leer the moment it is chosen; what comes back fills every
     EMPTY field (marked "De la ficha") and, where he had already written something
     else, waits under the field as "En la ficha: … Usar". Then a summary: what was
     filled, what differs, the warnings the reader raised, and a car already in the
     list that looks like the same one. The sheet's photograph comes in as a staged
     photo only when the car has none. --}}
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
  function show(name, v) {
    var n = function (x) { return Number(x).toLocaleString('es-ES'); };
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
      apply(res.j);
    }, function () {
      box.classList.remove('is-busy');
      input.value = '';
      say('bad', [el('p', 'ad-cnet__t', 'Sin conexión.'), el('p', '', 'Vuelve a subir la ficha cuando tengas cobertura.')]);
    });
  });

  function apply(j) {
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
    if (s.new_total) keep.push('el precio de nuevo (' + Number(s.new_total).toLocaleString('es-ES', { maximumFractionDigits: 0 }) + ' €' + (s.new_year ? ' en ' + s.new_year : '') + ')');
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
