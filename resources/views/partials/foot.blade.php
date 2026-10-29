{{-- The footer, shared by /catalogo and /coche.
     Everything the live footer carries, minus the repetition: three lines that all
     said "Consultar" are one line that says it once. --}}
<footer class="mc-foot">
  {{-- Where he is, as the footer's opening: a full-bleed band of our own line
       drawing of Málaga (App\Support\StaticMap, 'malaga-band'), faded into the
       navy at every edge so it is the footer's ground and not a picture on it.
       It draws itself the first time it is reached (public/js/map-draw.js);
       without script it is simply there. The words sit over the sea, bottom
       right, which is the one calm region the drawing has. --}}
  @if(trim($__env->yieldContent('foot-map')) !== 'off')
  <section class="mc-foot__place" aria-labelledby="f-place">
    <div class="mc-foot__band" data-map-draw data-src="/img/map/malaga-band.svg">
      <img class="mc-foot__img" src="/img/map/malaga-band.svg" width="2400" height="800" alt="" loading="lazy" decoding="async">
      <span class="mc-foot__pin" aria-hidden="true"><b>Málaga</b></span>
    </div>
    <div class="mc-foot__say-where cat-wrap">
      <div>
        <h2 class="mc-foot__place-h" id="f-place">Quedamos donde esté el coche</h2>
        <p>Málaga, sin tienda: trabajo con cita.</p>
        <a class="mc-foot__maps" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode('Málaga, España') }}"
           target="_blank" rel="noopener">Ver en Google Maps</a>
        <small class="mc-foot__osm">Mapa © OpenStreetMap</small>
      </div>
    </div>
  </section>
  @endif

  <div class="mc-foot__in cat-wrap">

    <div class="mc-foot__lead">
      <a class="mc-foot__logo" href="/catalogo">IV&nbsp;MOTORCLASS</a>
      <p class="mc-foot__say">Coches alemanes premium en Málaga. Cinco marcas, y sólo esas.</p>
      <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187?text=Hola%2C+quiero+quedar+para+ver+un+coche.">Reservar cita</a>
    </div>

    <nav class="mc-foot__col" aria-labelledby="f-nav">
      <h2 class="mc-foot__h" id="f-nav">Navegación</h2>
      <ul>
        <li><a href="/catalogo">Coches</a></li>
        <li><a href="/catalogo#entregados">Entregados</a></li>
        <li><a href="/por-que-nosotros">Por qué nosotros</a></li>
        <li><a href="/contacto">Contacto</a></li>
        <li><a href="/vende">Vende tu coche</a></li>
        <li><a href="/recomienda">Recomienda a un amigo</a></li>
      </ul>
    </nav>

    <div class="mc-foot__col">
      <h2 class="mc-foot__h">Contacto</h2>
      <ul>
        <li><a href="tel:+34614753187">+34 614 753 187</a></li>
        <li><a href="https://wa.me/34614753187?text=Hola%2C+vengo+de+la+web+y+busco+un+coche.">WhatsApp</a></li>
        <li><a href="mailto:jvmotorclass@gmail.com">jvmotorclass@gmail.com</a></li>
        <li><a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode('Málaga, España') }}"
               target="_blank" rel="noopener">Málaga, España</a></li>
      </ul>
    </div>

    <div class="mc-foot__col">
      <h2 class="mc-foot__h">Horario</h2>
      <ul>
        <li><span>Todos los días · <b>a convenir</b></span></li>
        <li><span class="mc-foot__quiet">Escríbeme y quedamos a la hora que te venga bien.</span></li>
      </ul>

      {{-- The three accounts (HomePageController::SOCIAL), under the hours, so the
           footer keeps its four tracks. Each in its platform's own mark — the
           same drawing as the home page's band (partials/social-mark) — beside
           its name, in the footer's own list style: not an icon row, which is
           every footer's. Counted in the panel as "redes" with the platform. --}}
      <h2 class="mc-foot__h mc-foot__h--next" id="f-soc">Redes</h2>
      <ul class="mc-foot__soc" aria-labelledby="f-soc">
        @foreach(\App\Http\Controllers\HomePageController::SOCIAL as $k => $s)
          <li><a href="{{ $s['url'] }}" target="_blank" rel="noopener" data-stat="redes" data-stat-k="{{ $k }}">@include('partials.social-mark', ['k' => $k]){{ $s['name'] }}</a></li>
        @endforeach
      </ul>
    </div>
  </div>

  {{-- The signature. Set to the exact width of the container, cropped by the foot of
       the page, so it reads as the building's name cut into the wall rather than as a
       logo dropped in a corner. --}}
  <div class="mc-foot__sign" aria-hidden="true">
    <span class="mc-foot__mark">IV MOTORCLASS</span>
  </div>

  <div class="mc-foot__legal cat-wrap">
    <span>© {{ date('Y') }} IV MOTORCLASS</span>
    <span class="mc-foot__legal-sep">·</span>
    <a href="/terminos">Términos y condiciones</a>
    <span class="mc-foot__legal-sep">·</span>
    <a href="/privacidad">Política de privacidad</a>
  </div>
  {{-- Two notices the Junta de Andalucía asks of a car dealer's every advert and,
       without a shop, of its website: Decreto 256/2003 art. 2.4 (the vehicle's
       own document) and Decreto 82/2022 art. 7.3 (complaint sheets). The words
       are the decree's; the quiet size is ours. --}}
  <p class="mc-foot__notice cat-wrap">Existen hojas de quejas y reclamaciones a disposición de las
    personas consumidoras y usuarias, en papel y en formato electrónico. Este establecimiento
    comercial posee, a disposición del consumidor que lo solicite, un documento individualizado
    de cada vehículo expuesto, en el que se indican, entre otros datos, las características del
    mismo, su titularidad, la garantía que, en su caso, posea y su precio.</p>
</footer>
