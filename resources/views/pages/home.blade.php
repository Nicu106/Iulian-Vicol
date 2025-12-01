@extends('layouts.app')

@section('title','MOTORCLASS - Inicio')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/home.css') }}">
  <link rel="preload" as="image" href="https://images.unsplash.com/photo-1706397097971-8a42cf4e2450?q=80&w=1920&auto=format&fit=crop" imagesrcset="https://images.unsplash.com/photo-1706397097971-8a42cf4e2450?q=80&w=1280&auto=format&fit=crop 1280w, https://images.unsplash.com/photo-1706397097971-8a42cf4e2450?q=80&w=1920&auto=format&fit=crop 1920w" imagesizes="100vw">
@endpush

@section('content')
  <section class="home-hero text-light home-page" data-anim="reveal">
    <div class="container py-5">
      <div class="col-lg-8">
        <h1 class="display-4 fw-bold">Tu coche perfecto te espera</h1>
        <p class="lead mb-1">Meticulosamente escogido y llevado a los estandares mas altos para ti</p>
        <p class="mb-4">Explora nuestro catalogo ya!</p>
        <form class="hero-search mb-3" action="{{ route('catalog') }}" method="get" role="search" aria-label="Buscar marca o modelo">
          <div class="input-group input-group-lg">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" name="q" placeholder="Buscar marca o modelo..." value="{{ request('q') }}">
            <button class="btn btn-primary">Buscar</button>
          </div>
        </form>
        <div class="d-flex gap-2 flex-wrap hero-ctas">
          <a href="{{ route('catalog') }}" class="btn btn-primary btn-lg btn-cta">Ver Catálogo</a>
          <a href="{{ route('contact') }}" class="btn btn-outline-light btn-lg">Solicitar Oferta</a>
        </div>
        <ul class="list-inline small mt-3 hero-benefits">
          <li class="list-inline-item"><span class="badge bg-success-subtle text-success border">Verificado técnicamente</span></li>
          <li class="list-inline-item"><span class="badge bg-primary-subtle text-primary border">Garantía</span></li>
          <li class="list-inline-item"><span class="badge bg-info-subtle text-dark border">Historial claro</span></li>
        </ul>
      </div>
    </div>
  </section>

  <section class="py-5 bg-light values-section" data-anim="reveal">
    <div class="container text-center">
      <h2 class="fw-bold section-title">Nuestros Valores</h2>
      <p class="text-secondary mb-4 section-subtitle">Los principios que nos definen y nos guían cada día</p>
      <div class="row g-4 align-items-stretch">
        <div class="col-md-4">
          <div class="card shadow-sm h-100 value-card">
            <div class="card-body">
              <div class="value-icon mb-3" aria-hidden="true">🛡️</div>
              <h5 class="card-title value-title">Transparencia</h5>
              <p class="card-text small text-secondary value-text">Historial completo y verificación técnica detallada para cada vehículo.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm h-100 value-card">
            <div class="card-body">
              <div class="value-icon mb-3" aria-hidden="true">⚡</div>
              <h5 class="card-title value-title">Calidad</h5>
              <p class="card-text small text-secondary value-text">Verificado cuidadosamente por técnicos especializados para calidad superior.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm h-100 value-card">
            <div class="card-body">
              <div class="value-icon mb-3" aria-hidden="true">💙</div>
              <h5 class="card-title value-title">Confianza</h5>
              <p class="card-text small text-secondary value-text">Relaciones de confianza a través de servicios de calidad y soporte post-compra.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  

  <section class="py-5 category-section" data-anim="reveal">
    <div class="container">
      <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
          <h2 class="fw-bold mb-1">Elige tu próximo coche</h2>
          <p class="text-secondary mb-0">Segmentados según las preferencias más buscadas</p>
        </div>
        <a href="{{ route('catalog') }}" class="btn btn-outline-primary">Ver todos</a>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm category-card">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">SUV para familia</h5>
              <p class="card-text small text-secondary mb-3">Espacio, seguridad y comodidad para viajes largos.</p>
              <div class="row g-3">
                @forelse(($suvs ?? collect()) as $v)
                <div class="col-12">
                  <a href="{{ route('vehicle.show', $v->slug) }}" class="d-flex align-items-center text-decoration-none">
                    @php
                      $img = $v->cover_image ?? (is_array($v->gallery_images) && count($v->gallery_images) ? $v->gallery_images[0] : null);
                      $thumb = $img;
                      if (is_string($img) && preg_match('/^\/storage\//', $img) === 1) {
                        try { $thumb = route('img.resize', ['w' => 240]) . '?p=' . urlencode($img); } catch (\Throwable $e) { /* ignore */ }
                      }
                    @endphp
                    <div class="me-3" style="width:72px;height:48px;border-radius:8px;overflow:hidden;background:#f3f4f6;flex-shrink:0;">
                      @if($thumb)
                        <img src="{{ $thumb }}" alt="{{ $v->title ?? ($v->brand.' '.$v->model) }}" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                      @else
                        <div class="d-flex w-100 h-100 align-items-center justify-content-center text-muted"><i class="bi bi-car-front"></i></div>
                      @endif
                    </div>
                    <div class="flex-grow-1">
                      <div class="fw-semibold text-dark">{{ $v->title ?? ($v->brand.' '.$v->model.' '.$v->year) }}</div>
                      <div class="small text-secondary">€{{ number_format($v->display_price ?? $v->price, 0, ',', '.') }}</div>
                    </div>
                  </a>
                </div>
                @empty
                  <div class="col-12 small text-secondary">Momentan nu avem SUV-uri potrivite.</div>
                @endforelse
              </div>
              <div class="mt-auto pt-2">
                <a href="{{ route('catalog', ['body_type' => 'SUV']) }}" class="btn btn-primary category-cta w-100">Ver en catálogo</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm category-card">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Sedán premium</h5>
              <p class="card-text small text-secondary mb-3">Elegancia y tecnología para una conducción refinada.</p>
              <div class="row g-3">
                @forelse(($sedans ?? collect()) as $v)
                <div class="col-12">
                  <a href="{{ route('vehicle.show', $v->slug) }}" class="d-flex align-items-center text-decoration-none">
                    @php
                      $img = $v->cover_image ?? (is_array($v->gallery_images) && count($v->gallery_images) ? $v->gallery_images[0] : null);
                      $thumb = $img;
                      if (is_string($img) && preg_match('/^\/storage\//', $img) === 1) {
                        try { $thumb = route('img.resize', ['w' => 240]) . '?p=' . urlencode($img); } catch (\Throwable $e) { /* ignore */ }
                      }
                    @endphp
                    <div class="me-3" style="width:72px;height:48px;border-radius:8px;overflow:hidden;background:#f3f4f6;flex-shrink:0;">
                      @if($thumb)
                        <img src="{{ $thumb }}" alt="{{ $v->title ?? ($v->brand.' '.$v->model) }}" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                      @else
                        <div class="d-flex w-100 h-100 align-items-center justify-content-center text-muted"><i class="bi bi-car-front"></i></div>
                      @endif
                    </div>
                    <div class="flex-grow-1">
                      <div class="fw-semibold text-dark">{{ $v->title ?? ($v->brand.' '.$v->model.' '.$v->year) }}</div>
                      <div class="small text-secondary">€{{ number_format($v->display_price ?? $v->price, 0, ',', '.') }}</div>
                    </div>
                  </a>
                </div>
                @empty
                  <div class="col-12 small text-secondary">Momentan nu avem sedane potrivite.</div>
                @endforelse
              </div>
              <div class="mt-auto pt-2">
                <a href="{{ route('catalog', ['body_type' => 'Sedan']) }}" class="btn btn-primary category-cta w-100">Ver en catálogo</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm category-card">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Híbrido y eléctrico</h5>
              <p class="card-text small text-secondary mb-3">Eficiencia y costos reducidos de uso.</p>
              <div class="row g-3">
                @forelse(($evs ?? collect()) as $v)
                <div class="col-12">
                  <a href="{{ route('vehicle.show', $v->slug) }}" class="d-flex align-items-center text-decoration-none">
                    @php
                      $img = $v->cover_image ?? (is_array($v->gallery_images) && count($v->gallery_images) ? $v->gallery_images[0] : null);
                      $thumb = $img;
                      if (is_string($img) && preg_match('/^\/storage\//', $img) === 1) {
                        try { $thumb = route('img.resize', ['w' => 240]) . '?p=' . urlencode($img); } catch (\Throwable $e) { /* ignore */ }
                      }
                    @endphp
                    <div class="me-3" style="width:72px;height:48px;border-radius:8px;overflow:hidden;background:#f3f4f6;flex-shrink:0;">
                      @if($thumb)
                        <img src="{{ $thumb }}" alt="{{ $v->title ?? ($v->brand.' '.$v->model) }}" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                      @else
                        <div class="d-flex w-100 h-100 align-items-center justify-content-center text-muted"><i class="bi bi-car-front"></i></div>
                      @endif
                    </div>
                    <div class="flex-grow-1">
                      <div class="fw-semibold text-dark">{{ $v->title ?? ($v->brand.' '.$v->model.' '.$v->year) }}</div>
                      <div class="small text-secondary">€{{ number_format($v->display_price ?? $v->price, 0, ',', '.') }}</div>
                    </div>
                  </a>
                </div>
                @empty
                  <div class="col-12 small text-secondary">Momentan nu avem hibride/electrice potrivite.</div>
                @endforelse
              </div>
              <div class="mt-auto pt-2">
                <a href="{{ route('catalog', ['fuel' => 'Electric']) }}" class="btn btn-primary category-cta w-100">Ver en catálogo</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5" data-anim="reveal">
    <div class="container text-center">
      <h2 class="fw-bold">Nuestros Servicios</h2>
      <p class="text-secondary mb-4">Soluciones completas para tus necesidades automotrices</p>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card shadow-sm h-100"><div class="card-body">
            <div class="display-6 mb-2">🚗</div>
            <h5 class="card-title">Test Drive</h5>
            <p class="card-text small text-secondary">Prueba nuestros vehículos sin compromiso y somételos a cualquier prueba, ¡tan preparados estamos!</p>
          </div></div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm h-100"><div class="card-body">
            <div class="display-6 mb-2">📋</div>
            <h5 class="card-title">Consultación</h5>
            <p class="card-text small text-secondary">Pídenos el coche que deseas y prometemos entregarte la mejor unidad del mercado.</p>
          </div></div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm h-100"><div class="card-body">
            <div class="display-6 mb-2">🛡️</div>
            <h5 class="card-title">Garantías</h5>
            <p class="card-text small text-secondary">Puedes disfrutar de 2 o 3 años de tranquilidad estando protegido con la garantía mecánica de más confianza.</p>
          </div></div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 bg-light" data-anim="reveal">
    <div class="container">
      <div class="row g-4 align-items-center steps">
        <div class="col-lg-5">
          <h2 class="fw-bold mb-3">Proceso simple en 4 pasos</h2>
          <p class="text-secondary mb-4">Desde la selección hasta la entrega, nuestro equipo te guía sin complicaciones.</p>
          <a href="{{ route('catalog') }}" class="btn btn-primary">Comenzar ahora</a>
        </div>
        <div class="col-lg-7">
          <div class="row g-3">
            <div class="col-6">
              <div class="p-3 border rounded-3 h-100 bg-white">
                <div class="badge rounded-pill text-bg-primary mb-2">1</div>
                <div class="fw-semibold">Elige</div>
                <div class="small text-secondary">Busca en el catálogo el modelo adecuado.</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 border rounded-3 h-100 bg-white">
                <div class="badge rounded-pill text-bg-primary mb-2">2</div>
                <div class="fw-semibold">Verifica</div>
                <div class="small text-secondary">Historial claro e informe técnico detallado.</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 border rounded-3 h-100 bg-white">
                <div class="badge rounded-pill text-bg-primary mb-2">3</div>
                <div class="fw-semibold">Prueba</div>
                <div class="small text-secondary">Programa una prueba de manejo sin compromisos.</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 border rounded-3 h-100 bg-white">
                <div class="badge rounded-pill text-bg-primary mb-2">4</div>
                <div class="fw-semibold">Finaliza</div>
                <div class="small text-secondary">Financiación y entrega rápida.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  @php
    $carouselTestimonials = \App\Models\Testimonial::where('is_active', true)->orderBy('order_index')->get();
  @endphp
  @if($carouselTestimonials->count() > 0)
  <section class="py-5" data-anim="reveal">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold">Opiniones de clientes</h2>
        <p class="text-secondary">Confianza construida a través de experiencias reales</p>
      </div>
      
      <!-- Carousel cu imaginile clienților -->
      <div id="testimonialsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
        <div class="carousel-inner">
          @foreach($carouselTestimonials->chunk(3) as $i => $chunk)
          <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
            <div class="row g-4">
              @foreach($chunk as $t)
              <div class="col-md-4">
                <div class="testimonial-card position-relative overflow-hidden rounded-4 shadow-lg">
                  @if($t->image_path)
                    <img src="{{ $t->image_path }}" class="testimonial-bg-image" alt="Cliente {{ $t->author_name }}" loading="lazy">
                  @else
                    <img src="https://images.unsplash.com/photo-1514316454349-750a7fd3da3a?q=80&w=1200&auto=format&fit=crop" class="testimonial-bg-image" alt="Cliente" loading="lazy">
                  @endif
                  <div class="testimonial-overlay">
                    <div class="testimonial-content text-center text-white">
                      <p class="testimonial-text mb-3">"{{ $t->quote }}"</p>
                      <div class="testimonial-author">— {{ $t->author_name }}@if($t->author_location), {{ $t->author_location }}@endif</div>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          @endforeach
        </div>
        
        <!-- Controale carousel -->
        <button class="carousel-control-prev" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Siguiente</span>
        </button>
        
        <!-- Indicatori -->
        <div class="carousel-indicators">
          @foreach($carouselTestimonials->chunk(3) as $i => $chunk)
          <button type="button" data-bs-target="#testimonialsCarousel" data-bs-slide-to="{{ $i }}" @if($i === 0) class="active" aria-current="true" @endif aria-label="Slide {{ $i + 1 }}"></button>
          @endforeach
        </div>
      </div>
    </div>
  </section>
  @endif

  <section class="py-5 bg-light" data-anim="reveal">
    <div class="container">
      <div class="row g-4 align-items-start">
        <div class="col-lg-6">
          <h2 class="fw-bold mb-3">Preguntas frecuentes</h2>
          <p class="text-secondary mb-0">Respondemos a las preguntas más comunes para que decidas informado.</p>
        </div>
        <div class="col-lg-6">
          <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header" id="faqOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseOne" aria-expanded="false" aria-controls="faqCollapseOne">
                  ¿Ofrecen garantía para vehículos?
                </button>
              </h2>
              <div id="faqCollapseOne" class="accordion-collapse collapse" aria-labelledby="faqOne" data-bs-parent="#faqAccordion">
                <div class="accordion-body small text-secondary">Sí, ofrecemos garantía extendida hasta 24 meses, dependiendo del modelo.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="faqTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseTwo" aria-expanded="false" aria-controls="faqCollapseTwo">
                  ¿Puedo comprar en leasing o con financiación?
                </button>
              </h2>
              <div id="faqCollapseTwo" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#faqAccordion">
                <div class="accordion-body small text-secondary">Sí, colaboramos con socios financieros para ofertas rápidas y ventajosas.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="faqThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseThree" aria-expanded="false" aria-controls="faqCollapseThree">
                  ¿Puedo programar una prueba de manejo?
                </button>
              </h2>
              <div id="faqCollapseThree" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#faqAccordion">
                <div class="accordion-body small text-secondary">Por supuesto. Completa el formulario de contacto o llámanos para programar.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="final-cta text-light py-5" data-anim="reveal">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-8">
          <h2 class="fw-bold mb-2">Encuentra tu coche perfecto hoy</h2>
          <p class="mb-0 text-light-emphasis">Stock actualizado, verificaciones completas y ofertas flexibles de financiación.</p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
          <a href="{{ route('catalog') }}" class="btn btn-primary btn-lg">Entrar al catálogo</a>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('js/pages/home.js') }}" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Configurează carousel-ul pentru auto-play la 2 secunde
      const carousel = document.getElementById('testimonialsCarousel');
      if (carousel) {
        const bsCarousel = new bootstrap.Carousel(carousel, {
          interval: 4000,
          wrap: true,
          pause: 'hover'
        });
        
        // Pornește carousel-ul automat
        bsCarousel.cycle();
      }
    });
  </script>
@endpush

