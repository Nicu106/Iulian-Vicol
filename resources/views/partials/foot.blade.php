{{-- The footer, shared by /catalogo and /coche.
     Everything the live footer carries, minus the repetition: three lines that all
     said "Consultar" are one line that says it once. --}}
<footer class="mc-foot">
  <div class="mc-foot__in cat-wrap">

    <div class="mc-foot__lead">
      <a class="mc-foot__logo" href="/catalogo">IV&nbsp;MOTORCLASS</a>
      <p class="mc-foot__say">Coches alemanes premium en Málaga. Cinco marcas, y sólo esas.</p>
      <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187">Reservar cita</a>
    </div>

    <nav class="mc-foot__col" aria-labelledby="f-nav">
      <h2 class="mc-foot__h" id="f-nav">Navegación</h2>
      <ul>
        <li><a href="/catalogo">Coches</a></li>
        <li><a href="/catalogo#entregados">Entregados</a></li>
        <li><a href="/contacto">Contacto</a></li>
        <li><a href="/vende">Vende tu coche</a></li>
        <li><a href="/recomienda">Recomienda a un amigo</a></li>
      </ul>
    </nav>

    <div class="mc-foot__col">
      <h2 class="mc-foot__h">Contacto</h2>
      <ul>
        <li><a href="tel:+34614753187">+34 614 753 187</a></li>
        <li><a href="https://wa.me/34614753187">WhatsApp</a></li>
        <li><a href="mailto:jvmotorclass@gmail.com">jvmotorclass@gmail.com</a></li>
        {{-- Where, as a map rather than as two words: our own line drawing of the
             coast and the main roads in the footer's navy (App\Support\StaticMap),
             its edges faded into the footer so it is part of the ground and not a
             picture in a frame. Opens the real map; nothing from Google until then. --}}
        <li class="mc-foot__where">
          <a class="mc-foot__map" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode('Málaga, España') }}"
             target="_blank" rel="noopener" aria-label="Málaga, España, en Google Maps">
            <img src="/img/map/malaga-foot.svg" width="640" height="400" alt="" loading="lazy" decoding="async">
            <span class="mc-foot__pin" aria-hidden="true"><b>Málaga</b></span>
          </a>
          <a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode('Málaga, España') }}"
             target="_blank" rel="noopener">Málaga, España · Ver en el mapa</a>
          <small class="mc-foot__osm">Mapa © OpenStreetMap</small>
        </li>
      </ul>
    </div>

    <div class="mc-foot__col">
      <h2 class="mc-foot__h">Horario</h2>
      <ul>
        <li><span>Todos los días · <b>a convenir</b></span></li>
        <li><span class="mc-foot__quiet">Escríbeme y quedamos a la hora que te venga bien.</span></li>
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
    <a href="#">Términos y condiciones</a>
    <span class="mc-foot__legal-sep">·</span>
    <a href="#">Política de privacidad</a>
  </div>
</footer>
