@extends('layouts.app')

@section('title','IV MOTORCLASS — Coches de ocasión seleccionados en Málaga')
@section('description','Coches de ocasión revisados uno a uno en Málaga. Historial verificado, garantía y financiación a medida.')

@section('content')

{{-- ============================ HÉROE ============================ --}}
<section class="v2-hero" data-anim="reveal">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <span class="v2-eyebrow">IV Motorclass · Málaga</span>
        <h1>Encuentra tu próximo coche,<br class="d-none d-md-block"> ya revisado.</h1>
        <p class="v2-lead mt-3">Cada coche pasa por nuestras manos antes de pasar por las tuyas:
        historial comprobado, mecánica revisada y toda la información sobre la mesa.</p>

        <form class="v2-search mt-4" action="{{ route('catalog') }}" method="get" role="search">
          <i class="bi bi-search"></i>
          <input type="text" name="q" placeholder="Busca por marca o modelo…" value="{{ request('q') }}" aria-label="Buscar coche">
          <button type="submit" class="v2-btn v2-btn--primary">Buscar</button>
        </form>

        <ul class="v2-hero__points mt-4">
          <li><i class="bi bi-patch-check-fill"></i>Historial verificado</li>
          <li><i class="bi bi-shield-check"></i>Garantía incluida</li>
          <li><i class="bi bi-wrench-adjustable"></i>Revisión mecánica</li>
          <li><i class="bi bi-cash-coin"></i>Financiación a medida</li>
        </ul>
      </div>

      <div class="col-lg-5">
        <div class="v2-hero__panel">
          <div class="v2-hero__stat">
            <strong>{{ $stats['available'] }}</strong>
            <span>coches disponibles ahora</span>
          </div>
          <div class="v2-hero__stat">
            <strong>{{ $stats['sold'] }}</strong>
            <span>entregados hasta hoy</span>
          </div>
          @if($stats['from'])
          <div class="v2-hero__stat">
            <strong>desde € {{ number_format($stats['from'], 0, ',', '.') }}</strong>
            <span>en stock</span>
          </div>
          @endif
          <a href="{{ route('catalog') }}" class="v2-btn v2-btn--primary v2-btn--block v2-btn--lg mt-2">
            Ver todos los coches <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ======================== CATEGORÍAS ======================== --}}
@if($bodyTypes->count())
<section class="v2-section v2-section--tint" data-anim="reveal">
  <div class="container">
    <div class="v2-head v2-head--center">
      <span class="v2-eyebrow">Por carrocería</span>
      <h2>¿Qué tipo de coche buscas?</h2>
      <p class="v2-lead">Sólo mostramos categorías con coches disponibles ahora mismo.</p>
    </div>
    <div class="row g-3 g-md-4 justify-content-center">
      @foreach($bodyTypes as $bt)
        <div class="col-6 col-md-4 col-lg-3">
          <a href="{{ route('catalog', ['body_type' => $bt['value']]) }}" class="v2-cat">
            <span class="v2-cat__icon"><i class="bi bi-car-front-fill"></i></span>
            <span class="v2-cat__name">{{ $bt['label'] }}</span>
            <span class="v2-cat__count">{{ $bt['total'] }} {{ $bt['total'] === 1 ? 'coche' : 'coches' }}</span>
          </a>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ========================== STOCK =========================== --}}
@if($featured->count())
<section class="v2-section" data-anim="reveal">
  <div class="container">
    <div class="v2-head d-flex flex-wrap justify-content-between align-items-end gap-3">
      <div>
        <span class="v2-eyebrow">En stock</span>
        <h2>Coches disponibles</h2>
        <p class="v2-lead mb-0">Todos revisados y listos para entrega.</p>
      </div>
      <a href="{{ route('catalog') }}" class="v2-btn v2-btn--ghost">Ver todos <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4">
      @foreach($featured as $v)
        <div class="col-12 col-sm-6 col-lg-3 d-flex">
          <x-v2-vehicle-card :vehicle="$v" />
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ======================== POR QUÉ NOSOTROS ==================== --}}
<section class="v2-section v2-section--tint" data-anim="reveal">
  <div class="container">
    <div class="v2-head v2-head--center">
      <span class="v2-eyebrow">Por qué IV Motorclass</span>
      <h2>Comprar sin sorpresas</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4 d-flex">
        <div class="v2-card v2-feature">
          <span class="v2-feature__icon"><i class="bi bi-file-earmark-text"></i></span>
          <h3>Historial sobre la mesa</h3>
          <p>Kilometraje, mantenimientos e informe técnico de cada coche, antes de que preguntes.</p>
        </div>
      </div>
      <div class="col-md-4 d-flex">
        <div class="v2-card v2-feature">
          <span class="v2-feature__icon"><i class="bi bi-wrench-adjustable-circle"></i></span>
          <h3>Preparado antes de entregar</h3>
          <p>Revisión mecánica y puesta a punto. Si algo no está bien, no sale del taller.</p>
        </div>
      </div>
      <div class="col-md-4 d-flex">
        <div class="v2-card v2-feature">
          <span class="v2-feature__icon"><i class="bi bi-people"></i></span>
          <h3>Acompañamiento real</h3>
          <p>Te atendemos antes y después de la compra. Prueba el coche sin compromiso.</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ========================== CIFRAS ========================== --}}
<section class="v2-section v2-section--ink" data-anim="reveal">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-6 col-lg-3"><div class="v2-metric"><strong>{{ $stats['sold'] }}</strong><span>coches entregados</span></div></div>
      <div class="col-6 col-lg-3"><div class="v2-metric"><strong>{{ $stats['available'] }}</strong><span>disponibles ahora</span></div></div>
      <div class="col-6 col-lg-3"><div class="v2-metric"><strong>{{ $stats['reviews'] }}</strong><span>opiniones de clientes</span></div></div>
      <div class="col-6 col-lg-3"><div class="v2-metric"><strong>Málaga</strong><span>atención presencial</span></div></div>
    </div>
  </div>
</section>

{{-- ======================== OPINIONES ========================= --}}
@if($testimonials->count())
<section class="v2-section" data-anim="reveal">
  <div class="container">
    <div class="v2-head v2-head--center">
      <span class="v2-eyebrow">Clientes</span>
      <h2>Entregas reales, opiniones reales</h2>
      <p class="v2-lead">{{ $testimonials->count() }} clientes ya se han llevado su coche con nosotros.</p>
    </div>

    <div id="testimonialsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000" data-bs-pause="hover">
      <div class="carousel-inner">
        @foreach($testimonials->chunk(3) as $i => $chunk)
        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
          <div class="row g-4 justify-content-center">
            @foreach($chunk as $t)
            <div class="col-12 col-md-4 d-flex">
              <article class="v2-card tc-card">
                <div class="tc-photo">
                  @php
                    $tImg = $t->image_path; $tSrc = $tImg; $tSrcset = null;
                    if (is_string($tImg) && preg_match('/^\/storage\//', $tImg) === 1) {
                      $tSrc    = route('img.resize', ['w' => 600]).'?p='.urlencode($tImg);
                      $tSrcset = route('img.resize', ['w' => 480]).'?p='.urlencode($tImg).' 480w, '
                               . route('img.resize', ['w' => 600]).'?p='.urlencode($tImg).' 600w, '
                               . route('img.resize', ['w' => 900]).'?p='.urlencode($tImg).' 900w';
                    }
                  @endphp
                  @if($tSrc)
                    <img src="{{ $tSrc }}" @if($tSrcset) srcset="{{ $tSrcset }}" sizes="(min-width:768px) 33vw, 100vw" @endif
                         alt="{{ $t->author_name }}" loading="lazy" decoding="async">
                  @else
                    <div class="tc-photo-placeholder"><i class="bi bi-person-circle"></i></div>
                  @endif
                </div>
                <div class="v2-card__body tc-body">
                  <div class="v2-stars tc-stars" aria-label="5 de 5">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                  </div>
                  <blockquote class="tc-quote">{{ $t->quote }}</blockquote>
                  <button type="button" class="tc-more" hidden data-more="Ver más" data-less="Ver menos">Ver más</button>
                  <div class="tc-author">
                    <span class="tc-name">{{ $t->author_name }}</span>
                    @if($t->author_location)<span class="tc-location">{{ $t->author_location }}</span>@endif
                  </div>
                </div>
              </article>
            </div>
            @endforeach
          </div>
        </div>
        @endforeach
      </div>

      <button class="v2-carousel-nav v2-carousel-nav--prev" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="prev" aria-label="Anterior"><i class="bi bi-chevron-left"></i></button>
      <button class="v2-carousel-nav v2-carousel-nav--next" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="next" aria-label="Siguiente"><i class="bi bi-chevron-right"></i></button>
    </div>

    <div class="v2-dots">
      @foreach($testimonials->chunk(3) as $i => $chunk)
        <button type="button" data-bs-target="#testimonialsCarousel" data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" aria-label="Grupo {{ $i + 1 }}"></button>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ========================== MARCAS ========================== --}}
@if($brands->count())
<section class="v2-section v2-section--tint2" data-anim="reveal">
  <div class="container">
    <div class="v2-head v2-head--center">
      <span class="v2-eyebrow">Marcas</span>
      <h2>Lo que tenemos ahora</h2>
    </div>
    <div class="d-flex flex-wrap justify-content-center gap-2 gap-md-3">
      @foreach($brands as $b)
        <a href="{{ route('catalog', ['brand' => $b['value']]) }}" class="v2-brandchip">
          {{ $b['value'] }} <span>{{ $b['total'] }}</span>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ========================== PROCESO ========================= --}}
<section class="v2-section" data-anim="reveal">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-5">
        <span class="v2-eyebrow">Cómo funciona</span>
        <h2>Cuatro pasos, sin letra pequeña</h2>
        <p class="v2-lead mt-3">Desde que eliges el coche hasta que te lo llevas, sabes en todo momento qué viene después.</p>
        <a href="{{ route('catalog') }}" class="v2-btn v2-btn--primary v2-btn--lg mt-3">Empezar ahora <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="col-lg-7">
        <ol class="v2-steps">
          <li><span>1</span><div><h3>Elige</h3><p>Filtra por carrocería, marca o presupuesto y quédate con los que te encajan.</p></div></li>
          <li><span>2</span><div><h3>Comprueba</h3><p>Te damos historial e informe técnico del coche. Pregunta lo que quieras.</p></div></li>
          <li><span>3</span><div><h3>Pruébalo</h3><p>Ven a verlo y condúcelo. Sin compromiso y sin prisa.</p></div></li>
          <li><span>4</span><div><h3>Llévatelo</h3><p>Cerramos la financiación si la necesitas y preparamos la entrega.</p></div></li>
        </ol>
      </div>
    </div>
  </div>
</section>

{{-- ============================ FAQ =========================== --}}
<section class="v2-section v2-section--tint" data-anim="reveal">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-5">
        <span class="v2-eyebrow">Dudas frecuentes</span>
        <h2>Antes de que preguntes</h2>
        <p class="v2-lead mt-3">Si falta algo, escríbenos por WhatsApp y te contestamos.</p>
        <a href="https://wa.me/34614753187" target="_blank" rel="noopener" class="v2-btn v2-btn--outline mt-3"><i class="bi bi-whatsapp"></i> Preguntar por WhatsApp</a>
      </div>
      <div class="col-lg-7">
        <div class="accordion v2-accordion" id="faqAccordion">
          <div class="accordion-item">
            <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">¿Los coches tienen garantía?</button></h3>
            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Sí. La cobertura concreta depende del modelo y de los años del coche; te la detallamos por escrito antes de la compra.</div></div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">¿Puedo financiar la compra?</button></h3>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Sí, trabajamos con entidades financieras y preparamos la propuesta según tu caso. Te explicamos el coste total antes de firmar nada.</div></div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">¿Puedo probar el coche?</button></h3>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Por supuesto. Escríbenos o llámanos y concertamos una cita para que lo veas y lo conduzcas.</div></div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">¿Aceptáis mi coche como parte del pago?</button></h3>
            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Lo valoramos sin compromiso. Puedes enviarnos los datos desde <a href="{{ route('sell-car') }}">Vende tu coche</a> y te damos una tasación.</div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ========================== CTA FINAL ======================= --}}
<section class="v2-cta" data-anim="reveal">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <h2>¿Hablamos de tu próximo coche?</h2>
        <p class="v2-lead mb-0">Cuéntanos qué buscas y te avisamos en cuanto entre algo que encaje.</p>
      </div>
      <div class="col-lg-4 d-flex flex-wrap gap-2 justify-content-lg-end">
        <a href="tel:+34614753187" class="v2-btn v2-btn--light v2-btn--lg"><i class="bi bi-telephone-fill"></i> Llamar</a>
        <a href="{{ route('contact') }}" class="v2-btn v2-btn--primary v2-btn--lg">Contactar</a>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
(function(){
  // --- revelado al hacer scroll
  if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches){
    var io=new IntersectionObserver(function(es){
      es.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('is-visible'); io.unobserve(e.target);} });
    },{threshold:.12,rootMargin:'0px 0px -8% 0px'});
    document.querySelectorAll('[data-anim="reveal"]').forEach(function(el){io.observe(el);});
  } else {
    document.querySelectorAll('[data-anim="reveal"]').forEach(function(el){el.classList.add('is-visible');});
  }

  // --- opiniones: recortar sólo si desborda + foto vertical/horizontal
  var BOX=3/4;
  function fitPhoto(img){
    if(!img.naturalWidth||!img.naturalHeight)return;
    if(img.naturalWidth/img.naturalHeight<=BOX+0.03) img.classList.add('is-cover');
  }
  function prepare(card){
    if(card.dataset.tcDone==='1')return;
    var q=card.querySelector('.tc-quote'),b=card.querySelector('.tc-more');
    if(!q||!b)return;
    if(!card.offsetParent&&card.offsetHeight===0)return;   // diapositiva oculta: se mide al mostrarse
    card.dataset.tcDone='1';
    q.classList.add('is-clamped');
    if(q.scrollHeight<=q.clientHeight+1){ q.classList.remove('is-clamped'); b.remove(); return; }
    b.hidden=false; b.setAttribute('aria-expanded','false');
    b.addEventListener('click',function(){
      var open=!q.classList.toggle('is-clamped');
      b.textContent=open?b.dataset.less:b.dataset.more;
      b.setAttribute('aria-expanded',open?'true':'false');
    });
  }
  function scan(){
    document.querySelectorAll('#testimonialsCarousel .tc-card').forEach(prepare);
    document.querySelectorAll('#testimonialsCarousel .tc-photo img').forEach(function(img){
      if(img.dataset.tcFit==='1')return; img.dataset.tcFit='1';
      img.complete?fitPhoto(img):img.addEventListener('load',function(){fitPhoto(img);},{once:true});
    });
  }
  function init(){ scan(); var c=document.getElementById('testimonialsCarousel'); if(c)c.addEventListener('slid.bs.carousel',scan); }
  document.readyState==='loading'?document.addEventListener('DOMContentLoaded',init):init();
})();
</script>
@endpush
