{{-- An empty place in a marque's row: "Próximamente". Not a car, and not
     dressed as one — no photograph, no price, no chips. The card is reversed out
     of the row's own colour (a ghost of a card), with the marque's mark where the
     photograph would be. The whole card opens WhatsApp with the question already
     written. $marque is the row; $i is the card's place (0-3).

     Four lines, one per card, so four empty places read as the four things he can
     say about a marque he has not had yet, not as a loading skeleton repeated. --}}
@php
  $lines = [
    'El próximo '.$marque['name'].' que entre, aquí primero.',
    '¿Buscas un modelo concreto? Te lo busco.',
    'Revisado y con un año de garantía, como todos.',
    'Te aviso por WhatsApp en cuanto llegue.',
  ];
  $wa = 'https://wa.me/34614753187?text='.rawurlencode('Hola, avísame cuando entre un '.$marque['name'].'.');
@endphp
<article class="mc-card mc-card--soon">
  <a class="mc-card__link" href="{{ $wa }}">
    <div class="mc-frame mc-frame--card mc-soon__frame">
      <span class="mc-soon__mark" aria-hidden="true"
            style="--logo:url('{{ asset('img/marques/'.$marque['key'].'.svg') }}')"></span>
    </div>
    <div class="mc-card__body">
      <h3 class="mc-card__title">Próximamente</h3>
      <p class="mc-card__sub">{{ $lines[$i % count($lines)] }}</p>
      <span class="mc-soon__go">Avísame por WhatsApp</span>
    </div>
  </a>
</article>
