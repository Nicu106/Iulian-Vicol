@extends('layouts.app')

@section('title', $vehicle->title . ' - Auto Premium')
@section('description', $vehicle->description)

@section('content')
    <!-- Vehicle Header -->
    <section class="bg-gradient-to-br from-primary-800 to-primary-900 text-white py-16">
        <div class="container-custom">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between">
                <div class="mb-6 lg:mb-0">
                    <nav class="text-sm text-primary-200 mb-4">
                        <a href="{{ route('vehicles.index') }}" class="hover:text-white">Catalog</a>
                        <span class="mx-2">/</span>
                        <span>{{ $vehicle->brand }} {{ $vehicle->model }}</span>
                    </nav>
                    <h1 class="text-3xl md:text-4xl font-poppins font-bold mb-2">
                        {{ $vehicle->brand }} {{ $vehicle->model }}
                    </h1>
                    <p class="text-xl text-primary-100">{{ $vehicle->year }} • {{ $vehicle->formatted_mileage }} km</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-poppins font-bold text-accent-400">
                        {{ $vehicle->formatted_price }}
                    </div>
                    <p class="text-primary-200">Preț negociabil</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Vehicle Content -->
    <section class="section-padding bg-white">
        <div class="container-custom">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Image Gallery -->
                    <div class="mb-8">
                        <div class="relative">
                            @if($vehicle->images->count() > 0)
                                <div id="main-image" class="aspect-video bg-primary-100 rounded-xl overflow-hidden mb-4">
                                    <img src="{{ $vehicle->images->first()->image_url }}" 
                                         alt="{{ $vehicle->title }}" 
                                         class="w-full h-full object-cover">
                                </div>
                                
                                @if($vehicle->images->count() > 1)
                                    <div class="grid grid-cols-4 gap-2">
                                        @foreach($vehicle->images->take(4) as $image)
                                            <button class="thumbnail-btn aspect-square bg-primary-100 rounded-lg overflow-hidden hover:opacity-75 transition-opacity" 
                                                    data-image="{{ $image->image_url }}">
                                                <img src="{{ $image->image_url }}" 
                                                     alt="{{ $vehicle->title }}" 
                                                     class="w-full h-full object-cover">
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <div class="aspect-video bg-primary-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-24 h-24 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Vehicle Description -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-4">Descriere</h2>
                        <div class="prose prose-lg text-primary-600">
                            {!! nl2br(e($vehicle->description)) !!}
                        </div>
                    </div>

                    <!-- Specifications -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-6">Specificații Tehnice</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="flex justify-between py-3 border-b border-primary-100">
                                    <span class="font-medium text-primary-700">Marcă</span>
                                    <span class="text-primary-600">{{ $vehicle->brand }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-primary-100">
                                    <span class="font-medium text-primary-700">Model</span>
                                    <span class="text-primary-600">{{ $vehicle->model }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-primary-100">
                                    <span class="font-medium text-primary-700">An</span>
                                    <span class="text-primary-600">{{ $vehicle->year }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-primary-100">
                                    <span class="font-medium text-primary-700">Kilometri</span>
                                    <span class="text-primary-600">{{ $vehicle->formatted_mileage }} km</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-primary-100">
                                    <span class="font-medium text-primary-700">Combustibil</span>
                                    <span class="text-primary-600">{{ $vehicle->fuel_type }}</span>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="flex justify-between py-3 border-b border-primary-100">
                                    <span class="font-medium text-primary-700">Transmisie</span>
                                    <span class="text-primary-600">{{ $vehicle->transmission ?? 'Manual' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-primary-100">
                                    <span class="font-medium text-primary-700">Motor</span>
                                    <span class="text-primary-600">{{ $vehicle->engine_size }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-primary-100">
                                    <span class="font-medium text-primary-700">Culoare</span>
                                    <span class="text-primary-600">{{ $vehicle->color }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-primary-100">
                                    <span class="font-medium text-primary-700">Stare</span>
                                    <span class="text-primary-600">{{ $vehicle->condition }}</span>
                                </div>
                                @if($vehicle->vin)
                                <div class="flex justify-between py-3 border-b border-primary-100">
                                    <span class="font-medium text-primary-700">VIN</span>
                                    <span class="text-primary-600 font-mono text-sm">{{ $vehicle->vin }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    @if($vehicle->features && count($vehicle->features) > 0)
                    <div class="mb-8">
                        <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-6">Dotări</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($vehicle->features as $feature)
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-primary-600">{{ $feature }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar - Fixed Contact -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24">
                        <!-- Contact Card -->
                        <div class="card p-6 mb-8">
                            <h3 class="text-xl font-poppins font-semibold text-primary-800 mb-4">Interesat de acest vehicul?</h3>
                            <div class="space-y-4">
                                <a href="tel:+40123456789" class="flex items-center space-x-3 text-primary-600 hover:text-primary-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span>+40 123 456 789</span>
                                </a>
                                <a href="mailto:contact@autopremium.ro" class="flex items-center space-x-3 text-primary-600 hover:text-primary-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>contact@autopremium.ro</span>
                                </a>
                            </div>
                            
                            <div class="mt-6 space-y-3">
                                <button class="w-full btn-primary">
                                    Solicită Test-Drive
                                </button>
                                <button class="w-full btn-secondary">
                                    Programează Vizita
                                </button>
                            </div>
                        </div>

                        <!-- Quick Contact Form -->
                        <div class="card p-6">
                            <h3 class="text-xl font-poppins font-semibold text-primary-800 mb-4">Trimite un mesaj</h3>
                            <form action="{{ route('contact.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                                <input type="hidden" name="subject" value="Interesat de {{ $vehicle->brand }} {{ $vehicle->model }}">
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-primary-700 mb-1">Nume</label>
                                        <input type="text" id="name" name="name" required
                                               class="w-full px-3 py-2 border border-primary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-primary-700 mb-1">Email</label>
                                        <input type="email" id="email" name="email" required
                                               class="w-full px-3 py-2 border border-primary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-primary-700 mb-1">Telefon</label>
                                        <input type="tel" id="phone" name="phone"
                                               class="w-full px-3 py-2 border border-primary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    
                                    <div>
                                        <label for="message" class="block text-sm font-medium text-primary-700 mb-1">Mesaj</label>
                                        <textarea id="message" name="message" rows="4" required
                                                  class="w-full px-3 py-2 border border-primary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                  placeholder="Scrie mesajul tău aici..."></textarea>
                                    </div>
                                    
                                    <button type="submit" class="w-full btn-primary">
                                        Trimite Mesajul
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Vehicles -->
    @if($relatedVehicles->count() > 0)
    <section class="section-padding bg-primary-50">
        <div class="container-custom">
            <h2 class="text-3xl font-poppins font-bold text-primary-800 mb-8 text-center">
                Vehicule Similare
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedVehicles as $relatedVehicle)
                    @include('components.vehicle-card-small', ['vehicle' => $relatedVehicle])
                @endforeach
            </div>
        </div>
    </section>
    @endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image gallery functionality
    const mainImage = document.getElementById('main-image');
    const thumbnailBtns = document.querySelectorAll('.thumbnail-btn');
    
    thumbnailBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const imageUrl = this.dataset.image;
            const img = mainImage.querySelector('img');
            img.src = imageUrl;
            
            // Update active thumbnail
            thumbnailBtns.forEach(b => b.classList.remove('ring-2', 'ring-primary-500'));
            this.classList.add('ring-2', 'ring-primary-500');
        });
    });
});
</script>
@endpush 