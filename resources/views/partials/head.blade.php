{{-- One header for every built page. $current marks the page you are on. --}}
<header class="mc-head">
  <div class="mc-head__in cat-wrap" style="padding-block:0">
    <a class="mc-logo" href="/">IV&nbsp;MOTORCLASS</a>
    <nav class="mc-nav">
      {{-- on a phone the logo is the way home, so "Inicio" gives its place to "Por qué nosotros" --}}
      <a class="mc-nav__i mc-nav__i--home {{ ($current ?? '') === 'inicio' ? 'is-current' : '' }}" href="/">Inicio</a>
      <a class="mc-nav__i {{ ($current ?? '') === 'catalogo' ? 'is-current' : '' }}" href="/catalogo">Coches</a>
      <a class="mc-nav__i {{ ($current ?? '') === 'porque' ? 'is-current' : '' }}" href="/por-que-nosotros" aria-label="Por qué nosotros"><span class="mc-nav__pre">Por qué </span><span class="mc-nav__rest">nosotros</span></a>
      <a class="mc-nav__i {{ ($current ?? '') === 'contacto' ? 'is-current' : '' }}" href="/contacto">Contacto</a>
      <a class="mc-nav__i {{ ($current ?? '') === 'vender' ? 'is-current' : '' }}" href="/vende">Vende tu coche</a>
    </nav>
    <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187?text=Hola%2C+vengo+de+la+web+y+busco+un+coche.">WhatsApp</a>
  </div>
</header>
