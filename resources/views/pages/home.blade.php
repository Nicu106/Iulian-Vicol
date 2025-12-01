@extends('layouts.app')

@section('title','MOTORCLASS - Inicio')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/home.css') }}">
  <link rel="preload" as="image" href="https://images.unsplash.com/photo-1706397097971-8a42cf4e2450?q=80&w=1920&auto=format&fit=crop" imagesrcset="https://images.unsplash.com/photo-1706397097971-8a42cf4e2450?q=80&w=1280&auto=format&fit=crop 1280w, https://images.unsplash.com/photo-1706397097971-8a42cf4e2450?q=80&w=1920&auto=format&fit=crop 1920w" imagesizes="100vw">
@endpush

@section('content')
  <section class="hero-luxury text-light" data-anim="reveal">
    <div class="hero-overlay"></div>
    <div class="container position-relative py-5">
      <div class="row min-vh-50 align-items-center">
        <div class="col-lg-7">
          <span class="hero-eyebrow">IV MOTORCLASS</span>
          <h1 class="hero-title">Tu coche perfecto<br>te espera</h1>
          <p class="hero-subtitle">Meticulosamente escogido y llevado a los estándares más altos para ti</p>

          <form class="hero-search-luxury" action="{{ route('catalog') }}" method="get" role="search">
            <div class="search-wrapper">
              <i class="bi bi-search search-icon"></i>
              <input type="text" name="q" placeholder="Buscar marca o modelo..." value="{{ request('q') }}">
              <button type="submit">Buscar</button>
            </div>
          </form>

          <div class="hero-buttons">
            <a href="{{ route('catalog') }}" class="btn-hero-primary">
              <span>Ver Catálogo</span>
              <i class="bi bi-arrow-right"></i>
            </a>
            <a href="{{ route('contact') }}" class="btn-hero-outline">
              Solicitar Oferta
            </a>
          </div>

          <div class="hero-features">
            <div class="hero-feature">
              <i class="bi bi-patch-check-fill"></i>
              <span>Verificado</span>
            </div>
            <div class="hero-feature">
              <i class="bi bi-shield-check"></i>
              <span>Garantía</span>
            </div>
            <div class="hero-feature">
              <i class="bi bi-file-earmark-text"></i>
              <span>Historial claro</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <style>
    .hero-luxury {
      position: relative;
      min-height: 85vh;
      display: flex;
      align-items: center;
      background: url('https://images.unsplash.com/photo-1514316454349-750a7fd3da3a?q=80&w=1920&auto=format&fit=crop') center/cover no-repeat;
    }
    .hero-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.5) 50%, rgba(0,0,0,0.4) 100%);
    }
    .hero-eyebrow {
      display: inline-block;
      font-size: 0.75rem;
      font-weight: 500;
      letter-spacing: 4px;
      color: #0d6efd;
      margin-bottom: 1rem;
    }
    .hero-title {
      font-size: clamp(2.5rem, 5vw, 4rem);
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 1.25rem;
      letter-spacing: -1px;
    }
    .hero-subtitle {
      font-size: 1.1rem;
      color: rgba(255,255,255,0.7);
      margin-bottom: 2rem;
      max-width: 480px;
    }
    .hero-search-luxury {
      margin-bottom: 2rem;
    }
    .search-wrapper {
      display: flex;
      align-items: center;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.15);
      border-radius: 8px;
      padding: 0.5rem;
      max-width: 500px;
      transition: all 0.3s ease;
    }
    .search-wrapper:focus-within {
      background: rgba(255,255,255,0.15);
      border-color: rgba(13, 110, 253, 0.5);
    }
    .search-wrapper .search-icon {
      color: rgba(255,255,255,0.5);
      padding: 0 1rem;
      font-size: 1.1rem;
    }
    .search-wrapper input {
      flex: 1;
      background: transparent;
      border: none;
      color: #fff;
      font-size: 1rem;
      padding: 0.75rem 0;
      outline: none;
    }
    .search-wrapper input::placeholder {
      color: rgba(255,255,255,0.5);
    }
    .search-wrapper button {
      background: #0d6efd;
      color: #fff;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 6px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .search-wrapper button:hover {
      background: #0b5ed7;
    }
    .hero-buttons {
      display: flex;
      gap: 1rem;
      margin-bottom: 2.5rem;
      flex-wrap: wrap;
    }
    .btn-hero-primary {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      background: #0d6efd;
      color: #fff;
      padding: 1rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    .btn-hero-primary:hover {
      background: #0b5ed7;
      transform: translateY(-2px);
      color: #fff;
    }
    .btn-hero-primary i {
      transition: transform 0.3s ease;
    }
    .btn-hero-primary:hover i {
      transform: translateX(4px);
    }
    .btn-hero-outline {
      display: inline-flex;
      align-items: center;
      background: transparent;
      color: #fff;
      padding: 1rem 2rem;
      border: 1px solid rgba(255,255,255,0.3);
      border-radius: 8px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    .btn-hero-outline:hover {
      background: rgba(255,255,255,0.1);
      border-color: rgba(255,255,255,0.5);
      color: #fff;
    }
    .hero-features {
      display: flex;
      gap: 2rem;
      flex-wrap: wrap;
    }
    .hero-feature {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: rgba(255,255,255,0.7);
      font-size: 0.9rem;
    }
    .hero-feature i {
      color: #0d6efd;
      font-size: 1rem;
    }
    .min-vh-50 {
      min-height: 50vh;
    }
    @media (max-width: 767.98px) {
      .hero-luxury {
        min-height: 100vh;
        padding-top: 80px;
      }
      .hero-title {
        font-size: 2.25rem;
      }
      .hero-buttons {
        flex-direction: column;
      }
      .btn-hero-primary, .btn-hero-outline {
        justify-content: center;
      }
      .hero-features {
        gap: 1rem;
      }
    }
  </style>

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
    $totalCount = $carouselTestimonials->count();
    $remainder = $totalCount % 3;
    if ($remainder > 0 && $totalCount > 0) {
      $needed = 3 - $remainder;
      for ($i = 0; $i < $needed; $i++) {
        $carouselTestimonials->push($carouselTestimonials[$i % $totalCount]);
      }
    }
  @endphp
  @if($carouselTestimonials->count() > 0)
  <section class="testimonials-elite py-5" data-anim="reveal">
    <div class="container">
      <!-- Header Elite -->
      <div class="text-center mb-5">
        <span class="elite-badge">CLIENTES SATISFECHOS</span>
        <h2 class="elite-title">Experiencias que hablan por nosotros</h2>
      </div>

      <!-- Carousel Elite -->
      <div id="testimonialsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
          @foreach($carouselTestimonials->chunk(3) as $i => $chunk)
          <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
            <div class="row g-4">
              @foreach($chunk as $t)
              <div class="col-md-4">
                <div class="testimonial-elite-card">
                  <!-- Imagen de fondo -->
                  <div class="elite-image-bg">
                    @if($t->image_path)
                      <img src="{{ $t->image_path }}" alt="{{ $t->author_name }}" loading="lazy">
                    @else
                      <img src="https://images.unsplash.com/photo-1494976388531-d1058494cdd8?q=80&w=800&auto=format&fit=crop" alt="Cliente" loading="lazy">
                    @endif
                  </div>

                  <!-- Overlay con contenido -->
                  <div class="elite-overlay"></div>
                  <div class="elite-content">
                    <div class="elite-stars">
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="elite-quote">"{{ $t->quote }}"</p>
                    <div class="elite-author">
                      <span class="elite-name">{{ $t->author_name }}</span>
                      @if($t->author_location)
                      <span class="elite-location">{{ $t->author_location }}</span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          @endforeach
        </div>

        <!-- Controles Elite -->
        <button class="elite-nav elite-nav-prev" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="prev">
          <i class="bi bi-arrow-left"></i>
        </button>
        <button class="elite-nav elite-nav-next" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="next">
          <i class="bi bi-arrow-right"></i>
        </button>
      </div>

      <!-- Indicadores Elite -->
      <div class="elite-indicators">
        @foreach($carouselTestimonials->chunk(3) as $i => $chunk)
        <button type="button" data-bs-target="#testimonialsCarousel" data-bs-slide-to="{{ $i }}" class="elite-dot {{ $i === 0 ? 'active' : '' }}"></button>
        @endforeach
      </div>
    </div>
  </section>

  <style>
    .testimonials-elite {
      background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
      position: relative;
    }
    .elite-badge {
      display: inline-block;
      font-size: 0.7rem;
      font-weight: 500;
      letter-spacing: 4px;
      color: #0d6efd;
      border: 1px solid rgba(13, 110, 253, 0.3);
      padding: 0.5rem 1.5rem;
      margin-bottom: 1.25rem;
    }
    .elite-title {
      font-size: 2.25rem;
      font-weight: 300;
      color: #fff;
      letter-spacing: 1px;
    }
    .testimonial-elite-card {
      position: relative;
      height: 420px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
    }
    .elite-image-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1;
    }
    .elite-image-bg img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s ease;
    }
    .testimonial-elite-card:hover .elite-image-bg img {
      transform: scale(1.05);
    }
    .elite-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to top, rgba(15,23,42,0.95) 0%, rgba(15,23,42,0.5) 50%, rgba(15,23,42,0.1) 100%);
      z-index: 2;
    }
    .elite-content {
      position: relative;
      z-index: 3;
      padding: 2rem;
      display: flex;
      flex-direction: column;
    }
    .elite-stars {
      display: flex;
      gap: 4px;
      margin-bottom: 1rem;
    }
    .elite-stars i {
      color: #fbbf24;
      font-size: 0.85rem;
    }
    .elite-quote {
      font-size: 1rem;
      line-height: 1.7;
      color: rgba(255,255,255,0.9);
      font-weight: 300;
      font-style: italic;
      margin-bottom: 1.25rem;
    }
    .elite-author {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
      border-top: 1px solid rgba(255,255,255,0.1);
      padding-top: 1rem;
    }
    .elite-name {
      font-size: 0.95rem;
      font-weight: 500;
      color: #fff;
      letter-spacing: 1px;
    }
    .elite-location {
      font-size: 0.8rem;
      color: rgba(255,255,255,0.5);
      letter-spacing: 0.5px;
    }
    .elite-nav {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 50px;
      height: 50px;
      background: transparent;
      border: 1px solid rgba(255,255,255,0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
      z-index: 10;
      color: #fff;
    }
    .elite-nav:hover {
      background: #0d6efd;
      border-color: #0d6efd;
      color: #fff;
    }
    .elite-nav i {
      font-size: 1.1rem;
    }
    .elite-nav-prev { left: -60px; }
    .elite-nav-next { right: -60px; }
    .elite-indicators {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin-top: 2.5rem;
    }
    .elite-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: rgba(255,255,255,0.2);
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      padding: 0;
    }
    .elite-dot:hover {
      background: rgba(255,255,255,0.4);
    }
    .elite-dot.active {
      background: #0d6efd;
      width: 30px;
      border-radius: 10px;
    }
    @media (max-width: 1399.98px) {
      .elite-nav-prev { left: 10px; }
      .elite-nav-next { right: 10px; }
    }
    @media (max-width: 991.98px) {
      .elite-nav {
        width: 44px;
        height: 44px;
      }
    }
    @media (max-width: 767.98px) {
      .elite-title {
        font-size: 1.5rem;
      }
      .testimonial-elite-card {
        height: 380px;
      }
      .elite-nav {
        display: none;
      }
    }
  </style>
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

