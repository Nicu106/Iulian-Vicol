@extends('layouts.ad')

@section('title', 'Recomendaciones — IV MOTORCLASS')

@section('content')

@php
  use App\Models\ReferralReward;
  use App\Support\Referral;

  $open   = $rewards->whereIn('status', ReferralReward::OPEN)->values();
  $closed = $rewards->whereNotIn('status', ReferralReward::OPEN)->values();

  $chip = [
    'pending'  => ['wait', 'Pendiente'],
    'approved' => ['live', 'Aprobado'],
    'paid'     => ['gone', 'Pagado'],
    'rejected' => ['bad',  'Rechazado'],
  ];
  $carName = fn ($v) => $v ? trim($v->brand . ' ' . $v->model . ' ' . $v->year) : 'coche borrado';
@endphp

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Recomendaciones</h1>
    <p class="ad-head__p">Quién te trae clientes, y qué se le debe.</p>
  </div>
  <div class="ad-head__go">
    <a class="ad-btn" href="{{ route('admin.referrals.create') }}">Crear enlace</a>
    {{-- A real link, so it works everywhere; the script below opens the same
         explanation as a popup where the popover API exists. --}}
    <a class="ad-btn ad-btn--q" href="{{ route('admin.referrals.how') }}"
       data-how aria-haspopup="dialog" aria-controls="ad-how" aria-expanded="false">Cómo funciona</a>
  </div>
</div>

<div class="ad-sheet ad-sheet--how" id="ad-how" popover aria-label="Cómo funcionan las recomendaciones">
  <div class="ad-sheet__head">
    <p class="ad-sheet__t">Cómo funciona</p>
    <button class="ad-btn ad-btn--q ad-btn--s" type="button" popovertarget="ad-how" popovertargetaction="hide">Entendido</button>
  </div>
  @include('admin.referrals._how')
</div>

{{-- What is owed comes first: it is the only part of this page that asks the
     owner to do something. --}}
@if($open->isNotEmpty())
  <section class="ad-sec" style="margin-top:0">
    <h2 class="ad-h2">Premios por dar</h2>
    <ul class="ad-list">
      @foreach($open as $r)
        <li>
          <article class="ad-ref">
            <div class="ad-ref__h">
              <span class="ad-ref__who">{{ $r->referrer?->name ?? 'Enlace borrado' }}</span>
              @if($r->referrer)<span class="ad-chip ad-ref__code">{{ $r->referrer->code }}</span>@endif
              <span class="ad-chip ad-chip--{{ $chip[$r->status][0] }}">{{ $chip[$r->status][1] }}</span>
            </div>
            <p class="ad-ref__f">Trajo al comprador de <b>{{ $carName($r->vehicle) }}</b>
              @if($r->vehicle?->sold_date) · vendido el {{ $r->vehicle->sold_date->format('d/m/Y') }}@endif</p>
            <div class="ad-ref__act">
              @if($r->status === 'pending')
                <form action="{{ route('admin.referrals.reward', $r) }}" method="POST">@csrf
                  <input type="hidden" name="status" value="approved">
                  <button class="ad-btn ad-btn--s" type="submit">Aprobar</button>
                </form>
              @endif
              <form action="{{ route('admin.referrals.reward', $r) }}" method="POST">@csrf
                <input type="hidden" name="status" value="paid">
                <button class="ad-btn {{ $r->status === 'approved' ? '' : 'ad-btn--q' }} ad-btn--s" type="submit">Marcar pagado</button>
              </form>
              <form action="{{ route('admin.referrals.reward', $r) }}" method="POST"
                    onsubmit="return confirm('¿Rechazar este premio?')">@csrf
                <input type="hidden" name="status" value="rejected">
                <button class="ad-btn ad-btn--bad ad-btn--s" type="submit">Rechazar</button>
              </form>
            </div>
          </article>
        </li>
      @endforeach
    </ul>
  </section>
@endif

<section class="ad-sec" @if($open->isEmpty()) style="margin-top:0" @endif>
  <h2 class="ad-h2">Enlaces <span class="ad-seg__n">{{ $referrers->count() }}</span></h2>

  @if($referrers->isNotEmpty())
    <ul class="ad-list">
      @foreach($referrers as $p)
        <li>
          <article class="ad-ref {{ $fresh === $p->id ? 'is-fresh' : '' }}" id="enlace-{{ $p->id }}">
            <div class="ad-ref__h">
              <a class="ad-ref__who" href="{{ route('admin.referrals.show', $p) }}">{{ $p->name }}</a>
              <span class="ad-chip ad-ref__code">{{ $p->code }}</span>
              <span class="ad-chip">{{ $p->source === 'web' ? 'Desde la web' : 'Creado por ti' }}</span>
            </div>
            <p class="ad-ref__f">
              {{ $p->opens_count }} {{ $p->opens_count === 1 ? 'apertura' : 'aperturas' }}
              · {{ $p->visitors_count }} {{ $p->visitors_count === 1 ? 'persona' : 'personas' }}
              · {{ $p->pressed_count }} {{ $p->pressed_count === 1 ? 'pulsó contactar' : 'pulsaron contactar' }}
              · {{ $p->sales_count }} {{ $p->sales_count === 1 ? 'venta' : 'ventas' }}
              @if($p->vehicle) · compró el {{ $carName($p->vehicle) }}@endif
            </p>
            <div class="ad-ref__act">
              <a class="ad-btn ad-btn--q ad-btn--s" href="{{ route('admin.referrals.show', $p) }}">Ver qué pasó</a>
              <a class="ad-btn ad-btn--s" target="_blank" rel="noopener"
                 href="https://wa.me/{{ $p->phone }}?text={{ rawurlencode(Referral::messageFor($p)) }}">Enviarle su enlace</a>
              <button class="ad-btn ad-btn--q ad-btn--s" type="button" data-copy="{{ $p->link }}">Copiar enlace</button>
              <form action="{{ route('admin.referrals.destroy', $p) }}" method="POST"
                    onsubmit="return confirm('¿Borrar el enlace de {{ $p->name }}?')">@csrf @method('DELETE')
                <button class="ad-btn ad-btn--bad ad-btn--s" type="submit">Borrar</button>
              </form>
            </div>
          </article>
        </li>
      @endforeach
    </ul>
  @else
    <div class="ad-empty">
      <p class="ad-empty__t">Todavía no hay enlaces</p>
      <p class="ad-empty__p">Crea uno para alguien que te ha comprado y mándaselo por WhatsApp.
        También pueden pedirlo ellos en la web, en «Recomienda a un amigo».</p>
    </div>
  @endif
</section>

@if($closed->isNotEmpty())
  <details class="ad-more ad-sec">
    <summary>Premios cerrados ({{ $closed->count() }})</summary>
    <div class="ad-more__in">
      <ul class="ad-list">
        @foreach($closed as $r)
          <li>
            <article class="ad-ref">
              <div class="ad-ref__h">
                <span class="ad-ref__who">{{ $r->referrer?->name ?? 'Enlace borrado' }}</span>
                <span class="ad-chip ad-chip--{{ $chip[$r->status][0] }}">{{ $chip[$r->status][1] }}</span>
              </div>
              <p class="ad-ref__f">{{ $carName($r->vehicle) }}
                @if($r->paid_at) · pagado el {{ $r->paid_at->format('d/m/Y') }}@endif</p>
            </article>
          </li>
        @endforeach
      </ul>
    </div>
  </details>
@endif

@endsection

@push('js')
<script>
(function () {
  /* "Cómo funciona": a popup where the popover API exists, and it opens by
     itself the first time the page is visited, so the owner meets the rules
     before the first link. Without the API the button is a link to a page. */
  var how = document.getElementById('ad-how');
  var btn = document.querySelector('[data-how]');
  if (how && btn && typeof how.showPopover === 'function') {
    btn.addEventListener('click', function (e) { e.preventDefault(); how.showPopover(); });
    how.addEventListener('toggle', function (e) {
      btn.setAttribute('aria-expanded', e.newState === 'open' ? 'true' : 'false');
      if (e.newState === 'closed') { try { localStorage.setItem('ad-ref-how', '1'); } catch (x) {} }
    });
    var seen = false;
    try { seen = localStorage.getItem('ad-ref-how') === '1'; } catch (x) {}
    if (!seen) how.showPopover();
  }

  document.querySelectorAll('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var v = b.getAttribute('data-copy'), label = b.textContent;
      var done = function () { b.textContent = 'Copiado'; setTimeout(function () { b.textContent = label; }, 1600); };
      if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(v).then(done); }
      else { window.prompt('Copia el enlace:', v); }
    });
  });
})();
</script>
@endpush
