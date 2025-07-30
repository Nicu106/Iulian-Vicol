@extends('layouts.app')

@section('title', 'Contact - Auto Premium')
@section('description', 'Contactează-ne pentru orice întrebări despre vehiculele noastre sau pentru a programa o vizită.')

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary-800 to-primary-900 text-white py-16">
        <div class="container-custom">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-poppins font-bold mb-4">
                    Contactează-ne
                </h1>
                <p class="text-xl text-primary-100 max-w-2xl mx-auto">
                    Suntem aici să te ajutăm să găsești mașina perfectă. 
                    Contactează-ne pentru orice întrebări.
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="section-padding bg-white">
        <div class="container-custom">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Form -->
                <div class="animate-on-scroll">
                    <h2 class="text-3xl font-poppins font-bold text-primary-800 mb-6">
                        Trimite-ne un Mesaj
                    </h2>
                    
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-primary-700 mb-2">Nume Complet *</label>
                                <input type="text" id="name" name="name" required
                                       value="{{ old('name') }}"
                                       class="w-full px-4 py-3 border border-primary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                @error('name')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-primary-700 mb-2">Email *</label>
                                <input type="email" id="email" name="email" required
                                       value="{{ old('email') }}"
                                       class="w-full px-4 py-3 border border-primary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                @error('email')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-primary-700 mb-2">Telefon</label>
                            <input type="tel" id="phone" name="phone"
                                   value="{{ old('phone') }}"
                                   class="w-full px-4 py-3 border border-primary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('phone')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="subject" class="block text-sm font-medium text-primary-700 mb-2">Subiect *</label>
                            <input type="text" id="subject" name="subject" required
                                   value="{{ old('subject') }}"
                                   class="w-full px-4 py-3 border border-primary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('subject')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-medium text-primary-700 mb-2">Mesaj *</label>
                            <textarea id="message" name="message" rows="6" required
                                      class="w-full px-4 py-3 border border-primary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                      placeholder="Scrie mesajul tău aici...">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <button type="submit" class="btn-primary text-lg px-8 py-4">
                            Trimite Mesajul
                        </button>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="animate-on-scroll">
                    <h2 class="text-3xl font-poppins font-bold text-primary-800 mb-6">
                        Informații de Contact
                    </h2>
                    
                    <div class="space-y-8">
                        <!-- Address -->
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-primary-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-poppins font-semibold text-primary-800 mb-2">Adresa</h3>
                                <p class="text-primary-600">
                                    Strada Exemplu, Nr. 123<br>
                                    Sector 1, București<br>
                                    România
                                </p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-primary-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-poppins font-semibold text-primary-800 mb-2">Telefon</h3>
                                <p class="text-primary-600">
                                    <a href="tel:+40123456789" class="hover:text-primary-800 transition-colors">+40 123 456 789</a><br>
                                    <a href="tel:+40123456788" class="hover:text-primary-800 transition-colors">+40 123 456 788</a>
                                </p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-primary-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-poppins font-semibold text-primary-800 mb-2">Email</h3>
                                <p class="text-primary-600">
                                    <a href="mailto:contact@autopremium.ro" class="hover:text-primary-800 transition-colors">contact@autopremium.ro</a><br>
                                    <a href="mailto:info@autopremium.ro" class="hover:text-primary-800 transition-colors">info@autopremium.ro</a>
                                </p>
                            </div>
                        </div>

                        <!-- Hours -->
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-primary-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-poppins font-semibold text-primary-800 mb-2">Program</h3>
                                <p class="text-primary-600">
                                    Luni - Vineri: 09:00 - 18:00<br>
                                    Sâmbătă: 09:00 - 16:00<br>
                                    Duminică: Închis
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="bg-primary-50">
        <div class="container-custom py-16">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-poppins font-bold text-primary-800 mb-4">
                    Găsește-ne
                </h2>
                <p class="text-lg text-primary-600">
                    Vizitează-ne la showroom pentru a vedea vehiculele în persoană
                </p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-8">
                <div class="aspect-video bg-primary-100 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <svg class="w-16 h-16 text-primary-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"></path>
                        </svg>
                        <p class="text-primary-600">Harta va fi integrată aici</p>
                        <p class="text-sm text-primary-500 mt-2">Google Maps sau OpenStreetMap</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection 