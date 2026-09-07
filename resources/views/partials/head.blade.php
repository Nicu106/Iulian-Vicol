{{-- One header for every built page. $current marks the page you are on. --}}
<header class="mc-head">
  <div class="mc-head__in cat-wrap" style="padding-block:0">
    <a class="mc-logo" href="/inicio">IV&nbsp;MOTORCLASS</a>
    <nav class="mc-nav">
      <a class="mc-nav__i {{ ($current ?? '') === 'inicio' ? 'is-current' : '' }}" href="/inicio">Inicio</a>
      <a class="mc-nav__i {{ ($current ?? '') === 'catalogo' ? 'is-current' : '' }}" href="/catalogo">Coches</a>
      <a class="mc-nav__i {{ ($current ?? '') === 'brandbook' ? 'is-current' : '' }}" href="/brandbook">Quién soy</a>
      <a class="mc-nav__i {{ ($current ?? '') === 'contacto' ? 'is-current' : '' }}" href="/contacto">Contacto</a>
      <a class="mc-nav__i {{ ($current ?? '') === 'vender' ? 'is-current' : '' }}" href="/vende">Vende tu coche</a>
    </nav>
    <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187">WhatsApp</a>
  </div>
</header>
