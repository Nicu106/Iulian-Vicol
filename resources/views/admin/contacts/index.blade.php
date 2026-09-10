@extends('layouts.ad')

@section('title', 'Mensajes — IV MOTORCLASS')

@section('content')

<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Mensajes</h1>
    <p class="ad-head__p">Del formulario de contacto de la web.</p>
  </div>
</div>

{{-- The two piles. The page used to say "Total: 233 mensajes" as if that were
     a result; 230 of them are spam and he had no way to tell which. --}}
<div class="ad-seg" role="group" aria-label="Qué mensajes">
  <a class="ad-seg__o {{ $pile === 'real' ? 'is-on' : '' }}"
     href="{{ route('admin.contacts.index') }}"
     @if($pile === 'real') aria-current="true" @endif>
    Reales <span class="ad-seg__n">{{ $nReal }}</span>
  </a>
  <a class="ad-seg__o {{ $pile === 'junk' ? 'is-on' : '' }}"
     href="{{ route('admin.contacts.index', ['pile' => 'junk']) }}"
     @if($pile === 'junk') aria-current="true" @endif>
    Spam <span class="ad-seg__n">{{ $nJunk }}</span>
  </a>
</div>

@if($pile === 'junk')
  <p class="ad-lede">No se ha borrado nada. Aquí está lo que trae un enlace, lo que llega
    repetido, lo que viene en otro alfabeto y lo que manda el mismo nombre una y otra vez.
    Si algo real acaba aquí, dímelo y ajusto la regla.</p>
@endif

@if($messages->isNotEmpty())
  <ul class="ad-list">
    @foreach($messages as $m)
      <li>
        <article class="ad-msg {{ $pile === 'junk' ? 'ad-msg--junk' : '' }}">
          <div class="ad-msg__h">
            <span class="ad-msg__who">{{ $m->name ?: 'Sin nombre' }}</span>
            <span class="ad-msg__when">{{ $m->created_at?->format('d/m/Y H:i') }}</span>
            @if($m->subject)
              <span class="ad-chip">{{ $m->subject }}</span>
            @endif
          </div>

          {{-- The whole message. The old table cut every body at the right
               edge of the screen and offered no way to open one. --}}
          <p class="ad-msg__body">{{ $m->message }}</p>

          <div class="ad-msg__ways">
            @if($m->phone)
              {{-- WhatsApp, because that is where he actually answers. --}}
              <a class="ad-msg__way" href="https://wa.me/{{ preg_replace('~\D~', '', $m->phone) }}">{{ $m->phone }}</a>
            @endif
            @if($m->email)
              <a class="ad-msg__way" href="mailto:{{ $m->email }}">{{ $m->email }}</a>
            @endif
          </div>

          <div class="ad-msg__act">
            <form action="{{ route('admin.contacts.destroy', $m) }}" method="POST"
                  onsubmit="return confirm('¿Borrar este mensaje?')">
              @csrf
              @method('DELETE')
              <button class="ad-btn ad-btn--bad ad-btn--s" type="submit">Borrar</button>
            </form>
          </div>
        </article>
      </li>
    @endforeach
  </ul>

  @if($pages > 1)
    <nav class="ad-pages" aria-label="Páginas">
      @for($i = 1; $i <= $pages; $i++)
        @if($i === $page)
          <span class="is-on" aria-current="page">{{ $i }}</span>
        @else
          <a href="{{ route('admin.contacts.index', array_filter(['pile' => $pile === 'junk' ? 'junk' : null, 'page' => $i])) }}">{{ $i }}</a>
        @endif
      @endfor
    </nav>
  @endif
@else
  <div class="ad-empty">
    <p class="ad-empty__t">{{ $pile === 'junk' ? 'Nada en spam' : 'Ningún mensaje real' }}</p>
    <p class="ad-empty__p">
      @if($pile === 'real')
        De {{ $nReal + $nJunk }} mensajes recibidos, ninguno parece de una persona. La gente
        te escribe por WhatsApp.
      @else
        Todavía no ha llegado spam.
      @endif
    </p>
  </div>
@endif

@endsection
