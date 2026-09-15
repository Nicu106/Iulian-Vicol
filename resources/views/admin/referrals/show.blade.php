@extends('layouts.ad')

@section('title', $referrer->name . ' — Recomendaciones')

@section('content')

@php
  use App\Support\Referral;

  $carName = fn ($v) => $v ? trim($v->brand . ' ' . $v->model . ' ' . $v->year) : 'un coche borrado';

  $place = function (?string $path) {
      return match (true) {
          $path === null, $path === '/', $path === '/inicio' => 'la portada',
          $path === '/catalogo'                              => 'el catálogo',
          $path === '/contacto'                              => 'Contacto',
          $path === '/vende'                                 => 'Vende tu coche',
          $path === '/recomienda'                            => 'Recomienda a un amigo',
          str_starts_with($path, '/coche/')                  => 'la ficha de un coche',
          default                                            => $path,
      };
  };

  $device = ['mobile' => 'Móvil', 'tablet' => 'Tableta', 'desktop' => 'Ordenador'];
  $via = [
      'whatsapp'  => 'desde WhatsApp',
      'instagram' => 'desde Instagram',
      'facebook'  => 'desde Facebook',
      'telegram'  => 'desde Telegram',
      'directo'   => 'sin origen conocido',
  ];

  /* One line per thing done. Consecutive views of the same page collapse into
     one line with a count, so ten reloads of a car read as "vio 10 veces". */
  $steps = function ($events) use ($referrer, $carName, $place) {
      $out = [];
      foreach ($events as $e) {
          $key = $e->type . '|' . ($e->vehicle_id ?? $e->path) . '|' . ($e->referrer_id ?? '');
          $last = array_key_last($out);
          if ($last !== null && $out[$last]['key'] === $key && in_array($e->type, ['page', 'car'], true)) {
              $out[$last]['n']++;
              $out[$last]['at'] = $e->created_at;
              continue;
          }
          $text = match ($e->type) {
              'open'       => $e->referrer_id && $e->referrer_id !== $referrer->id
                                ? 'Abrió el enlace de ' . ($e->referrer?->name ?? 'otra persona') . ' (sigue contando para ' . $referrer->name . ')'
                                : 'Abrió el enlace',
              'car'        => 'Vio el ' . $carName($e->vehicle),
              'page'       => 'Vio ' . $place($e->path),
              'whatsapp'   => 'Pulsó WhatsApp en ' . $place($e->path),
              'email'      => 'Pulsó el correo en ' . $place($e->path),
              'form_sell'  => 'Envió el formulario «Vende tu coche»',
              'form_refer' => 'Pidió su propio enlace para recomendar',
              default      => $e->type,
          };
          $out[] = ['key' => $key, 'type' => $e->type, 'text' => $text, 'n' => 1, 'at' => $e->created_at];
      }
      return $out;
  };
@endphp

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">{{ $referrer->name }}</h1>
    <p class="ad-head__p">Código <b>{{ $referrer->code }}</b> · enlace creado el {{ $referrer->created_at?->format('d/m/Y') }}
      @if($referrer->vehicle) · compró el {{ $carName($referrer->vehicle) }}@endif</p>
  </div>
  <div class="ad-head__go">
    <a class="ad-btn" target="_blank" rel="noopener"
       href="https://wa.me/{{ $referrer->phone }}?text={{ rawurlencode(Referral::messageFor($referrer)) }}">Enviarle su enlace</a>
    <a class="ad-btn ad-btn--q" href="{{ route('admin.referrals.index') }}">Volver</a>
  </div>
</div>

{{-- From opening the link to buying a car. Each number is people, except the
     first, which is openings: one person can open a link several times. --}}
<ul class="ad-facts ad-facts--five">
  <li class="ad-fact"><span class="ad-fact__k">Aperturas</span><span class="ad-fact__v">{{ $referrer->opens_count }}</span><span class="ad-fact__n">veces que se abrió</span></li>
  <li class="ad-fact"><span class="ad-fact__k">Personas</span><span class="ad-fact__v">{{ $referrer->visitors_count }}</span><span class="ad-fact__n">distintas</span></li>
  <li class="ad-fact"><span class="ad-fact__k">Vieron coches</span><span class="ad-fact__v">{{ $sawCars }}</span><span class="ad-fact__n">entraron en alguna ficha</span></li>
  <li class="ad-fact"><span class="ad-fact__k">Pulsaron contactar</span><span class="ad-fact__v">{{ $pressed }}</span><span class="ad-fact__n">WhatsApp o correo</span></li>
  <li class="ad-fact"><span class="ad-fact__k">Compraron</span><span class="ad-fact__v">{{ $referrer->sales_count }}</span><span class="ad-fact__n">coches vendidos</span></li>
</ul>

@if($cars->isNotEmpty())
  <section class="ad-sec">
    <h2 class="ad-h2">Los coches que miraron</h2>
    <ul class="ad-list">
      @foreach($cars as $c)
        <li>
          <a class="ad-todo" href="{{ route('admin.vehicles.edit', $c['vehicle']->slug) }}">
            <span class="ad-todo__n">{{ $c['views'] }}</span>
            <span class="ad-todo__t">
              <b>{{ $carName($c['vehicle']) }}</b>
              <em>{{ $c['views'] === 1 ? 'una vez' : $c['views'] . ' veces' }} ·
                {{ $c['people'] === 1 ? '1 persona' : $c['people'] . ' personas' }}</em>
            </span>
            <span class="ad-todo__go" aria-hidden="true">Abrir</span>
          </a>
        </li>
      @endforeach
    </ul>
  </section>
@endif

<section class="ad-sec">
  <h2 class="ad-h2">Qué hizo cada persona</h2>

  @if($visitors->isNotEmpty())
    <ul class="ad-list">
      @foreach($visitors as $i => $v)
        @php $trail = $steps($v->events); @endphp
        <li>
          <article class="ad-person">
            <div class="ad-ref__h">
              <span class="ad-ref__who">Persona {{ $visitors->count() - $i }}</span>
              <span class="ad-chip">{{ $device[$v->device] ?? $v->device }}</span>
              <span class="ad-chip">{{ $v->os }} · {{ $v->browser }}</span>
              @if(collect($trail)->contains(fn ($s) => in_array($s['type'], ['whatsapp', 'email'], true)))
                <span class="ad-chip ad-chip--live">Pulsó contactar</span>
              @endif
            </div>
            <p class="ad-ref__f">Llegó {{ $via[$v->via] ?? '' }} el {{ $v->first_seen_at?->format('d/m/Y \a \l\a\s H:i') }}
              · {{ $v->visits === 1 ? '1 visita' : $v->visits . ' visitas' }}
              · última vez {{ $v->last_seen_at?->locale('es')->diffForHumans() }}</p>

            <ol class="ad-trail">
              @foreach(array_slice($trail, -12) as $s)
                <li class="ad-trail__i ad-trail__i--{{ $s['type'] }}">
                  <time datetime="{{ $s['at']?->toIso8601String() }}">{{ $s['at']?->format('d/m H:i') }}</time>
                  <span>{{ $s['text'] }}@if($s['n'] > 1) <em>· {{ $s['n'] }} veces</em>@endif</span>
                </li>
              @endforeach
            </ol>
            @if(count($trail) > 12)
              <p class="ad-note" style="margin:0">Y {{ count($trail) - 12 }} pasos antes.</p>
            @endif
          </article>
        </li>
      @endforeach
    </ul>
  @else
    <div class="ad-empty">
      <p class="ad-empty__t">Nadie ha abierto este enlace todavía</p>
      <p class="ad-empty__p">Cuando alguien lo abra, aquí verás qué coches mira y si pulsa WhatsApp.</p>
    </div>
  @endif

  <p class="ad-note">Son personas anónimas: no se guarda su nombre ni su IP, sólo el tipo de
    dispositivo y lo que vieron en esta web. Se borran {{ Referral::days() }} días después de su
    última visita. Las vistas previas que genera WhatsApp al enviar el enlace no cuentan como
    aperturas.</p>
</section>

@endsection
