@extends('layouts.ad')

@section('title', 'Panel — IV MOTORCLASS')

@section('content')

  {{-- The one thing this screen is for. A man opens the panel to put a car up;
       everything else he can reach from the bar. --}}
  <div class="ad-head">
    <div class="ad-head__t">
      <h1 class="ad-h1">Panel</h1>
      {{-- APP_LOCALE is 'en' and changing it would reach the public site's
           validation messages, so the locale is set on this one call. --}}
      <p class="ad-head__p">{{ \Illuminate\Support\Str::ucfirst(now()->locale('es')->translatedFormat('l, j \d\e F')) }}</p>
    </div>
    <div class="ad-head__go">
      <a class="ad-btn" href="{{ route('admin.vehicles.create') }}">Añadir coche</a>
    </div>
  </div>

  {{-- Four numbers, and every one of them can move. The seven tiles this
       replaces included three that have shown zero since the tables were
       created. "Vistas" is not here either: it was labelled "del mes" and was
       the all-time sum. --}}
  <ul class="ad-facts">
    <li>
      <a class="ad-fact" href="{{ route('admin.vehicles.index', ['status' => 'available']) }}">
        <span class="ad-fact__k">En la web</span>
        <span class="ad-fact__v">{{ $nLive }}</span>
        <span class="ad-fact__n">coches a la venta</span>
      </a>
    </li>
    <li>
      <a class="ad-fact" href="{{ route('admin.vehicles.index', ['status' => 'sold']) }}">
        <span class="ad-fact__k">Vendidos</span>
        <span class="ad-fact__v">{{ $nGone }}</span>
        <span class="ad-fact__n">siguen publicados, con sus fotos</span>
      </a>
    </li>
    <li>
      <a class="ad-fact" href="{{ route('admin.testimonials.index') }}">
        <span class="ad-fact__k">Opiniones</span>
        <span class="ad-fact__v">{{ $nSays }}</span>
        <span class="ad-fact__n">activas en la portada</span>
      </a>
    </li>
    <li>
      <a class="ad-fact" href="{{ route('admin.contacts.index') }}">
        <span class="ad-fact__k">Mensajes reales</span>
        <span class="ad-fact__v">{{ $nReal }}</span>
        <span class="ad-fact__n">de {{ $nMsgTotal }} recibidos</span>
      </a>
    </li>
  </ul>

  {{-- Only what is true today. Four invented rows used to sit here saying
       "Nueva consulta recibida — hace 4 horas" on a site that has never
       received one. --}}
  <section class="ad-sec">
    <h2 class="ad-h2">Te espera</h2>
    @if($todo)
      <ul class="ad-list">
        @foreach($todo as $t)
          <li>
            <div class="ad-msg">
              <div class="ad-msg__h">
                <span class="ad-msg__who">{{ $t['n'] }} {{ $t['what'] }}</span>
              </div>
              <p class="ad-msg__body">{{ $t['why'] }}</p>
              <div class="ad-msg__act">
                <a class="ad-btn ad-btn--q ad-btn--s" href="{{ $t['to'] }}">Ver</a>
              </div>
            </div>
          </li>
        @endforeach
      </ul>
    @else
      <div class="ad-empty">
        <p class="ad-empty__t">Nada pendiente</p>
        <p class="ad-empty__p">Todos los coches en la web tienen fotos y descripción, y no hay mensajes
          nuevos que parezcan reales.</p>
      </div>
    @endif
  </section>

  {{-- The stock itself, because that is what the panel is for. The old first
       screen never showed a single car. --}}
  <section class="ad-sec">
    <div class="ad-head">
      <div class="ad-head__t">
        <h2 class="ad-h2" style="margin:0">En la web ahora</h2>
      </div>
      <div class="ad-head__go">
        <a class="ad-btn ad-btn--q ad-btn--s" href="{{ route('admin.vehicles.index') }}">Ver los {{ $nLive + $nGone }}</a>
      </div>
    </div>

    @if($live->isNotEmpty())
      <ul class="ad-list">
        @foreach($live as $car)
          <li>@include('admin.partials.car', ['car' => $car])</li>
        @endforeach
      </ul>
    @else
      <div class="ad-empty">
        <p class="ad-empty__t">No hay ningún coche a la venta</p>
        <p class="ad-empty__p">El catálogo enseña los vendidos, pero un visitante que llega hoy no
          encuentra nada que comprar.</p>
      </div>
    @endif
  </section>

@endsection
