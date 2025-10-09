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
  
  $title = $baseTitle;
  $displayPrice = $vehicle->has_active_offer ? $vehicle->offer_price : $vehicle->price;
  $originalPrice = $vehicle->has_active_offer ? $vehicle->price : null;
@endphp

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/vehicle.css') }}">
@endpush
@section('content')
<section class="pt-5 vehicle-page">
  <div class="container py-4">
    <div class="row g-4 align-items-start mb-3">
      <div class="col-lg-8 vehicle-gallery">
        <nav class="small text-secondary mb-2" aria-label="breadcrumb">
          <a href="{{ route('home') }}" class="text-decoration-none">Acasă</a>
          <span class="mx-1">›</span>
          <a href="{{ route('catalog') }}" class="text-decoration-none">Catalog</a>
          <span class="mx-1">›</span>
          <a href="{{ route('catalog') }}?brand={{ $vehicle->brand }}" class="text-decoration-none">{{ $vehicle->brand }}</a>
          <span class="mx-1">›</span>
          <span class="text-dark">{{ $vehicle->model }}</span>
        </nav>
        <h1 class="h3 fw-bold mb-2">{{ $title }}</h1>
        <div class="d-flex align-items-center gap-3 flex-wrap price-header">
          @if($vehicle->has_active_offer)
            <div class="d-flex align-items-center gap-3">
              <div class="text-center">
                <div class="text-decoration-line-through text-muted h6 mb-0">Preț original</div>
                <span class="text-decoration-line-through text-muted h4">€ {{ number_format($extractPrice($originalPrice)) }}</span>
              </div>
              <div class="text-center">
                <div class="text-danger h6 mb-0">Preț ofertă</div>
                <div class="display-5 text-danger fw-bold">€ {{ number_format($extractPrice($displayPrice)) }}</div>
                @if($vehicle->discount_percentage)
                  <span class="badge bg-danger fs-6">{{ $vehicle->discount_percentage }}% reducere</span>
                @endif
              </div>
              <div class="d-flex flex-column align-items-center">
                <span class="badge bg-danger fs-5 px-3 py-2">OFERTĂ SPECIALĂ</span>
                @if($vehicle->offer_expires_at)
                  <small class="text-danger mt-1">Expiră {{ \Carbon\Carbon::parse($vehicle->offer_expires_at)->format('d.m.Y') }}</small>
                @endif
              </div>
            </div>
          @else
            <div class="price-card">
              <div class="price-label">Preț</div>
              <div class="price-value">€ {{ number_format($extractPrice($displayPrice)) }}</div>
              <div class="price-estimate">de la ≈ € {{ number_format(max(1, round(($extractPrice($displayPrice) - 5000) / max(12, 60)))) }}/lună</div>
            </div>
          @endif
          
          @if($vehicle->featured)
            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-star-fill"></i> Recomandat</span>
          @endif
          
          @foreach($vehicle->badges ?? [] as $badge)
            <span class="badge bg-info text-dark fs-6">{{ $badge }}</span>
          @endforeach
          
          <span class="badge financing-badge"><i class="bi bi-piggy-bank me-1"></i>Finanțare disponibilă</span>
        </div>
      </div>
      <div class="col-lg-4 d-grid gap-2 gap-lg-2">
        <button type="button" class="btn btn-outline-primary btn-lg" id="saveVehicleBtn" 
                data-vehicle-id="{{ $vehicle->id }}" 
                data-vehicle-title="{{ $title }}"
                data-vehicle-slug="{{ $vehicle->slug }}"
                data-vehicle-price="{{ $vehicle->has_offer && $vehicle->offer_price ? $vehicle->offer_price : $vehicle->price }}"
                data-vehicle-image="{{ $vehicle->cover_image ?? '' }}">
          <i class="bi bi-bookmark-heart me-2"></i><span id="saveVehicleText">Salvează mașina</span>
        </button>
        <a href="tel:+40123456789" class="btn btn-primary btn-lg"><i class="bi bi-telephone me-2"></i>Sună acum</a>
        <a href="https://wa.me/40123456789" class="btn btn-outline-success btn-lg"><i class="bi bi-whatsapp me-2"></i>WhatsApp</a>
        <a href="#testdrive" class="btn btn-outline-primary btn-lg"><i class="bi bi-calendar2-check me-2"></i>Programează test drive</a>
        
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
                'url' => $vehicle->cover_image
              ];
            }
            
            // Adaugă imaginile din galerie
            if($vehicle->gallery_images) {
              foreach($vehicle->gallery_images as $img) {
                $allMedia[] = [
                  'type' => 'image',
                  'url' => $img
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
                    ];
                  }
                }
              }
            }
            
            $mainIndex = 0;
            $mainMedia = $allMedia[$mainIndex] ?? null;
            // Include toate item-urile în thumbnails (inclusiv primul) pentru a permite revenirea la video
            $thumbnails = array_slice($allMedia, 0, 6); // Max 6 thumbnails afișate
          @endphp
          
          @if($mainMedia)
            <div class="vehicle-gallery">
              <!-- Imaginea/Video-ul principal -->
              <div class="main-media-container mb-3">
                <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
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
                    <img src="{{ $mainMedia['url'] }}" 
                         class="w-100 h-100 object-fit-cover" 
                         alt="{{ $title }}" 
                         id="main-gallery-image"
                         onerror="this.src='https://via.placeholder.com/800x450/f8f9fa/6c757d?text=Fără+imagine'" />
                  @endif
                </div>
              </div>
              
              <!-- Thumbnails -->
              @if(count($thumbnails) > 0)
                <div class="thumbnails-container">
                  <div class="row g-2">
                    @foreach($thumbnails as $index => $media)
                    <div class="col-4 col-md-3">
                      <div class="thumbnail-item {{ $media['type'] === 'video' ? 'video-thumbnail' : 'image-thumbnail' }} 
                                   {{ $index === $mainIndex ? 'active' : '' }}"
                           data-type="{{ $media['type'] }}"
                           data-url="{{ $media['url'] }}"
                           @if($media['type'] === 'video') data-embed="{{ $media['embed'] ?? '' }}" @endif
                           data-thumbnail="{{ $media['thumbnail'] ?? $media['url'] }}">
                        <div class="ratio ratio-16x9 rounded overflow-hidden">
                          @if($media['type'] === 'video')
                            <img src="{{ $media['thumbnail'] ?? 'https://via.placeholder.com/300x200/f8f9fa/6c757d?text=Video' }}" 
                                 class="w-100 h-100 object-fit-cover" 
                                 alt="Video thumbnail" 
                                 loading="lazy"
                                 onerror="this.src='https://via.placeholder.com/300x200/f8f9fa/6c757d?text=Video'" />
                            <div class="video-play-overlay">
                              <i class="bi bi-play-circle-fill"></i>
                            </div>
                          @else
                            <img src="{{ $media['url'] }}" 
                                 class="w-100 h-100 object-fit-cover" 
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
                <p class="mt-2">Fără imagini disponibile</p>
              </div>
            </div>
          @endif
        @else
          <div class="ratio ratio-16x9 rounded overflow-hidden bg-light d-flex align-items-center justify-content-center">
            <div class="text-center text-muted">
              <i class="bi bi-car-front" style="font-size: 4rem;"></i>
              <p class="mt-2">Fără imagini disponibile</p>
            </div>
          </div>
        @endif
      </div>
      <div class="col-lg-4 vehicle-specs">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Specificații rapide</h5>
            <ul class="list-unstyled small mb-0">
              <li class="mb-2"><strong>An:</strong> {{ $vehicle->year ?? 'N/A' }}</li>
              <li class="mb-2"><strong>Kilometraj:</strong> {{ $vehicle->mileage ? number_format($extractMileage($vehicle->mileage)) . ' km' : 'N/A' }}</li>
              <li class="mb-2"><strong>Combustibil:</strong> {{ $vehicle->fuel ?? 'N/A' }}</li>
              <li class="mb-2"><strong>Transmisie:</strong> {{ $vehicle->transmission ?? 'N/A' }}</li>
              @if($vehicle->power)
                <li class="mb-2"><strong>Putere:</strong> {{ $vehicle->power }}</li>
              @endif
              @if($vehicle->drivetrain)
                <li class="mb-2"><strong>Tracțiune:</strong> {{ $vehicle->drivetrain }}</li>
              @endif
              @if($vehicle->engine)
                <li class="mb-2"><strong>Motor:</strong> {{ $vehicle->engine }}</li>
              @endif
              @if($vehicle->color)
                <li class="mb-2"><strong>Culoare:</strong> {{ $vehicle->color }}</li>
              @endif
              @if($vehicle->body_type)
                <li class="mb-2"><strong>Caroserie:</strong> {{ $vehicle->body_type }}</li>
              @endif
            </ul>
          </div>
        </div>
        
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <h4>Descriere</h4>
        <p class="text-secondary">{{ $vehicle->description ?? 'Descriere nu este disponibilă pentru acest vehicul.' }}</p>

        <h4 class="mt-4">Specificații Tehnice</h4>
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <tbody>
              <tr><th class="w-25">Marcă</th><td>{{ $vehicle->brand ?? 'N/A' }}</td><th>Transmisie</th><td>{{ $vehicle->transmission ?? 'N/A' }}</td></tr>
              <tr><th>Model</th><td>{{ $vehicle->model ?? 'N/A' }}</td><th>Motor</th><td>{{ $vehicle->engine ?? 'N/A' }}</td></tr>
              <tr><th>An</th><td>{{ $vehicle->year ?? 'N/A' }}</td><th>Culoare</th><td>{{ $vehicle->color ?? 'N/A' }}</td></tr>
              <tr><th>Kilometri</th><td>{{ $vehicle->mileage ? number_format($extractMileage($vehicle->mileage)) . ' km' : 'N/A' }}</td><th>Stare</th><td>{{ $vehicle->condition ?? 'N/A' }}</td></tr>
              <tr><th>Combustibil</th><td>{{ $vehicle->fuel ?? 'N/A' }}</td><th>VIN</th><td>@if($vehicle->vin)<code>{{ substr($vehicle->vin,0,6) }}••••••</code>@else N/A @endif</td></tr>
              @if($vehicle->power || $vehicle->drivetrain)
                <tr>
                  @if($vehicle->power)<th>Putere</th><td>{{ $vehicle->power }}</td>@else<th></th><td></td>@endif
                  @if($vehicle->drivetrain)<th>Tracțiune</th><td>{{ $vehicle->drivetrain }}</td>@else<th></th><td></td>@endif
                </tr>
              @endif
              @if($vehicle->body_type || $vehicle->location)
                <tr>
                  @if($vehicle->body_type)<th>Caroserie</th><td>{{ $vehicle->body_type }}</td>@else<th></th><td></td>@endif
                  @if($vehicle->location)<th>Locație</th><td>{{ $vehicle->location }}</td>@else<th></th><td></td>@endif
                </tr>
              @endif
            </tbody>
          </table>
        </div>

        <!-- Secțiunea Prețuri și oferte -->
        <div class="card shadow-sm mt-4">
          <div class="card-body">
            <h4 class="card-title mb-3">
              <i class="bi bi-tag-fill text-primary me-2"></i>
              Prețuri și oferte
            </h4>
            
            <div class="row g-3">
              <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <div>
                    <small class="text-muted d-block">Preț curent</small>
                    <span class="h4 fw-bold text-primary mb-0">€ {{ number_format($extractPrice($vehicle->price)) }}</span>
                  </div>
                  <i class="bi bi-currency-euro text-primary" style="font-size: 2rem;"></i>
                </div>
              </div>
              
              @if($vehicle->has_active_offer)
                <div class="col-md-6">
                  <div class="d-flex align-items-center justify-content-between p-3 bg-danger bg-opacity-10 rounded border border-danger">
                    <div>
                      <small class="text-muted d-block">Preț ofertă</small>
                      <span class="h4 fw-bold text-danger mb-0">€ {{ number_format($extractPrice($vehicle->offer_price)) }}</span>
                      @if($vehicle->discount_percentage)
                        <small class="text-danger d-block">Reducere {{ $vehicle->discount_percentage }}%</small>
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
                        <strong>Ofertă limitată!</strong> Această ofertă expiră la {{ \Carbon\Carbon::parse($vehicle->offer_expires_at)->format('d.m.Y') }}
                      </div>
                    </div>
                  </div>
                @endif
              @endif
              
              @if($vehicle->original_price && $vehicle->original_price != $vehicle->price)
                <div class="col-md-6">
                  <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded">
                    <div>
                      <small class="text-muted d-block">Preț original</small>
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
                <small class="text-muted">Economisești € {{ number_format($savings) }}</small>
              </div>
            @endif
          </div>
        </div>

        @if(!empty($vehicle->features))
          <h4 class="mt-4">Dotări</h4>
          <div class="row g-2">
            @foreach($vehicle->features as $feature)
              <div class="col-6 col-md-4"><span class="badge bg-light text-dark border">✓ {{ $feature }}</span></div>
            @endforeach
          </div>
        @endif

        @if(!empty($vehicle->tags))
          <h4 class="mt-4">Etichete</h4>
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
            <h5 class="card-title">Solicită ofertă / test drive</h5>
            <form method="POST" action="{{ route('inquiries.store') }}">
              @csrf
              <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
              <input type="hidden" name="vehicle_slug" value="{{ $vehicle->slug }}">
              <input type="hidden" name="vehicle_title" value="{{ $title }}">
              <input type="hidden" name="vehicle_link" value="{{ route('vehicle.show', $vehicle->slug) }}">
              <div class="mb-2"><input type="text" class="form-control" name="name" placeholder="Nume" required></div>
              <div class="mb-2"><input type="tel" class="form-control" name="phone" placeholder="Telefon" required></div>
              <div class="mb-2"><textarea class="form-control" name="message" rows="3" placeholder="Mesaj"></textarea></div>
              <button class="btn btn-primary w-100" type="submit">Trimite</button>
            </form>
            @if(session('status'))
              <div class="alert alert-success mt-2">{{ session('status') }}</div>
            @endif
          </div>
        </div>
        <div class="card shadow-sm">
          <div class="card-body">
            <h6 class="mb-2">Calculator finanțare (estimativ)</h6>
            <div class="row g-2 align-items-end">
              <div class="col-6">
                <label class="form-label small">Avans (€)</label>
                <input type="number" class="form-control" value="5000" min="0">
              </div>
              <div class="col-6">
                <label class="form-label small">Luni</label>
                <input type="number" class="form-control" value="60" min="12" step="6">
              </div>
              <div class="col-12"><button class="btn btn-outline-primary w-100" type="button">Află oferta</button></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    @if($similarVehicles && $similarVehicles->count() > 0)
      <div class="mt-5">
        <h4 class="mb-4">
          <i class="bi bi-graph-up text-primary me-2"></i>
          Vehicule similare pentru comparație
        </h4>
        
        <!-- Comparație prețuri -->
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <h6 class="card-title text-muted mb-3">Comparație prețuri</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between p-3 bg-primary bg-opacity-10 rounded border border-primary">
                  <div>
                    <small class="text-muted d-block">Prețul tău</small>
                    <span class="h5 fw-bold text-primary mb-0">€ {{ number_format($extractPrice($displayPrice)) }}</span>
                  </div>
                  <i class="bi bi-car-front text-primary" style="font-size: 2rem;"></i>
                </div>
              </div>
              
              @php
                $avgPrice = $similarVehicles->avg('price');
                $minPrice = $similarVehicles->min('price');
                $maxPrice = $similarVehicles->max('price');
              @endphp
              
              <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between p-3 bg-info bg-opacity-10 rounded border border-info">
                  <div>
                    <small class="text-muted d-block">Media pieței</small>
                    <span class="h5 fw-bold text-info mb-0">€ {{ number_format($extractPrice($avgPrice)) }}</span>
                    <small class="text-muted d-block">€{{ number_format($extractPrice($minPrice)) }} - €{{ number_format($extractPrice($maxPrice)) }}</small>
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
                     style="height: 200px; object-fit: cover;"
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
                  <a href="{{ route('vehicle.show', $similar->slug) }}" class="btn btn-sm btn-primary">Detalii</a>
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
    const thumbnailItems = document.querySelectorAll('.thumbnail-item');
    const mainMediaContainer = document.querySelector('.main-media-container .ratio');
    
    thumbnailItems.forEach(function(item) {
        item.addEventListener('click', function() {
            const mediaType = this.dataset.type;
            const mediaUrl = this.dataset.url;
            const thumbnailUrl = this.dataset.thumbnail;
            
            // Remove active class from all thumbnails
            thumbnailItems.forEach(thumb => thumb.classList.remove('active'));
            // Add active class to clicked thumbnail
            this.classList.add('active');
            
            // Update main media
            if (mediaType === 'video') {
                const embedUrl = this.dataset.embed || mediaUrl;
                mainMediaContainer.innerHTML = `
                    <iframe src="${embedUrl}" 
                            class="w-100 h-100" 
                            frameborder="0" 
                            allowfullscreen
                            title="Video prezentare vehicul">
                    </iframe>
                `;
            } else {
                mainMediaContainer.innerHTML = `
                    <img src="${mediaUrl}" 
                         class="w-100 h-100 object-fit-cover" 
                         alt="Gallery image" 
                         id="main-gallery-image"
                         onerror="this.src='https://via.placeholder.com/800x450/f8f9fa/6c757d?text=Fără+imagine'" />
                `;
            }
        });
    });
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

