@extends('layouts.ad')

@section('title', 'Solicitudes de prueba — IV MOTORCLASS')

@section('content')

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Solicitudes de prueba</h1>
    <p class="ad-head__p">Del formulario que había en la ficha del coche.</p>
  </div>
</div>

{{-- This page used to show the contact messages a second time, underneath, so
     the same 233 rows appeared in two places in the panel. They live in
     Mensajes and nowhere else. --}}
@if($inquiries->isNotEmpty())
  <ul class="ad-list">
    @foreach($inquiries as $q)
      <li>
        <article class="ad-msg">
          <div class="ad-msg__h">
            <span class="ad-msg__who">{{ $q->name ?? 'Sin nombre' }}</span>
            <span class="ad-msg__when">{{ $q->created_at?->format('d/m/Y H:i') }}</span>
          </div>
          @if($q->message)<p class="ad-msg__body">{{ $q->message }}</p>@endif
          <div class="ad-msg__ways">
            @if($q->phone)<a class="ad-msg__way" href="https://wa.me/{{ preg_replace('~\D~', '', $q->phone) }}">{{ $q->phone }}</a>@endif
            @if($q->email)<a class="ad-msg__way" href="mailto:{{ $q->email }}">{{ $q->email }}</a>@endif
          </div>
          <div class="ad-msg__act">
            <form action="{{ route('admin.inquiries.destroy', $q) }}" method="POST" onsubmit="return confirm('¿Borrar esta solicitud?')">
              @csrf
              @method('DELETE')
              <button class="ad-btn ad-btn--bad ad-btn--s" type="submit">Borrar</button>
            </form>
          </div>
        </article>
      </li>
    @endforeach
  </ul>
@else
  <div class="ad-empty">
    <p class="ad-empty__t">No hay ninguna</p>
    <p class="ad-empty__p">Esta tabla está vacía desde que se creó, y el formulario de prueba
      ya no está en la ficha del coche. La página se queda por si vuelve.</p>
  </div>
@endif

@endsection
