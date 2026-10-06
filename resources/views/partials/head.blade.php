{{-- One header for every built page. $current marks the page you are on. --}}
<header class="mc-head">
  <div class="mc-head__in cat-wrap" style="padding-block:0">
    <a class="mc-logo" href="/">IV&nbsp;MOTORCLASS</a>
    <nav class="mc-nav">
      <a class="mc-nav__i {{ ($current ?? '') === 'inicio' ? 'is-current' : '' }}" href="/">Inicio</a>
      <a class="mc-nav__i {{ ($current ?? '') === 'catalogo' ? 'is-current' : '' }}" href="/catalogo">Coches</a>
      {{-- desk only: at 320-430 the four links already fill the row; on a phone the page is in the footer and on the home page --}}
      <a class="mc-nav__i mc-nav__i--desk {{ ($current ?? '') === 'porque' ? 'is-current' : '' }}" href="/por-que-nosotros">Por qué nosotros</a>
      <a class="mc-nav__i {{ ($current ?? '') === 'contacto' ? 'is-current' : '' }}" href="/contacto">Contacto</a>
      <a class="mc-nav__i {{ ($current ?? '') === 'vender' ? 'is-current' : '' }}" href="/vende">Vende tu coche</a>
    </nav>
    <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187?text=Hola%2C+vengo+de+la+web+y+busco+un+coche.">WhatsApp</a>
  </div>
</header>
