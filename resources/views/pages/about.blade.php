@extends('layouts.app')
@section('title', 'Sobre Auto Premium - Líderes en la venta de vehículos premium')
@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/about.css') }}">
@endpush
@section('content')
<section class="py-5 about-page">
  <div class="container">
    <!-- Hero Section -->
    <div class="row align-items-center mb-5">
      <div class="col-lg-6">
        <h1 class="display-5 fw-bold mb-3">Auto Premium</h1>
        <p class="lead text-secondary mb-4">Líderes en la venta de vehículos premium en España, con más de 4 años de experiencia y miles de clientes satisfechos.</p>
        <div class="d-flex gap-3">
          <a href="{{ route('catalog') }}" class="btn btn-primary btn-lg">Ver catálogo</a>
          <a href="{{ route('contact') }}" class="btn btn-outline-primary btn-lg">Contáctanos</a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="ratio ratio-16x9 rounded overflow-hidden shadow">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d13333315.892943129!2d-17.576527996018655!3d35.325753487332875!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xc42e3783261bc8b%3A0xa6ec2c940768a3ec!2sSpain!5e0!3m2!1sen!2s!4v1758042385615!5m2!1sen!2s" 
                  style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Harta showroom Auto Premium"></iframe>
        </div>
      </div>
    </div>

    <!-- Statistici impressive -->
    <div class="row text-center mb-5 about-stats">
      <div class="col-6 col-md-3 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="display-4 fw-bold text-primary mb-2">2500+</div>
            <h6 class="text-secondary mb-0">Vehículos vendidos</h6>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="display-4 fw-bold text-success mb-2">98%</div>
            <h6 class="text-secondary mb-0">Clientes satisfechos</h6>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="display-4 fw-bold text-warning mb-2">15</div>
            <h6 class="text-secondary mb-0">Marcas premium</h6>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="display-4 fw-bold text-info mb-2">4+</div>
            <h6 class="text-secondary mb-0">Años de experiencia</h6>
          </div>
        </div>
      </div>
    </div>

    <!-- Povestea noastră -->
    <div class="row mb-5">
      <div class="col-lg-8 mx-auto text-center">
        <h2 class="h3 mb-4">Nuestra historia</h2>
        <p class="lead text-secondary mb-4">
          Auto Premium nació de la pasión por los vehículos de calidad y el deseo de ofrecer a los clientes una experiencia de compra excepcional. 
          Desde el principio, nos hemos centrado en la transparencia, calidad y servicios de alta gama.
        </p>
        <p class="text-secondary">
          Nuestro equipo de expertos verifica cuidadosamente cada vehículo, ofreciendo garantías extendidas y soporte completo en el proceso de compra. 
          Creemos que cada cliente merece conducir el vehículo perfecto para sus necesidades.
        </p>
      </div>
    </div>

    <!-- Timeline en Español -->
    <div class="row mb-5 about-timeline">
      <div class="col-12">
        <h2 class="h3 text-center mb-5">Nuestra evolución</h2>
        <div class="timeline position-relative">
          <div class="timeline-line d-none d-md-block"></div>

          <!-- Origen y pasión -->
          <div class="row gy-4 mb-4">
            <div class="col-12 col-md-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="timeline-icon bg-primary text-white">
                      <i class="bi bi-flag"></i>
                    </div>
                    <div class="ms-3">
                      <h5 class="mb-0">Origen y pasión</h5>
                      <small class="text-secondary">Desde pequeño</small>
                    </div>
                  </div>
                  <p class="text-secondary mb-0">Desde pequeño, los coches han sido más que una afición para mí: han sido parte de mi ADN. Todo empezó con un coche teledirigido de Fórmula 1 de Fernando Alonso que me hizo soñar en grande.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- 2016 -->
          <div class="row gy-4 mb-4">
            <div class="col-12 col-md-6 offset-md-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="timeline-icon bg-success text-white">
                      <i class="bi bi-tools"></i>
                    </div>
                    <div class="ms-3">
                      <h5 class="mb-0">Primeros pasos profesionales</h5>
                      <span class="timeline-year badge bg-success ms-2">2016</span>
                      <div><small class="text-secondary">Aprendiz en concesionario oficial</small></div>
                    </div>
                  </div>
                  <p class="text-secondary mb-0">A los 16 años (2016), empecé mi carrera profesional como aprendiz en un concesionario oficial en España. No tardé en darme cuenta de que esto era solo el principio.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- 2018 -->
          <div class="row gy-4 mb-4">
            <div class="col-12 col-md-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="timeline-icon bg-warning text-dark">
                      <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="ms-3">
                      <h5 class="mb-0">Escala y liderazgo</h5>
                      <span class="timeline-year badge bg-warning text-dark ms-2">2018</span>
                      <div><small class="text-secondary">Startup automovilística en Reino Unido</small></div>
                    </div>
                  </div>
                  <p class="text-secondary mb-0">En 2018, dejé España buscando más. Me uní a una startup automovilística en Reino Unido que, en solo dos años, pasó de tener 20 a más de 1000 coches en stock. Fui líder en ventas, empujando y aprendiendo cada día, formando nuevos equipos y superando récords mensuales. Pero algo dentro de mí quería más: más calidad, más transparencia, más autenticidad.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- 2025 -->
          <div class="row gy-4 mb-4">
            <div class="col-12 col-md-6 offset-md-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="timeline-icon bg-info text-white">
                      <i class="bi bi-stars"></i>
                    </div>
                    <div class="ms-3">
                      <h5 class="mb-0">Un nuevo estándar</h5>
                      <span class="timeline-year badge bg-info ms-2">2025</span>
                      <div><small class="text-secondary">Calidad sin compromisos</small></div>
                    </div>
                  </div>
                  <p class="text-secondary mb-2">En 2025 decidí que era hora de cambiar las reglas del juego y dar un giro al mercado con unidades fabulosas.</p>
                  <p class="text-secondary mb-0">Abrimos en España con un stock selecto que se vendió en tiempo récord.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Hoy -->
          <div class="row gy-4">
            <div class="col-12 col-md-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="timeline-icon bg-dark text-white">
                      <i class="bi bi-bullseye"></i>
                    </div>
                    <div class="ms-3">
                      <h5 class="mb-0">Hoy</h5>
                      <small class="text-secondary">Misión y promesa</small>
                    </div>
                  </div>
                  <p class="text-secondary mb-0">Hoy, seguimos creciendo con una misión: ofrecer coches auténticos, únicos y reales, sin tonterías. Aquí, no vendemos solo coches. Compartimos una pasión. Y lo hacemos con la misma energía que me movió desde aquel primer coche teledirigido.</p>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Echipa / Fondatori -->
    <div class="row mb-5">
      <div class="col-12">
        <h2 class="h3 text-center mb-5">Equipo directivo</h2>
        <div class="row g-4 justify-content-center">
          <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm text-center">
              <div class="card-body p-4">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3" style="width: 80px; height: 80px;">
                  <i class="bi bi-person-fill fs-2"></i>
                </div>
                <h5 class="mb-2">Alexandru Popescu</h5>
                <p class="text-primary small mb-2">CEO & Co-Fundador</p>
                <p class="text-secondary small">15+ años en la industria automotriz, ex director comercial en BMW Rumania. Apasionado por la innovación y la experiencia del cliente.</p>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm text-center">
              <div class="card-body p-4">
                <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3" style="width: 80px; height: 80px;">
                  <i class="bi bi-person-fill fs-2"></i>
                </div>
                <h5 class="mb-2">Maria Ionescu</h5>
                <p class="text-success small mb-2">COO & Co-Fundadora</p>
                <p class="text-secondary small">Experta en operaciones y logística automotriz, con experiencia en Porsche y Mercedes-Benz. Especializada en procesos y calidad.</p>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm text-center">
              <div class="card-body p-4">
                <div class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center text-dark mb-3" style="width: 80px; height: 80px;">
                  <i class="bi bi-person-fill fs-2"></i>
                </div>
                <h5 class="mb-2">Andrei Marinescu</h5>
                <p class="text-warning small mb-2">CTO</p>
                <p class="text-secondary small">Tech lead con 12+ años de experiencia en tecnología automotriz y e-commerce. Arquitecto de la plataforma digital Auto Premium.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Valorile noastre -->
    <div class="row mb-5">
      <div class="col-lg-8 mx-auto">
        <h2 class="h3 text-center mb-5">Nuestros valores</h2>
        <div class="row g-4">
          <div class="col-md-6">
            <div class="d-flex align-items-start">
              <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                <i class="bi bi-shield-check"></i>
              </div>
              <div>
                <h6 class="mb-2">Transparencia total</h6>
                <p class="text-secondary small mb-0">Toda la información sobre vehículos está verificada y comunicada claramente. Sin sorpresas desagradables.</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex align-items-start">
              <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                <i class="bi bi-award"></i>
              </div>
              <div>
                <h6 class="mb-2">Calidad premium</h6>
                <p class="text-secondary small mb-0">Seleccionamos solo vehículos que cumplen con nuestros altos estándares de calidad y rendimiento.</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex align-items-start">
              <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                <i class="bi bi-people"></i>
              </div>
              <div>
                <h6 class="mb-2">Orientación al cliente</h6>
                <p class="text-secondary small mb-0">Cada cliente es único. Ofrecemos asesoramiento personalizado para encontrar el vehículo perfecto.</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex align-items-start">
              <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                <i class="bi bi-rocket"></i>
              </div>
              <div>
                <h6 class="mb-2">Innovación continua</h6>
                <p class="text-secondary small mb-0">Invertimos constantemente en tecnología y procesos para mejorar la experiencia de compra.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    


    <!-- Call to action final -->
    <div class="row">
      <div class="col-lg-8 mx-auto text-center">
        <div class="card border-0 bg-light">
          <div class="card-body p-5">
            <h3 class="mb-3">Encontremos el vehículo perfecto para ti</h3>
            <p class="text-secondary mb-4">Con nuestra experiencia de 4+ años y más de 2500 vehículos vendidos, estamos listos para ayudarte a tomar la decisión correcta.</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
              <a href="{{ route('catalog') }}" class="btn btn-primary btn-lg">Explora el catálogo</a>
              <a href="{{ route('contact') }}" class="btn btn-outline-primary btn-lg">Habla con un experto</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

