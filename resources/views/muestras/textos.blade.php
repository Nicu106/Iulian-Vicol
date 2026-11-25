<!doctype html>
<html lang="ro">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Textele site-ului — IV MOTORCLASS</title>
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
{{-- /muestras/textos (SiteTextController): every text of the site beside a box
     for its new wording. Each box saves itself; what has not reached the
     server yet is kept in this browser and sent again. --}}
<style>
  :root{--navy:#0E2E57;--ink:#0F1A2B;--muted:#5B6878;--line:#DDE3EA;--soft:#F3F6FA;--ok:#1E7A46;--ok-bg:#EEF8F2;--err:#B42318;
    --ph-bg:#E8EEF7;--tag:#8A5A00;--tag-bg:#FFF3D6}
  *{box-sizing:border-box}
  html{-webkit-text-size-adjust:100%}
  body{margin:0;background:#fff;color:var(--ink);font:15px/1.5 "DM Sans",system-ui,-apple-system,"Segoe UI",sans-serif}
  .wrap{max-width:1160px;margin:0 auto;padding:24px max(16px,env(safe-area-inset-right)) 64px max(16px,env(safe-area-inset-left))}
  h1{font-size:28px;line-height:1.15;margin:0 0 8px;letter-spacing:-.01em}
  .say{margin:0 0 16px;color:var(--muted);max-width:68ch}
  .bar{position:sticky;top:0;z-index:3;display:flex;flex-wrap:wrap;gap:8px 20px;align-items:center;background:#fff;
    padding:10px 0;border-bottom:1px solid var(--line)}
  .bar b{font-variant-numeric:tabular-nums}
  .only{display:inline-flex;gap:8px;align-items:center;cursor:pointer;min-height:44px}
  .only input{width:20px;height:20px;accent-color:var(--navy)}
  .auth{display:none;margin:12px 0 0;padding:12px 14px;border-radius:8px;background:#FDECEA;color:var(--err);font-weight:600}
  .auth a{color:inherit}
  .is-auth .auth{display:block}
  table{width:100%;border-collapse:collapse;table-layout:fixed;margin-top:8px}
  col.a{width:50%} col.b{width:50%}
  thead th{text-align:left;font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);padding:10px 12px;
    background:var(--soft);border-bottom:2px solid var(--line)}
  .sec th{background:var(--navy);color:#fff;text-align:left;font-size:16px;padding:12px}
  td{vertical-align:top;padding:10px 12px;border-bottom:1px solid var(--line);overflow-wrap:anywhere}
  td.new{border-left:1px solid var(--line)}
  textarea{display:block;width:100%;min-height:44px;resize:vertical;overflow:hidden;padding:9px 10px;border:1px solid var(--line);
    border-radius:6px;background:#fff;color:var(--ink);font:inherit;line-height:1.45}
  textarea:focus{outline:2px solid var(--navy);outline-offset:1px;border-color:var(--navy)}
  .is-changed textarea{background:var(--ok-bg);border-color:#BFE3CD}
  .st{display:block;margin-top:4px;font-size:12.5px;color:var(--muted)}
  .st:empty{display:none}
  .st--ok{color:var(--ok)} .st--err{color:var(--err);font-weight:600}
  .ph{font-family:ui-monospace,"SFMono-Regular",Menlo,monospace;font-size:.86em;background:var(--ph-bg);color:var(--navy);padding:0 4px;border-radius:3px}
  .tag{display:inline-block;font-size:11.5px;font-weight:700;color:var(--tag);background:var(--tag-bg);padding:1px 6px;border-radius:3px;margin-right:4px}
  .only-changed tr.row:not(.is-changed){display:none}
  .only-changed tbody:not(:has(tr.is-changed)){display:none}
  @media (max-width:700px){
    table,thead,tbody,tr,td,th{display:block;width:100%}
    thead{display:none}
    tr.row{padding:12px 0;border-bottom:1px solid var(--line)}
    td{border:0;padding:0 2px}
    td.new{border:0;margin-top:8px}
    .sec th{margin-top:16px}
  }
</style>
</head>
<body>
<div class="wrap">
  <h1>Textele site-ului</h1>
  <p class="say">Scrie textul nou în a doua coloană. Se salvează singur pe server, în timp ce scrii.
    Lasă semnele între acolade, ca <span class="ph">{precio}</span>: acolo site-ul pune cifra sau numele.
    Unde nu schimbi nimic, lasă căsuța goală.</p>
  <div class="bar">
    <span><b id="count">{{ $changed }}</b> din {{ $total }} schimbate</span>
    <label class="only"><input type="checkbox" id="only"> Arată doar ce am schimbat</label>
  </div>
  <p class="auth" role="alert">Nu s-a salvat: sesiunea a expirat. <a href="">Intră din nou cu codul</a> — ce ai scris e păstrat în acest browser și se trimite singur după aceea.</p>

  <table>
    <colgroup><col class="a"><col class="b"></colgroup>
    <thead><tr><th scope="col">Text actual</th><th scope="col">Text nou</th></tr></thead>
    @foreach($sections as $name => $rows)
      <tbody>
        <tr class="sec"><th colspan="2" scope="colgroup">{{ $name }}</th></tr>
        @foreach($rows as $t)
          <tr class="row{{ $t->changed() ? ' is-changed' : '' }}" data-id="{{ $t->id }}">
            <td class="old" id="o{{ $t->id }}">{!! $t->originalHtml() !!}</td>
            <td class="new"><textarea rows="1" aria-labelledby="o{{ $t->id }}" spellcheck="true">{{ $t->proposed }}</textarea><span class="st" aria-live="polite"></span></td>
          </tr>
        @endforeach
      </tbody>
    @endforeach
  </table>
</div>

<script>
(function () {
  var token = document.querySelector('meta[name="csrf-token"]').content;
  var count = document.getElementById('count'), KEY = 'mc-textos-pendientes';
  var rows = {}, timers = {}, busy = {};
  document.querySelectorAll('tr.row').forEach(function (r) { rows[r.getAttribute('data-id')] = r; });

  // what was typed and has not reached the server: kept here until it has
  function load() { try { return JSON.parse(localStorage.getItem(KEY) || '{}') || {}; } catch (e) { return {}; } }
  function keep() { try { localStorage.setItem(KEY, JSON.stringify(pending)); } catch (e) {} }
  var pending = load();

  function grow(ta) { ta.style.height = 'auto'; ta.style.height = ta.scrollHeight + 2 + 'px'; }
  function say(row, text, kind) { var s = row.querySelector('.st'); s.textContent = text; s.className = 'st' + (kind ? ' st--' + kind : ''); }

  function save(id) {
    var row = rows[id], ta = row.querySelector('textarea'), value = ta.value;
    if (busy[id]) { busy[id] = 'again'; return; }
    busy[id] = 'yes';
    say(row, 'Se salvează…');
    fetch('/muestras/textos/' + id, {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ value: value })
    }).then(function (r) {
      if (r.status === 401 || r.status === 419) throw { auth: true };
      if (!r.ok) throw {};
      return r.json();
    }).then(function (j) {
      var again = busy[id] === 'again';
      delete busy[id];
      if (pending[id] === value) { delete pending[id]; keep(); }
      row.classList.toggle('is-changed', value.trim() !== '');
      say(row, 'Salvat' + (j.at ? ' la ' + j.at : ''), 'ok');
      count.textContent = j.changed;
      if (again || (id in pending)) save(id);
    }).catch(function (e) {
      delete busy[id];
      if (e && e.auth) { say(row, 'Nesalvat: intră din nou cu codul (textul e păstrat).', 'err'); document.body.classList.add('is-auth'); return; }
      say(row, 'Nu s-a salvat încă. Reîncerc…', 'err');
      setTimeout(function () { if (id in pending) save(id); }, 4000);
    });
  }

  Object.keys(rows).forEach(function (id) {
    var row = rows[id], ta = row.querySelector('textarea');
    if (ta.value) grow(ta);   // an empty box is one line already: no layout for 500 of them
    ta.addEventListener('input', function () {
      grow(ta);
      pending[id] = ta.value; keep();
      say(row, 'Nesalvat…');
      clearTimeout(timers[id]);
      timers[id] = setTimeout(function () { save(id); }, 800);
    });
    ta.addEventListener('blur', function () {
      if (id in pending) { clearTimeout(timers[id]); save(id); }
    });
  });

  // a tab closed or a connection lost before the save: put it back and send it
  Object.keys(pending).forEach(function (id) {
    var row = rows[id];
    if (!row) { delete pending[id]; return; }
    var ta = row.querySelector('textarea');
    if (ta.value === pending[id]) { delete pending[id]; return; }
    ta.value = pending[id]; grow(ta); save(id);
  });
  keep();

  var resized;
  window.addEventListener('resize', function () {
    clearTimeout(resized);
    resized = setTimeout(function () { document.querySelectorAll('textarea').forEach(function (ta) { if (ta.value) grow(ta); }); }, 250);
  });
  // a warning before leaving with something unsent; not on the way to the PIN,
  // where what was typed waits in this browser and is sent right after
  var leaving = false;
  document.querySelector('.auth a').addEventListener('click', function () { leaving = true; });
  window.addEventListener('beforeunload', function (e) { if (!leaving && Object.keys(pending).length) { e.preventDefault(); e.returnValue = ''; } });
  document.getElementById('only').addEventListener('change', function () { document.body.classList.toggle('only-changed', this.checked); });
})();
</script>
</body>
</html>
