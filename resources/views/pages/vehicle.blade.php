@extends('layouts.app')
@php
  $baseTitle = $vehicle->title ?? ($vehicle->brand . ' ' . $vehicle->model . ' ' . $vehicle->year);
  $pageTitle = $vehicle->meta_title ?: $baseTitle;
  $fallbackDescParts = [];
  if (!empty($vehicle->year)) { $fallbackDescParts[] = (string)$vehicle->year; }
  if (!empty($vehicle->brand)) { $fallbackDescParts[] = (string)$vehicle->brand; }
  if (!empty($vehicle->model)) { $fallbackDescParts[] = (string)$vehicle->model; }
  if (!empty($vehicle->mileage)) { $fallbackDescParts[] = (is_numeric($vehicle->mileage) ? number_format($vehicle->mileage) : (string)$vehicle->mileage) . ' km'; }
  if (!empty($vehicle->fuel)) { $fallbackDescParts[] = (string)$vehicle->fuel; }
  if (!empty($vehicle->transmission)) { $fallbackDescParts[] = (string)$vehicle->transmission; }
  if (!empty($vehicle->location)) { $fallbackDescParts[] = 'Locație: ' . (string)$vehicle->location; }
  $fallbackDesc = trim(implode(' • ', $fallbackDescParts));
  $pageDescription = $vehicle->meta_description ?: ($fallbackDesc ?: 'Vehicul disponibil la MOTORCLASS.');
  $ogImage = $vehicle->cover_image ?: (file_exists(public_path('images/logo-motorclass.png')) ? asset('images/logo-motorclass.png') : asset('favicon.ico'));
  $canonicalUrl = route('vehicle.show', $vehicle->slug);
@endphp
@section('title', $pageTitle)
@section('description', $pageDescription)
@section('og:title', $pageTitle)
@section('og:description', $pageDescription)
@section('og:type', 'product')
@section('og:url', $canonicalUrl)
@section('og:image', $ogImage)
@section('twitter:title', $pageTitle)
@section('twitter:description', $pageDescription)
@section('twitter:image', $ogImage)

@php
  $extractPrice = function($priceStr) {
      if (is_numeric($priceStr)) return (float) $priceStr;
      if (empty($priceStr) || is_null($priceStr)) return 0.0;
      preg_match_all('/\d+/', (string) $priceStr, $matches);
      return $matches[0] ? (float) implode('', $matches[0]) : 0.0;
  };

  $extractMileage = function($mileageStr) {
      if (is_numeric($mileageStr)) return (float) $mileageStr;
      if (empty($mileageStr) || is_null($mileageStr)) return 0.0;
      preg_match_all('/\d+/', (string) $mileageStr, $matches);
      return $matches[0] ? (float) implode('', $matches[0]) : 0.0;
  };

  $resizeImg = function($url, $w) {
      if (!is_string($url) || $url === '') return $url;
      // Only resize local storage files like /storage/...
      if (preg_match('/^\/storage\//', $url) === 1) {
          try { return route('img.resize', ['w' => (int)$w]) . '?p=' . urlencode($url); } catch (\Throwable $e) { return $url; }
      }
      return $url;
  };

  $title = $baseTitle;
  $displayPrice = $vehicle->has_active_offer ? $vehicle->offer_price : $vehicle->price;
  $originalPrice = $vehicle->has_active_offer ? $vehicle->price : null;
  // Calcul precio de mercado (marketing): de obicei ~€1500 mai scump decât al nostru,
  // cu o șansă de 3% să fie mai ieftin decât al nostru
  $basePriceNum = (float) $extractPrice($displayPrice);
  $chance = random_int(1, 100);
  if ($chance <= 3) {
    $marketDelta = random_int(200, 800);
    $marketPrice = max(0, $basePriceNum - $marketDelta);
    $isBetterDeal = false; // prețul nostru > piață (rar)
  } else {
    $marketDelta = random_int(1200, 1800);
    $marketPrice = $basePriceNum + $marketDelta;
    $isBetterDeal = true; // prețul nostru < piață (uzual)
  }
@endphp

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/vehicle.css') }}">
  <style>
    .gallery-lightbox { position: fixed; inset: 0; z-index: 2000; display: none; align-items: center; justify-content: center; background: rgba(0,0,0,0.92); }
    .gallery-lightbox.show { display: flex; }
    .gallery-lightbox .lightbox-inner { max-width: 95vw; max-height: 90vh; position: relative; }
    .gallery-lightbox .lightbox-media img,
    .gallery-lightbox .lightbox-media iframe { max-width: 95vw; max-height: 90vh; width: auto; height: auto; object-fit: contain; display: block; background: #000; }
    .gallery-lightbox .lightbox-close { position: absolute; top: 12px; right: 12px; z-index: 1; }
    .gallery-lightbox .lightbox-prev,
    .gallery-lightbox .lightbox-next { position: absolute; top: 50%; transform: translateY(-50%); }
    .gallery-lightbox .lightbox-prev { left: 12px; }
    .gallery-lightbox .lightbox-next { right: 12px; }
  </style>
@endpush
@section('content')
<section class="pt-5 vehicle-page">
  <div class="container py-4">
    <div class="row g-4 align-items-start mb-3">
      <div class="col-lg-8 vehicle-gallery">
        <nav class="small text-secondary mb-2" aria-label="breadcrumb">
          <a href="{{ route('home') }}" class="text-decoration-none">Inicio</a>
          <span class="mx-1">›</span>
          <a href="{{ route('catalog') }}" class="text-decoration-none">Catálogo</a>
          <span class="mx-1">›</span>
          <a href="{{ route('catalog') }}?brand={{ $vehicle->brand }}" class="text-decoration-none">{{ $vehicle->brand }}</a>
          <span class="mx-1">›</span>
          <span class="text-dark">{{ $vehicle->model }}</span>
        </nav>
        <h1 class="h3 fw-bold mb-2">{{ $title }}</h1>
        <div class="d-flex align-items-center gap-3 flex-wrap price-header">
          @if($vehicle->has_active_offer)
            @php
              $baseOriginal = $vehicle->original_price ?: $vehicle->price;
            @endphp
            <div class="price-card bg-danger-subtle border-0 p-3 rounded-4 d-flex align-items-center gap-4" style="backdrop-filter: blur(0)">
              <div>
                <div class="text-muted small mb-1">Precio original</div>
                <div class="h4 text-muted text-decoration-line-through mb-0">€ {{ number_format($extractPrice($baseOriginal)) }}</div>
              </div>
              <div>
                <div class="text-danger small mb-1">Precio de oferta</div>
                <div class="display-5 text-danger fw-bold mb-0">€ {{ number_format($extractPrice($vehicle->offer_price)) }}</div>
              </div>
              @if($vehicle->discount_percentage)
                <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">{{ $vehicle->discount_percentage }}% descuento</span>
              @endif
              @if($vehicle->offer_expires_at)
                <small class="text-danger">Expira {{ \Carbon\Carbon::parse($vehicle->offer_expires_at)->format('d.m.Y') }}</small>
              @endif
            </div>
          @else
            <div class="price-card">
              <div class="price-label">Precio</div>
              <div class="price-value">€ {{ number_format($extractPrice($displayPrice)) }}</div>
              <div class="price-estimate">desde ≈ € {{ number_format(max(1, round(($extractPrice($displayPrice) - 5000) / max(12, 60)))) }}/mes</div>
            </div>
          @endif

          @if($vehicle->featured)
            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-star-fill"></i> Recomendado</span>
          @endif

          @foreach($vehicle->badges ?? [] as $badge)
            <span class="badge bg-info text-dark fs-6">{{ $badge }}</span>
          @endforeach

          <span class="badge financing-badge"><i class="bi bi-piggy-bank me-1"></i>Financiación disponible</span>
        </div>
      </div>
      <div class="col-lg-4 d-grid gap-2 gap-lg-2">
        <button type="button" class="btn btn-outline-primary btn-lg" id="saveVehicleBtn"
                data-vehicle-id="{{ $vehicle->id }}"
                data-vehicle-title="{{ $title }}"
                data-vehicle-slug="{{ $vehicle->slug }}"
                data-vehicle-price="{{ $vehicle->has_offer && $vehicle->offer_price ? $vehicle->offer_price : $vehicle->price }}"
                data-vehicle-image="{{ $vehicle->cover_image ?? '' }}">
          <i class="bi bi-bookmark-heart me-2"></i><span id="saveVehicleText">Guardar coche</span>
        </button>
        <a href="tel:+40123456789" class="btn btn-primary btn-lg"><i class="bi bi-telephone me-2"></i>Llamar ahora</a>
        <a href="https://wa.me/40123456789" class="btn btn-outline-success btn-lg"><i class="bi bi-whatsapp me-2"></i>WhatsApp</a>
        <a href="#testdrive" class="btn btn-outline-primary btn-lg"><i class="bi bi-calendar2-check me-2"></i>Programar prueba de manejo</a>

      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        @if($vehicle->cover_image || !empty($vehicle->gallery_images) || $vehicle->video_url)
          @php
            $allMedia = [];

            // Adaugă video-ul primul dacă există (normalizează la embed YouTube)
            if($vehicle->video_url) {
              $videoUrl = trim((string) $vehicle->video_url);
              $host = (string) (parse_url($videoUrl, PHP_URL_HOST) ?? '');
              $path = trim((string) (parse_url($videoUrl, PHP_URL_PATH) ?? ''), '/');
              $queryStr = (string) (parse_url($videoUrl, PHP_URL_QUERY) ?? '');
              parse_str($queryStr, $queryParams);
              $videoId = null;
              if (!empty($queryParams['v'])) {
                $videoId = $queryParams['v'];
              } elseif (stripos($host, 'youtu.be') !== false && $path) {
                $segments = explode('/', $path);
                $videoId = $segments[0] ?? null;
              } elseif (stripos($path, 'embed/') !== false) {
                $segments = explode('/', $path);
                $idx = array_search('embed', $segments);
                if ($idx !== false && !empty($segments[$idx+1])) { $videoId = $segments[$idx+1]; }
              } elseif (stripos($path, 'shorts/') !== false) {
                $segments = explode('/', $path);
                $idx = array_search('shorts', $segments);
                if ($idx !== false && !empty($segments[$idx+1])) { $videoId = $segments[$idx+1]; }
              }
              $embedUrl = $videoId ? ('https://www.youtube-nocookie.com/embed/' . $videoId . '?rel=0&modestbranding=1&iv_load_policy=3&playsinline=1&showinfo=0') : $videoUrl;
              $videoThumb = $videoId ? ('https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg') : 'https://via.placeholder.com/300x200/f8f9fa/6c757d?text=Video';
              $allMedia[] = [
                'type' => 'video',
                'url' => $videoUrl,
                'embed' => $embedUrl,
                'thumbnail' => $videoThumb,
              ];
            }

            // Adaugă imaginea de copertă
            if($vehicle->cover_image) {
              $allMedia[] = [
                'type' => 'image',
                'url' => $vehicle->cover_image,
                'display' => $resizeImg($vehicle->cover_image, 1200),
                'thumb' => $resizeImg($vehicle->cover_image, 240)
              ];
            }

            // Adaugă imaginile din galerie
            if($vehicle->gallery_images) {
              foreach($vehicle->gallery_images as $img) {
                $allMedia[] = [
                  'type' => 'image',
                  'url' => $img,
                  'display' => $resizeImg($img, 1200),
                  'thumb' => $resizeImg($img, 240)
                ];
              }
            }

            // Adaugă imaginile din câmpul `images` (Sell Your Car)
            if(!empty($vehicle->images)) {
              $imagesField = is_string($vehicle->images) ? json_decode($vehicle->images, true) : $vehicle->images;
              if (is_array($imagesField)) {
                foreach($imagesField as $img) {
                  $isAbsolute = is_string($img) && preg_match('/^https?:\/\//i', $img);
                  $imgUrl = $isAbsolute ? $img : (is_string($img) ? Storage::url($img) : null);
                  if ($imgUrl) {
                    $allMedia[] = [
                      'type' => 'image',
                      'url' => $imgUrl,
                      'display' => $resizeImg($imgUrl, 1200),
                      'thumb' => $resizeImg($imgUrl, 240)
                    ];
                  }
                }
              }
            }

            $mainIndex = 0;
            $mainMedia = $allMedia[$mainIndex] ?? null;
            // Include toate item-urile în thumbnails (inclusiv primul) pentru a permite revenirea la video
            // Pe mobil vom afișa lista orizontală scrollabilă, fără limită la 6
            $thumbnails = $allMedia;
          @endphp

          @isset($mainMedia)
            <div class="vehicle-gallery">
              <!-- Imaginea/Video-ul principal -->
              <div class="main-media-container mb-3 position-relative">
                <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm" id="main-media-click">
                  @if($mainMedia['type'] === 'video')
                    <iframe src="{{ $mainMedia['embed'] ?? $mainMedia['url'] }}"
                            class="w-100 h-100"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                            title="Video prezentare vehicul">
                    </iframe>
                  @else
                    <img src="{{ $mainMedia['display'] ?? $mainMedia['url'] }}"
                         class="w-100 h-100 object-fit-contain"
                         alt="{{ $title }}"
                         id="main-gallery-image"
                         onerror="this.src='https://via.placeholder.com/800x450/f8f9fa/6c757d?text=Fără+imagine'" />
                  @endif
                </div>
              <!-- Lightbox overlay -->
              <div class="gallery-lightbox" id="gallery-lightbox" role="dialog" aria-modal="true" aria-label="Vizualizare imagine pe tot ecranul">
                <div class="lightbox-inner">
                  <button type="button" class="btn btn-light btn-sm lightbox-close" id="lightbox-close" aria-label="Închide">
                    <i class="bi bi-x-lg"></i>
                  </button>
                  <button type="button" class="btn btn-light rounded-circle lightbox-prev" id="lightbox-prev" aria-label="Anterior">
                    <i class="bi bi-chevron-left"></i>
                  </button>
                  <div class="lightbox-media" id="lightbox-media"></div>
                  <button type="button" class="btn btn-light rounded-circle lightbox-next" id="lightbox-next" aria-label="Următor">
                    <i class="bi bi-chevron-right"></i>
                  </button>
                </div>
              </div>
                @if(count($allMedia) > 1)
                  <button type="button" class="btn btn-light rounded-circle shadow position-absolute top-50 start-0 translate-middle-y ms-2 gallery-prev" aria-label="Anterior">
                    <i class="bi bi-chevron-left"></i>
                  </button>
                  <button type="button" class="btn btn-light rounded-circle shadow position-absolute top-50 end-0 translate-middle-y me-2 gallery-next" aria-label="Siguiente">
                    <i class="bi bi-chevron-right"></i>
                  </button>
                @endif
              </div>

              <!-- Thumbnails -->
              @if(!empty($thumbnails) && count($thumbnails) > 0)
                <div class="thumbnails-container">
                  <!-- Mobil: listă orizontală scrollabilă; Desktop: grilă -->
                  <div class="row g-2 flex-nowrap overflow-auto px-1 px-md-0">
                    @foreach($thumbnails as $index => $media)
                    <div class="col-auto col-md-3">
                      <div class="thumbnail-item {{ $media['type'] === 'video' ? 'video-thumbnail' : 'image-thumbnail' }}
                                   {{ $index === $mainIndex ? 'active' : '' }}"
                           data-type="{{ $media['type'] }}"
                           data-url="{{ $media['url'] }}"
                           @if($media['type'] === 'video') data-embed="{{ $media['embed'] ?? '' }}" @endif
                           data-thumbnail="{{ $media['thumbnail'] ?? $media['url'] }}">
                        <div class="ratio ratio-16x9 rounded overflow-hidden" style="width: 96px;" >
                          @if($media['type'] === 'video')
                            <img src="{{ $media['thumbnail'] ?? 'https://via.placeholder.com/300x200/f8f9fa/6c757d?text=Video' }}"
                                 class="w-100 h-100 object-fit-contain"
                                 alt="Video thumbnail"
                                 loading="lazy"
                                 onerror="this.src='https://via.placeholder.com/300x200/f8f9fa/6c757d?text=Video'" />
                            <div class="video-play-overlay">
                              <i class="bi bi-play-circle-fill"></i>
                            </div>
                          @else
                            <img src="{{ $media['thumb'] ?? $media['url'] }}"
                                 class="w-100 h-100 object-fit-contain"
                                 alt="{{ $title }}"
                                 loading="lazy"
                                 onerror="this.src='https://via.placeholder.com/300x200/f8f9fa/6c757d?text=Fără+imagine'" />
                          @endif
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>
              @endif
            </div>
          @else
            <div class="ratio ratio-16x9 rounded overflow-hidden bg-light d-flex align-items-center justify-content-center">
              <div class="text-center text-muted">
                <i class="bi bi-car-front" style="font-size: 4rem;"></i>
                <p class="mt-2">Sin imágenes disponibles</p>
              </div>
            </div>
          @endisset
        @else
          <div class="ratio ratio-16x9 rounded overflow-hidden bg-light d-flex align-items-center justify-content-center">
            <div class="text-center text-muted">
              <i class="bi bi-car-front" style="font-size: 4rem;"></i>
              <p class="mt-2">Sin imágenes disponibles</p>
            </div>
          </div>
        @endif
      </div>
      <div class="col-lg-4 vehicle-specs">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Especificaciones rápidas</h5>
            <ul class="list-unstyled small mb-0">
              <li class="mb-2"><strong>Año:</strong> {{ $vehicle->year ?? 'N/A' }}</li>
              <li class="mb-2"><strong>Kilometraje:</strong> {{ $vehicle->mileage ? number_format($extractMileage($vehicle->mileage)) . ' km' : 'N/A' }}</li>
              <li class="mb-2"><strong>Combustible:</strong> {{ $vehicle->fuel ?? 'N/A' }}</li>
              <li class="mb-2"><strong>Transmisión:</strong> {{ $vehicle->transmission ?? 'N/A' }}</li>
              @if($vehicle->power)
                <li class="mb-2"><strong>Potencia:</strong> {{ $vehicle->power }}</li>
              @endif
              @if($vehicle->drivetrain)
                <li class="mb-2"><strong>Tracción:</strong> {{ $vehicle->drivetrain }}</li>
              @endif
              @if($vehicle->engine)
                <li class="mb-2"><strong>Motor:</strong> {{ $vehicle->engine }}</li>
              @endif
              @if($vehicle->color)
                <li class="mb-2"><strong>Color:</strong> {{ $vehicle->color }}</li>
              @endif
              @if($vehicle->body_type)
                <li class="mb-2"><strong>Carrocería:</strong> {{ $vehicle->body_type }}</li>
              @endif
            </ul>
          </div>
        </div>

      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <h4>Descripción</h4>
        <p class="text-secondary">{{ $vehicle->description ?? 'Descripción no disponible para este vehículo.' }}</p>

        <h4 class="mt-4">Especificaciones técnicas</h4>
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <tbody>
              <tr><th class="w-25">Marca</th><td>{{ $vehicle->brand ?? 'N/A' }}</td><th>Transmisión</th><td>{{ $vehicle->transmission ?? 'N/A' }}</td></tr>
              <tr><th>Modelo</th><td>{{ $vehicle->model ?? 'N/A' }}</td><th>Motor</th><td>{{ $vehicle->engine ?? 'N/A' }}</td></tr>
              <tr><th>Año</th><td>{{ $vehicle->year ?? 'N/A' }}</td><th>Color</th><td>{{ $vehicle->color ?? 'N/A' }}</td></tr>
              <tr><th>Kilometraje</th><td>{{ $vehicle->mileage ? number_format($extractMileage($vehicle->mileage)) . ' km' : 'N/A' }}</td><th>Estado</th><td>{{ $vehicle->condition ?? 'N/A' }}</td></tr>
              <tr><th>Combustible</th><td>{{ $vehicle->fuel ?? 'N/A' }}</td><th>Etiqueta medioambiental</th><td>@if($vehicle->vin)<code>{{ substr($vehicle->vin,0,6) }}••••••</code>@else N/A @endif</td></tr>
              @if($vehicle->power || $vehicle->drivetrain)
                @if($vehicle->power && $vehicle->drivetrain)
                  <tr>
                    <th>Potencia</th><td>{{ $vehicle->power }}</td>
                    <th>Tracción</th><td>{{ $vehicle->drivetrain }}</td>
                  </tr>
                @elseif($vehicle->power)
                  <tr>
                    <th>Potencia</th><td colspan="3">{{ $vehicle->power }}</td>
                  </tr>
                @else
                  <tr>
                    <th>Tracción</th><td colspan="3">{{ $vehicle->drivetrain }}</td>
                  </tr>
                @endif
              @endif
              @if($vehicle->body_type || $vehicle->location)
                @if($vehicle->body_type && $vehicle->location)
                  <tr>
                    <th>Carrocería</th><td>{{ $vehicle->body_type }}</td>
                    <th>Ubicación</th><td>{{ $vehicle->location }}</td>
                  </tr>
                @elseif($vehicle->body_type)
                  <tr>
                    <th>Carrocería</th><td colspan="3">{{ $vehicle->body_type }}</td>
                  </tr>
                @else
                  <tr>
                    <th>Ubicación</th><td colspan="3">{{ $vehicle->location }}</td>
                  </tr>
                @endif
              @endif
            </tbody>
          </table>
        </div>

        <!-- Secțiunea Prețuri și oferte -->
        <div class="card shadow-sm mt-4">
          <div class="card-body">
            <h4 class="card-title mb-3">
              <i class="bi bi-tag-fill text-primary me-2"></i>
              Precios y ofertas
            </h4>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <div>
                    <small class="text-muted d-block">Precio actual</small>
                    <span class="h4 fw-bold text-primary mb-0">€ {{ number_format($extractPrice($vehicle->price)) }}</span>
                  </div>
                  <i class="bi bi-currency-euro text-primary" style="font-size: 2rem;"></i>
                </div>
              </div>

              @if($vehicle->has_active_offer)
                <div class="col-md-6">
                  <div class="d-flex align-items-center justify-content-between p-3 bg-danger bg-opacity-10 rounded border border-danger">
                    <div>
                      <small class="text-muted d-block">Precio de oferta</small>
                      <span class="h4 fw-bold text-danger mb-0">€ {{ number_format($extractPrice($vehicle->offer_price)) }}</span>
                      @if($vehicle->discount_percentage)
                        <small class="text-danger d-block">Descuento {{ $vehicle->discount_percentage }}%</small>
                      @endif
                    </div>
                    <i class="bi bi-tag-fill text-danger" style="font-size: 2rem;"></i>
                  </div>
                </div>

                @if($vehicle->offer_expires_at)
                  <div class="col-12">
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                      <i class="bi bi-clock me-2"></i>
                      <div>
                        <strong>¡Oferta limitada!</strong> Esta oferta expira el {{ \Carbon\Carbon::parse($vehicle->offer_expires_at)->format('d.m.Y') }}
                      </div>
                    </div>
                  </div>
                @endif
              @endif

              @if($vehicle->original_price && $vehicle->original_price != $vehicle->price)
                <div class="col-md-6">
                  <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded">
                    <div>
                      <small class="text-muted d-block">Precio original</small>
                      <span class="h5 text-decoration-line-through text-muted mb-0">€ {{ number_format($extractPrice($vehicle->original_price)) }}</span>
                    </div>
                    <i class="bi bi-arrow-down-circle text-secondary" style="font-size: 2rem;"></i>
                  </div>
                </div>
              @endif
            </div>

            @if($vehicle->has_active_offer)
              <div class="mt-3">
                <div class="progress" style="height: 8px;">
                  @php
                    $discountPercent = $vehicle->discount_percentage ?? 0;
                    $progressPercent = min($discountPercent, 100);
                  @endphp
                  <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $progressPercent }}%"
                       aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                @php
                  $savings = $extractPrice($vehicle->price ?? 0) - $extractPrice($vehicle->offer_price ?? 0);
                @endphp
                <small class="text-muted">Ahorra € {{ number_format($savings) }}</small>
              </div>
            @endif
          </div>
        </div>

        @if(!empty($vehicle->features))
          <h4 class="mt-4">Equipamiento</h4>
          <div class="row g-2">
            @foreach($vehicle->features as $feature)
              <div class="col-6 col-md-4"><span class="badge bg-light text-dark border">✓ {{ $feature }}</span></div>
            @endforeach
          </div>
        @endif

        @if(!empty($vehicle->tags))
          <h4 class="mt-4">Etiquetas</h4>
          <div class="d-flex flex-wrap gap-1">
            @foreach($vehicle->tags as $tag)
              <span class="badge bg-secondary">{{ $tag }}</span>
            @endforeach
          </div>
        @endif

      </div>
      <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
          <div class="card-body">
            <h5 class="card-title">Solicitar oferta / prueba de manejo</h5>
            <form method="POST" action="{{ route('inquiries.store') }}">
              @csrf
              <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
              <input type="hidden" name="vehicle_slug" value="{{ $vehicle->slug }}">
              <input type="hidden" name="vehicle_title" value="{{ $title }}">
              <input type="hidden" name="vehicle_link" value="{{ route('vehicle.show', $vehicle->slug) }}">
              <div class="mb-2"><input type="text" class="form-control" name="name" placeholder="Nombre" required></div>
              <div class="mb-2"><input type="tel" class="form-control" name="phone" placeholder="Teléfono" required></div>
              <div class="mb-2"><textarea class="form-control" name="message" rows="3" placeholder="Mensaje"></textarea></div>
              <button class="btn btn-primary w-100" type="submit">Enviar</button>
            </form>
            @if(session('status'))
              <div class="alert alert-success mt-2">{{ session('status') }}</div>
            @endif
          </div>
        </div>
        <div class="card shadow-sm">
          <div class="card-body">
            <h6 class="mb-2">Calculadora de financiación (estimativa)</h6>
            <div class="row g-2 align-items-end">
              <div class="col-6">
                <label class="form-label small">Entrada (€)</label>
                <input type="number" class="form-control" value="5000" min="0">
              </div>
              <div class="col-6">
                <label class="form-label small">Meses</label>
                <input type="number" class="form-control" value="60" min="12" step="6">
              </div>
              <div class="col-12"><button class="btn btn-outline-primary w-100" type="button">Obtener oferta</button></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    @if($similarVehicles && $similarVehicles->count() > 0)
      <div class="mt-5">
        <h4 class="mb-4">
          <i class="bi bi-graph-up text-primary me-2"></i>
          Vehículos similares para comparar
        </h4>

        <!-- Comparație prețuri (marketing) -->
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <h6 class="card-title text-muted mb-3">Comparație prețuri</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between p-3 bg-primary bg-opacity-10 rounded border border-primary">
                  <div>
                    <small class="text-muted d-block">Tu precio</small>
                    <span class="h5 fw-bold text-primary mb-0">€ {{ number_format($extractPrice($displayPrice)) }}</span>
                  </div>
                  <i class="bi bi-car-front text-primary" style="font-size: 2rem;"></i>
                </div>
              </div>

              <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between p-3 bg-info bg-opacity-10 rounded border border-info">
                  <div>
                    <small class="text-muted d-block">Media del mercado (estimativa)</small>
                    <span class="h5 fw-bold {{ $isBetterDeal ? 'text-info' : 'text-danger' }} mb-0">€ {{ number_format($marketPrice) }}</span>
                    @if($isBetterDeal)
                      <small class="text-success d-block">
                        <i class="bi bi-arrow-down"></i> Ahorro estimado de € {{ number_format(max(0, $marketPrice - $extractPrice($displayPrice))) }}
                      </small>
                    @else
                      <small class="text-danger d-block">
                        <i class="bi bi-exclamation-triangle"></i> Oferta limitada, verifica disponibilidad
                      </small>
                    @endif
                  </div>
                  <i class="bi bi-graph-up text-info" style="font-size: 2rem;"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-4">
          @foreach($similarVehicles as $similar)
          <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm hover-shadow">
              @if($similar->cover_image)
                <img src="{{ $similar->cover_image }}" class="card-img-top" alt="{{ $similar->title ?? ($similar->brand . ' ' . $similar->model) }}"
                     style="height: 200px; object-fit: contain; background-color: #f8f9fa;"
                     onerror="this.src='https://via.placeholder.com/400x200/f8f9fa/6c757d?text=Fără+imagine'" />
              @else
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                  <i class="bi bi-car-front text-muted" style="font-size: 2rem;"></i>
                </div>
              @endif
              <div class="card-body d-flex flex-column">
                @if($similar->featured)
                  <div class="mb-1">
                    <span class="badge bg-warning text-dark">
                      <i class="bi bi-star-fill"></i> Recomandat
                    </span>
                  </div>
                @endif

                <h6 class="card-title mb-1">{{ $similar->title ?? ($similar->brand . ' ' . $similar->model . ' ' . $similar->year) }}</h6>
                <div class="small text-secondary mb-2">
                  {{ $similar->fuel ?? 'N/A' }} • {{ $similar->mileage ? number_format($extractMileage($similar->mileage)) . ' km' : 'N/A' }} • {{ $similar->transmission ?? 'N/A' }}
                </div>

                <!-- Comparație preț cu vehiculul principal -->
                <div class="mb-2">
                  @php
                    $similarPrice = $extractPrice($similar->price ?? 0);
                    $mainPrice = $extractPrice($displayPrice ?? 0);
                    $priceDiff = $similarPrice - $mainPrice;
                    $priceDiffPercent = $mainPrice > 0 ? ($priceDiff / $mainPrice) * 100 : 0;
                  @endphp

                  @if($priceDiff > 0)
                    <small class="text-danger">
                      <i class="bi bi-arrow-up"></i> +€{{ number_format($priceDiff) }} (+{{ number_format($priceDiffPercent, 1) }}%)
                    </small>
                  @elseif($priceDiff < 0)
                    <small class="text-success">
                      <i class="bi bi-arrow-down"></i> -€{{ number_format(abs($priceDiff)) }} ({{ number_format(abs($priceDiffPercent), 1) }}%)
                    </small>
                  @else
                    <small class="text-muted">Preț similar</small>
                  @endif
                </div>

                <div class="mt-auto d-flex justify-content-between align-items-center">
                  @if($similar->has_active_offer)
                    <div>
                      <div class="small text-decoration-line-through text-muted">€ {{ number_format($extractPrice($similar->price)) }}</div>
                      <span class="fw-bold text-danger">€ {{ number_format($extractPrice($similar->offer_price)) }}</span>
                    </div>
                  @else
                    <span class="fw-bold text-primary">€ {{ number_format($extractPrice($similar->price)) }}</span>
                  @endif
                  <a href="{{ route('vehicle.show', $similar->slug) }}" class="btn btn-sm btn-primary">Detalles</a>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const mainMediaContainer = document.querySelector('.main-media-container .ratio');
  const prevBtn = document.querySelector('.main-media-container .gallery-prev');
  const nextBtn = document.querySelector('.main-media-container .gallery-next');
  const thumbnailItems = document.querySelectorAll('.thumbnail-item');
  const lightbox = document.getElementById('gallery-lightbox');
  const lightboxMedia = document.getElementById('lightbox-media');
  const lightboxPrev = document.getElementById('lightbox-prev');
  const lightboxNext = document.getElementById('lightbox-next');
  const lightboxClose = document.getElementById('lightbox-close');
  const mainMediaClick = document.getElementById('main-media-click');

  const mediaItems = {!! json_encode($allMedia, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!};
  let currentIndex = 0;

  function renderMedia(index) {
    if (!mediaItems || !mediaItems.length) return;
    const item = mediaItems[index];
    if (!item) return;
    if (item.type === 'video') {
      const embedUrl = item.embed || item.url;
      mainMediaContainer.innerHTML = `
        <iframe src="${embedUrl}" class="w-100 h-100" frameborder="0" allowfullscreen title="Video prezentare vehicul"></iframe>
      `;
    } else {
      mainMediaContainer.innerHTML = `
        <img src="${item.url}" class="w-100 h-100 object-fit-contain" alt="Gallery image" id="main-gallery-image" onerror="this.src='https://via.placeholder.com/800x450/f8f9fa/6c757d?text=Fără+imagine'" />
      `;
    }
    // Update active thumbnail if visible
    thumbnailItems.forEach(el => el.classList.remove('active'));
    const thumb = document.querySelector(`.thumbnail-item[data-url="${CSS.escape(item.url)}"]`);
    if (thumb) thumb.classList.add('active');
  }

  function setCurrent(index) {
    const total = mediaItems.length;
    if (!total) return;
    currentIndex = ((index % total) + total) % total; // wrap around
    renderMedia(currentIndex);
  }

  // Thumbnail clicks
  thumbnailItems.forEach(function(item, idx) {
    item.addEventListener('click', function() {
      const url = this.dataset.url;
      const index = mediaItems.findIndex(m => m.url === url);
      setCurrent(index >= 0 ? index : idx);
    });
  });

  // Prev/Next buttons
  if (prevBtn) prevBtn.addEventListener('click', function(){ setCurrent(currentIndex - 1); });
  if (nextBtn) nextBtn.addEventListener('click', function(){ setCurrent(currentIndex + 1); });

  // Lightbox helpers
  function renderLightbox(index) {
    if (!mediaItems || !mediaItems.length) return;
    const item = mediaItems[index];
    if (!item) return;
    if (item.type === 'video') {
      const embedUrl = item.embed || item.url;
      lightboxMedia.innerHTML = `<iframe src="${embedUrl}" frameborder="0" allowfullscreen title="Video prezentare vehicul" style="width:95vw; height:calc(95vw*9/16); max-height:90vh;"></iframe>`;
    } else {
      const displayUrl = item.display || item.url;
      lightboxMedia.innerHTML = `<img src="${displayUrl}" alt="Imagine" style="max-width:95vw; max-height:90vh; object-fit:contain;" />`;
    }
  }
  function openLightbox() {
    if (!lightbox) return;
    renderLightbox(currentIndex);
    lightbox.classList.add('show');
    document.body.style.overflow = 'hidden';
  }
  function closeLightbox() {
    if (!lightbox) return;
    lightbox.classList.remove('show');
    lightboxMedia.innerHTML = '';
    document.body.style.overflow = '';
  }
  if (mainMediaClick) {
    mainMediaClick.addEventListener('click', function(){ openLightbox(); });
  }
  if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
  if (lightboxPrev) lightboxPrev.addEventListener('click', function(){ setCurrent(currentIndex - 1); renderLightbox(currentIndex); });
  if (lightboxNext) lightboxNext.addEventListener('click', function(){ setCurrent(currentIndex + 1); renderLightbox(currentIndex); });
  if (lightbox) {
    lightbox.addEventListener('click', function(e){ if (e.target === lightbox) closeLightbox(); });
    document.addEventListener('keydown', function(e){
      if (!lightbox.classList.contains('show')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') { setCurrent(currentIndex - 1); renderLightbox(currentIndex); }
      if (e.key === 'ArrowRight') { setCurrent(currentIndex + 1); renderLightbox(currentIndex); }
    });
  }

  // Initialize index from active thumbnail if present
  const activeThumb = document.querySelector('.thumbnail-item.active');
  if (activeThumb) {
    const url = activeThumb.dataset.url;
    const idx = mediaItems.findIndex(m => m.url === url);
    if (idx >= 0) currentIndex = idx;
  }
});
</script>
<!-- JSON-LD Vehicle Schema -->
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'Vehicle',
  'name' => $pageTitle,
  'brand' => $vehicle->brand ?? null,
  'model' => $vehicle->model ?? null,
  'vehicleModelDate' => $vehicle->year ?? null,
  'description' => $pageDescription,
  'image' => $ogImage,
  'mileageFromOdometer' => !empty($vehicle->mileage) ? [
    '@type' => 'QuantitativeValue',
    'value' => is_numeric($vehicle->mileage) ? (int)$vehicle->mileage : null,
    'unitCode' => 'KM'
  ] : null,
  'offers' => [
    '@type' => 'Offer',
    'price' => $displayPrice ?? null,
    'priceCurrency' => 'EUR',
    'availability' => ($vehicle->status ?? 'available') === 'sold' ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
    'url' => $canonicalUrl
  ]
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}
</script>
@endpush
@endsection

