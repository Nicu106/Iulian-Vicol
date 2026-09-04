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
        <li><a href="/brandbook">Quién soy</a></li>
        <li><a href="/contacto">Contacto</a></li>
      </ul>
    </nav>

    <div class="mc-foot__col">
      <h2 class="mc-foot__h">Contacto</h2>
      <ul>
        <li><a href="tel:+34614753187">+34 614 753 187</a></li>
        <li><a href="https://wa.me/34614753187">WhatsApp</a></li>
        <li><a href="mailto:jvmotorclass@gmail.com">jvmotorclass@gmail.com</a></li>
        <li><span>Málaga, España</span></li>
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
