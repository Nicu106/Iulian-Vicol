{{-- The instalment calculator (App\Support\Finance), in the side column under
     the buttons: the client asked for the monthly payment "aici la preț", and
     on a desk everything about buying this car lives in that column.

     Six terms, the longest checked: its instalment is the lowest, the one the
     line under the price quotes. Without script it still works for the terms:
     every term's figures are in the page and car.css shows the checked one
     (:has). The deposit needs the sums, so the script shows that row.

     Every figure goes out with the TAE, the TIN, the amount, the term and the
     total (Ley 16/2011 art. 9), and the page says it is an estimate without an
     opening fee and that the lender sets the real figures.

     Needs: $car, $brand, $fin (Finance::plans). --}}
@php
  use App\Support\Finance;
  $finLast = count($fin) - 1;
  $finName = $brand.' '.$car->model.' '.$car->year;
  // the script writes the term, the deposit and the instalment in; without it
  // the term on screen may not be the one checked, so none is named
  $finAsk = 'Hola, quiero financiar el '.$finName.' ('.Finance::money((int) $car->price, false).'). '.url()->current();
@endphp
<section class="car-fin" id="financiacion" aria-labelledby="fin-h" data-stat-seen="financiacion"
         data-price="{{ (int) $car->price }}" data-tin="{{ Finance::TIN }}" data-name="{{ $finName }}">
  <h2 class="car-with__h" id="fin-h">Financiación</h2>

  <div class="car-off car-fin__box">
    <div class="car-fin__outs" aria-live="polite">
      @foreach($fin as $i => $p)
        <div class="car-fin__out" data-n="{{ $p['months'] }}">
          <p class="car-fin__q"><b data-q>{{ Finance::money($p['monthly'], false) }}</b> al mes</p>
          <p class="car-fin__ex" data-ex>{{ $p['months'] }} cuotas de {{ Finance::money($p['monthly']) }}.
            TAE {{ Finance::pct(Finance::tae()) }}, TIN {{ Finance::pct(Finance::TIN) }}.
            Importe financiado {{ Finance::money($p['amount'], false) }}; total a pagar {{ Finance::money($p['total']) }}.</p>
        </div>
      @endforeach
    </div>

    <fieldset class="car-fin__terms">
      <legend class="mc-vh">Plazo</legend>
      @foreach($fin as $i => $p)
        <label class="car-fin__t">
          <input type="radio" name="fin-n" value="{{ $p['months'] }}" data-stat="plazo" data-stat-k="{{ $p['months'] }}" @checked($i === $finLast)>
          <span>{{ $p['label'] }}</span>
        </label>
      @endforeach
    </fieldset>

    <label class="car-fin__down" hidden>
      <span class="car-fin__dl">Entrada</span>
      <span class="car-fin__dv"><input type="text" inputmode="numeric" autocomplete="off" placeholder="0" maxlength="9" id="fin-down"> €</span>
    </label>

    <p class="car-fin__note">Cálculo orientativo, sin comisión de apertura. La cuota y
      las condiciones finales las fija la entidad financiera al estudiar tu solicitud.</p>

    <a class="mc-btn mc-btn--cta car-fin__go" data-stat="financiar"
       href="https://wa.me/34614753187?text={{ urlencode($finAsk) }}">Quiero financiarlo</a>
  </div>

  {{-- His lenders, in his own words (WhatsApp, 2026-11-01): "Trabajamos con
       entidades como BBVA, Lendrock, Santander, Sabadell, Sofinco, Cofidis,
       Confía, Ibancar y Fintonic. Las opciones dependerán del perfil del
       cliente, las características del coche y los criterios de cada
       financiera. Puedes centralizar las solicitudes conmigo y te acompaño
       durante todo el proceso." In their own colours: the client wanted them to
       catch the eye (2026-11-04). --}}
  <div class="car-banks">
    <ul class="car-banks__list">
      @foreach(Finance::BANKS as $key => $b)
        <li class="car-banks__b" style="--logo:url({{ asset('img/banks/'.$key.'.svg') }});--w:{{ Finance::markWidth($b[1], $b[2] ?? 1) }}px"><span class="mc-vh">{{ $b[0] }}</span></li>
      @endforeach
    </ul>
    <p class="car-banks__say">Trabajo con entidades como estas. Las opciones dependen de
      tu perfil, del coche y de los criterios de cada financiera. Haces la solicitud
      conmigo y te acompaño en todo el proceso.</p>
  </div>
</section>

@push('js')
<script>
/* The calculator: the deposit is the one input that needs sums. The same
   French-loan formula as App\Support\Finance, to the cent; the figures for every
   term are rewritten, so switching the term afterwards needs nothing. */
(function () {
  var root = document.getElementById('financiacion');
  if (!root) return;
  var price = +root.getAttribute('data-price'), tin = +root.getAttribute('data-tin'), name = root.getAttribute('data-name');
  var i = tin / 1200, tae = (Math.pow(1 + i, 12) - 1) * 100;
  var outs = root.querySelectorAll('.car-fin__out'), terms = root.querySelectorAll('input[name="fin-n"]');
  var row = root.querySelector('.car-fin__down'), down = document.getElementById('fin-down'), go = root.querySelector('.car-fin__go');

  function group(s) { return s.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
  function money(n, cents) { var p = (cents ? n.toFixed(2) : String(Math.round(n))).split('.'); return group(p[0]) + (p[1] ? ',' + p[1] : '') + ' €'; }
  function pct(n) { return n.toFixed(2).replace('.', ',') + ' %'; }
  function monthly(a, n) { return a > 0 ? Math.round(a * i / (1 - Math.pow(1 + i, -n)) * 100) / 100 : 0; }
  function term(n) { return n % 12 ? n + ' meses' : (n === 12 ? '1 año' : n / 12 + ' años'); }
  function deposit() { return Math.min(price, parseInt(down.value.split(',')[0].replace(/\D/g, ''), 10) || 0); }
  function chosen() { var r = root.querySelector('input[name="fin-n"]:checked'); return r ? +r.value : 60; }

  function draw(sums) {
    var d = deposit(), a = price - d, n = chosen();
    outs.forEach(function (o) {
      var m = +o.getAttribute('data-n'), q = monthly(a, m);
      if (sums) {
        o.querySelector('[data-q]').textContent = money(q);
        o.querySelector('[data-ex]').textContent = a > 0
          ? m + ' cuotas de ' + money(q, true) + '. TAE ' + pct(tae) + ', TIN ' + pct(tin)
            + '. Importe financiado ' + money(a) + '; total a pagar ' + money(Math.round(q * m * 100) / 100, true) + '.'
          : 'Con esa entrada pagas el coche entero: no hay nada que financiar.';
      }
      o.classList.toggle('is-on', m === n);
    });
    terms.forEach(function (r) { r.parentNode.classList.toggle('is-on', r.checked); });
    go.href = 'https://wa.me/34614753187?text=' + encodeURIComponent('Hola, quiero financiar el ' + name + ' (' + money(price) + '): '
      + term(n) + ', ' + (d ? 'con ' + money(d) + ' de entrada' : 'sin entrada') + ', unos ' + money(monthly(a, n)) + ' al mes. '
      + location.origin + location.pathname);
  }

  row.hidden = false;
  root.querySelector('.car-fin__outs').classList.add('is-js');
  terms.forEach(function (r) { r.addEventListener('change', function () { draw(false); }); });
  down.addEventListener('input', function () { draw(true); });
  // 5000 → "5.000" once he leaves the field; never while typing, or the caret jumps
  down.addEventListener('blur', function () { var d = deposit(); down.value = d ? group(String(d)) : ''; });
  draw(false);
})();
</script>
@endpush
