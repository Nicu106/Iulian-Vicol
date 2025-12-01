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
    // Completăm pentru a avea multiplu de 3 (pentru a evita golurile)
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
  <section class="testimonials-section-pro py-5" data-anim="reveal">
    <div class="container">
      <!-- Header Premium -->
      <div class="text-center mb-5">
        <span class="testimonials-badge">Testimonios verificados</span>
        <h2 class="testimonials-title">Lo que dicen nuestros clientes</h2>
        <p class="testimonials-subtitle">Experiencias reales de compradores satisfechos</p>
      </div>

      <!-- Carousel Premium -->
      <div id="testimonialsCarousel" class="carousel slide testimonials-carousel-pro" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
          @foreach($carouselTestimonials->chunk(3) as $i => $chunk)
          <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
            <div class="row g-4">
              @foreach($chunk as $t)
              <div class="col-md-4">
                <div class="testimonial-card-pro">
                  <div class="testimonial-image-container">
                    @if($t->image_path)
                      <img src="{{ $t->image_path }}" class="testimonial-image-pro" alt="Cliente {{ $t->author_name }}" loading="lazy">
                    @else
                      <img src="https://images.unsplash.com/photo-1514316454349-750a7fd3da3a?q=80&w=800&auto=format&fit=crop" class="testimonial-image-pro" alt="Cliente" loading="lazy">
                    @endif
                    <div class="testimonial-gradient"></div>
                    <div class="testimonial-quote-icon">
                      <i class="bi bi-quote"></i>
                    </div>
                  </div>
                  <div class="testimonial-body">
                    <p class="testimonial-text-pro">"{{ $t->quote }}"</p>
                    <div class="testimonial-footer">
                      <div class="testimonial-author-info">
                        <span class="author-name-pro">{{ $t->author_name }}</span>
                        @if($t->author_location)
                        <span class="author-location-pro"><i class="bi bi-geo-alt"></i> {{ $t->author_location }}</span>
                        @endif
                      </div>
                      <div class="testimonial-stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          @endforeach
        </div>

        <!-- Controale Premium -->
        <button class="carousel-nav-pro carousel-nav-prev" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="prev">
          <i class="bi bi-arrow-left"></i>
        </button>
        <button class="carousel-nav-pro carousel-nav-next" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="next">
          <i class="bi bi-arrow-right"></i>
        </button>
      </div>

      <!-- Indicatori Premium -->
      <div class="testimonials-indicators">
        @foreach($carouselTestimonials->chunk(3) as $i => $chunk)
        <button type="button" data-bs-target="#testimonialsCarousel" data-bs-slide-to="{{ $i }}" class="indicator-dot {{ $i === 0 ? 'active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
        @endforeach
      </div>
    </div>
  </section>

  <style>
    .testimonials-section-pro {
      background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
      position: relative;
      overflow: hidden;
    }
    .testimonials-section-pro::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
      opacity: 0.5;
    }
    .testimonials-badge {
      display: inline-block;
      background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
      color: #fff;
      padding: 0.5rem 1.25rem;
      border-radius: 50px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 1rem;
    }
    .testimonials-title {
      color: #fff;
      font-size: 2.5rem;
      font-weight: 800;
      margin-bottom: 0.75rem;
    }
    .testimonials-subtitle {
      color: rgba(255,255,255,0.6);
      font-size: 1.1rem;
      margin-bottom: 0;
    }
    .testimonial-card-pro {
      background: #fff;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      height: 100%;
    }
    .testimonial-card-pro:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 35px 60px -15px rgba(0,0,0,0.5);
    }
    .testimonial-image-container {
      position: relative;
      height: 220px;
      overflow: hidden;
    }
    .testimonial-image-pro {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s ease;
    }
    .testimonial-card-pro:hover .testimonial-image-pro {
      transform: scale(1.1);
    }
    .testimonial-gradient {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 100px;
      background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 100%);
    }
    .testimonial-quote-icon {
      position: absolute;
      top: 15px;
      right: 15px;
      width: 45px;
      height: 45px;
      background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
    }
    .testimonial-quote-icon i {
      color: #fff;
      font-size: 1.25rem;
    }
    .testimonial-body {
      padding: 1.75rem;
    }
    .testimonial-text-pro {
      color: #334155;
      font-size: 0.95rem;
      line-height: 1.7;
      margin-bottom: 1.25rem;
      font-style: italic;
    }
    .testimonial-footer {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      border-top: 1px solid #f1f5f9;
      padding-top: 1rem;
    }
    .testimonial-author-info {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }
    .author-name-pro {
      font-weight: 700;
      color: #0f172a;
      font-size: 1rem;
    }
    .author-location-pro {
      color: #64748b;
      font-size: 0.85rem;
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    .testimonial-stars {
      display: flex;
      gap: 2px;
    }
    .testimonial-stars i {
      color: #fbbf24;
      font-size: 0.9rem;
    }
    .carousel-nav-pro {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 55px;
      height: 55px;
      border-radius: 50%;
      background: #fff;
      border: none;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
      z-index: 10;
      color: #0f172a;
    }
    .carousel-nav-pro:hover {
      background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
      color: #fff;
      transform: translateY(-50%) scale(1.1);
    }
    .carousel-nav-pro i {
      font-size: 1.25rem;
    }
    .carousel-nav-prev { left: -25px; }
    .carousel-nav-next { right: -25px; }
    .testimonials-indicators {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin-top: 2.5rem;
    }
    .indicator-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: rgba(255,255,255,0.3);
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      padding: 0;
    }
    .indicator-dot:hover {
      background: rgba(255,255,255,0.6);
    }
    .indicator-dot.active {
      background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
      width: 35px;
      border-radius: 20px;
    }
    @media (max-width: 991.98px) {
      .carousel-nav-prev { left: 10px; }
      .carousel-nav-next { right: 10px; }
      .carousel-nav-pro {
        width: 45px;
        height: 45px;
      }
    }
    @media (max-width: 767.98px) {
      .testimonials-title {
        font-size: 1.75rem;
      }
      .testimonial-image-container {
        height: 180px;
      }
      .carousel-nav-pro {
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

