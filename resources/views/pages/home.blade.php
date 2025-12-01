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

  <section class="py-5 values-section-premium" data-anim="reveal">
    <div class="container">
      <div class="text-center mb-5">
        <span class="text-uppercase text-primary fw-semibold letter-spacing-2 small">Por qué elegirnos</span>
        <h2 class="fw-bold mt-2 mb-3" style="font-size: 2.25rem;">Nuestros Valores</h2>
        <div class="mx-auto" style="width: 60px; height: 3px; background: linear-gradient(90deg, #0d6efd, #0dcaf0);"></div>
      </div>
      <div class="row g-4 g-lg-5">
        <div class="col-md-4">
          <div class="value-card-premium text-center h-100">
            <div class="value-icon-premium mx-auto mb-4">
              <i class="bi bi-shield-check"></i>
            </div>
            <h5 class="fw-bold mb-3">Transparencia</h5>
            <p class="text-muted mb-0">Historial completo y verificación técnica detallada para cada vehículo que ofrecemos.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="value-card-premium text-center h-100">
            <div class="value-icon-premium mx-auto mb-4">
              <i class="bi bi-award"></i>
            </div>
            <h5 class="fw-bold mb-3">Calidad Premium</h5>
            <p class="text-muted mb-0">Cada vehículo es verificado por técnicos especializados garantizando los más altos estándares.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="value-card-premium text-center h-100">
            <div class="value-icon-premium mx-auto mb-4">
              <i class="bi bi-people"></i>
            </div>
            <h5 class="fw-bold mb-3">Compromiso</h5>
            <p class="text-muted mb-0">Construimos relaciones duraderas a través de un servicio excepcional y soporte post-venta.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <style>
    .values-section-premium {
      background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
    }
    .letter-spacing-2 {
      letter-spacing: 2px;
    }
    .value-card-premium {
      padding: 2rem 1.5rem;
      border-radius: 16px;
      background: #fff;
      border: 1px solid rgba(0,0,0,0.06);
      transition: all 0.3s ease;
    }
    .value-card-premium:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.08);
      border-color: transparent;
    }
    .value-icon-premium {
      width: 80px;
      height: 80px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      background: linear-gradient(135deg, #f0f7ff 0%, #e8f4fd 100%);
      border: 2px solid rgba(13, 110, 253, 0.1);
    }
    .value-icon-premium i {
      font-size: 2rem;
      background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .value-card-premium:hover .value-icon-premium {
      background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
      border-color: transparent;
    }
    .value-card-premium:hover .value-icon-premium i {
      background: #fff;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
  </style>

  

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

  <section class="py-5 services-section-premium" data-anim="reveal">
    <div class="container">
      <div class="text-center mb-5">
        <span class="text-uppercase text-primary fw-semibold letter-spacing-2 small">Lo que ofrecemos</span>
        <h2 class="fw-bold mt-2 mb-3" style="font-size: 2.25rem;">Nuestros Servicios</h2>
        <div class="mx-auto" style="width: 60px; height: 3px; background: linear-gradient(90deg, #0d6efd, #0dcaf0);"></div>
      </div>
      <div class="row g-4 g-lg-5">
        <div class="col-md-4">
          <div class="service-card-premium h-100">
            <div class="service-icon-premium">
              <i class="bi bi-speedometer2"></i>
            </div>
            <h5 class="fw-bold mb-3">Test Drive</h5>
            <p class="text-muted mb-0">Prueba nuestros vehículos sin compromiso y somételos a cualquier prueba. Estamos seguros de su calidad.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="service-card-premium h-100">
            <div class="service-icon-premium">
              <i class="bi bi-search"></i>
            </div>
            <h5 class="fw-bold mb-3">Búsqueda Personalizada</h5>
            <p class="text-muted mb-0">Pídenos el coche que deseas y prometemos encontrarte la mejor unidad disponible en el mercado.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="service-card-premium h-100">
            <div class="service-icon-premium">
              <i class="bi bi-patch-check"></i>
            </div>
            <h5 class="fw-bold mb-3">Garantía Extendida</h5>
            <p class="text-muted mb-0">Disfruta de hasta 3 años de tranquilidad con nuestra garantía mecánica de máxima confianza.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <style>
    .services-section-premium {
      background: #fff;
    }
    .service-card-premium {
      padding: 2.5rem 2rem;
      border-radius: 20px;
      background: linear-gradient(145deg, #f8f9fa 0%, #ffffff 100%);
      border: 1px solid rgba(0,0,0,0.05);
      text-align: center;
      position: relative;
      overflow: hidden;
      transition: all 0.4s ease;
    }
    .service-card-premium::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #0d6efd, #0dcaf0);
      transform: scaleX(0);
      transition: transform 0.4s ease;
    }
    .service-card-premium:hover::before {
      transform: scaleX(1);
    }
    .service-card-premium:hover {
      transform: translateY(-10px);
      box-shadow: 0 25px 50px rgba(0,0,0,0.1);
    }
    .service-icon-premium {
      width: 70px;
      height: 70px;
      margin: 0 auto 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 16px;
      background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
      box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
    }
    .service-icon-premium i {
      font-size: 1.75rem;
      color: #fff;
    }
  </style>

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
  <section class="py-5 testimonials-section-premium" data-anim="reveal">
    <div class="container">
      <div class="text-center mb-5">
        <span class="text-uppercase text-primary fw-semibold letter-spacing-2 small">Testimonios</span>
        <h2 class="fw-bold mt-2 mb-3" style="font-size: 2.25rem;">Opiniones de clientes</h2>
        <div class="mx-auto" style="width: 60px; height: 3px; background: linear-gradient(90deg, #0d6efd, #0dcaf0);"></div>
        <p class="text-muted mt-3">Confianza construida a través de experiencias reales</p>
      </div>

      <!-- Testimonials Slider -->
      <div class="testimonials-slider-wrapper">
        <div class="testimonials-slider" id="testimonialsSlider">
          @foreach($carouselTestimonials as $t)
          <div class="testimonial-slide">
            <div class="testimonial-card-new">
              <div class="testimonial-image-wrapper">
                @if($t->image_path)
                  <img src="{{ $t->image_path }}" class="testimonial-image" alt="Cliente {{ $t->author_name }}" loading="lazy">
                @else
                  <img src="https://images.unsplash.com/photo-1514316454349-750a7fd3da3a?q=80&w=600&auto=format&fit=crop" class="testimonial-image" alt="Cliente" loading="lazy">
                @endif
                <div class="testimonial-overlay-new"></div>
              </div>
              <div class="testimonial-content-new">
                <div class="quote-icon"><i class="bi bi-quote"></i></div>
                <p class="testimonial-quote">"{{ $t->quote }}"</p>
                <div class="testimonial-author-new">
                  <span class="author-name">{{ $t->author_name }}</span>
                  @if($t->author_location)
                  <span class="author-location">{{ $t->author_location }}</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>

        <!-- Navigation Arrows -->
        <button class="slider-nav slider-prev" id="sliderPrev" aria-label="Anterior">
          <i class="bi bi-chevron-left"></i>
        </button>
        <button class="slider-nav slider-next" id="sliderNext" aria-label="Siguiente">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>

      <!-- Dots Indicator -->
      <div class="slider-dots" id="sliderDots"></div>
    </div>
  </section>

  <style>
    .testimonials-section-premium {
      background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
    }
    .testimonials-slider-wrapper {
      position: relative;
      max-width: 900px;
      margin: 0 auto;
      padding: 0 50px;
    }
    .testimonials-slider {
      display: flex;
      overflow: hidden;
      scroll-behavior: smooth;
    }
    .testimonial-slide {
      min-width: 100%;
      padding: 0 10px;
      box-sizing: border-box;
    }
    .testimonial-card-new {
      display: flex;
      flex-direction: row;
      background: #fff;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0,0,0,0.1);
      min-height: 350px;
    }
    .testimonial-image-wrapper {
      flex: 0 0 45%;
      position: relative;
      overflow: hidden;
    }
    .testimonial-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .testimonial-overlay-new {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(13,110,253,0.1) 0%, transparent 100%);
    }
    .testimonial-content-new {
      flex: 1;
      padding: 2.5rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .quote-icon {
      font-size: 3rem;
      color: #0d6efd;
      opacity: 0.3;
      line-height: 1;
      margin-bottom: 1rem;
    }
    .testimonial-quote {
      font-size: 1.15rem;
      line-height: 1.7;
      color: #333;
      margin-bottom: 1.5rem;
      font-style: italic;
    }
    .testimonial-author-new {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }
    .author-name {
      font-weight: 700;
      color: #1a1a2e;
      font-size: 1.1rem;
    }
    .author-location {
      color: #6c757d;
      font-size: 0.9rem;
    }
    .slider-nav {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: #fff;
      border: none;
      box-shadow: 0 5px 20px rgba(0,0,0,0.15);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      z-index: 10;
    }
    .slider-nav:hover {
      background: #0d6efd;
      color: #fff;
      transform: translateY(-50%) scale(1.1);
    }
    .slider-nav i {
      font-size: 1.25rem;
    }
    .slider-prev { left: 0; }
    .slider-next { right: 0; }
    .slider-dots {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 2rem;
    }
    .slider-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: #dee2e6;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .slider-dot.active {
      background: #0d6efd;
      transform: scale(1.2);
    }
    .slider-dot:hover {
      background: #0d6efd;
    }
    @media (max-width: 767.98px) {
      .testimonials-slider-wrapper {
        padding: 0 40px;
      }
      .testimonial-card-new {
        flex-direction: column;
        min-height: auto;
      }
      .testimonial-image-wrapper {
        flex: 0 0 200px;
        height: 200px;
      }
      .testimonial-content-new {
        padding: 1.5rem;
      }
      .testimonial-quote {
        font-size: 1rem;
      }
      .slider-nav {
        width: 40px;
        height: 40px;
      }
      .slider-prev { left: 0; }
      .slider-next { right: 0; }
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const slider = document.getElementById('testimonialsSlider');
      const slides = slider.querySelectorAll('.testimonial-slide');
      const prevBtn = document.getElementById('sliderPrev');
      const nextBtn = document.getElementById('sliderNext');
      const dotsContainer = document.getElementById('sliderDots');
      let currentIndex = 0;
      const totalSlides = slides.length;
      let autoSlideInterval;

      // Create dots
      slides.forEach((_, i) => {
        const dot = document.createElement('button');
        dot.className = 'slider-dot' + (i === 0 ? ' active' : '');
        dot.addEventListener('click', () => goToSlide(i));
        dotsContainer.appendChild(dot);
      });

      const dots = dotsContainer.querySelectorAll('.slider-dot');

      function updateSlider() {
        slider.style.transform = `translateX(-${currentIndex * 100}%)`;
        dots.forEach((dot, i) => {
          dot.classList.toggle('active', i === currentIndex);
        });
      }

      function goToSlide(index) {
        currentIndex = index;
        if (currentIndex >= totalSlides) currentIndex = 0;
        if (currentIndex < 0) currentIndex = totalSlides - 1;
        updateSlider();
        resetAutoSlide();
      }

      function nextSlide() {
        goToSlide(currentIndex + 1);
      }

      function prevSlide() {
        goToSlide(currentIndex - 1);
      }

      function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 5000);
      }

      function resetAutoSlide() {
        clearInterval(autoSlideInterval);
        startAutoSlide();
      }

      prevBtn.addEventListener('click', prevSlide);
      nextBtn.addEventListener('click', nextSlide);

      // Touch/swipe support
      let touchStartX = 0;
      let touchEndX = 0;

      slider.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
      }, {passive: true});

      slider.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        if (touchStartX - touchEndX > 50) nextSlide();
        if (touchEndX - touchStartX > 50) prevSlide();
      }, {passive: true});

      // Start auto-slide
      startAutoSlide();

      // Pause on hover
      slider.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
      slider.addEventListener('mouseleave', startAutoSlide);
    });
  </script>
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

