@extends('layouts.app')

@section('title', 'FAQ - Întrebări Frecvente - Auto Premium')
@section('description', 'Găsește răspunsuri la întrebările frecvente despre serviciile noastre, procesul de achiziție și vehiculele din catalog.')

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary-800 to-primary-900 text-white py-16">
        <div class="container-custom">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-poppins font-bold mb-4">
                    Întrebări Frecvente
                </h1>
                <p class="text-xl text-primary-100 max-w-2xl mx-auto">
                    Găsește răspunsuri la întrebările frecvente despre serviciile noastre
                </p>
            </div>
        </div>
    </section>

    <!-- FAQ Content -->
    <section class="section-padding bg-white">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto">
                <div class="space-y-6">
                    <!-- FAQ Item -->
                    <div class="card p-6 animate-on-scroll">
                        <button class="faq-toggle w-full text-left flex justify-between items-center" data-target="faq-1">
                            <h3 class="text-lg font-poppins font-semibold text-primary-800">
                                Cum pot programa o vizită la showroom?
                            </h3>
                            <svg class="w-6 h-6 text-primary-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq-1" class="faq-content hidden mt-4">
                            <p class="text-primary-600">
                                Poți programa o vizită la showroom prin mai multe metode: telefon la +40 123 456 789, 
                                email la contact@autopremium.ro, sau completând formularul de contact de pe site. 
                                Programul nostru este Luni-Vineri 09:00-18:00 și Sâmbătă 09:00-16:00.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item -->
                    <div class="card p-6 animate-on-scroll">
                        <button class="faq-toggle w-full text-left flex justify-between items-center" data-target="faq-2">
                            <h3 class="text-lg font-poppins font-semibold text-primary-800">
                                Ce documente sunt necesare pentru achiziția unui vehicul?
                            </h3>
                            <svg class="w-6 h-6 text-primary-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq-2" class="faq-content hidden mt-4">
                            <p class="text-primary-600">
                                Pentru achiziția unui vehicul vei avea nevoie de: buletin de identitate, 
                                certificat fiscal, și în funcție de metoda de plată, documente suplimentare. 
                                Pentru finanțare, vei avea nevoie și de adeverință de la locul de muncă.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item -->
                    <div class="card p-6 animate-on-scroll">
                        <button class="faq-toggle w-full text-left flex justify-between items-center" data-target="faq-3">
                            <h3 class="text-lg font-poppins font-semibold text-primary-800">
                                Oferiți servicii de finanțare?
                            </h3>
                            <svg class="w-6 h-6 text-primary-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq-3" class="faq-content hidden mt-4">
                            <p class="text-primary-600">
                                Da, oferim servicii de finanțare prin partenerii noștri bancari. 
                                Condițiile variază în funcție de vehicul și situația financiară a clientului. 
                                Contactează-ne pentru o consultație personalizată.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item -->
                    <div class="card p-6 animate-on-scroll">
                        <button class="faq-toggle w-full text-left flex justify-between items-center" data-target="faq-4">
                            <h3 class="text-lg font-poppins font-semibold text-primary-800">
                                Toate vehiculele au garanție?
                            </h3>
                            <svg class="w-6 h-6 text-primary-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq-4" class="faq-content hidden mt-4">
                            <p class="text-primary-600">
                                Da, toate vehiculele din catalogul nostru beneficiază de garanție. 
                                Durata și condițiile garanției variază în funcție de vehicul și anul de fabricație. 
                                Detaliile complete sunt disponibile la fiecare vehicul.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item -->
                    <div class="card p-6 animate-on-scroll">
                        <button class="faq-toggle w-full text-left flex justify-between items-center" data-target="faq-5">
                            <h3 class="text-lg font-poppins font-semibold text-primary-800">
                                Pot face un test-drive înainte de achiziție?
                            </h3>
                            <svg class="w-6 h-6 text-primary-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq-5" class="faq-content hidden mt-4">
                            <p class="text-primary-600">
                                Da, oferim posibilitatea de test-drive pentru majoritatea vehiculelor din catalog. 
                                Test-drive-ul se face cu programare și în prezența unui reprezentant al nostru. 
                                Contactează-ne pentru a programa un test-drive.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item -->
                    <div class="card p-6 animate-on-scroll">
                        <button class="faq-toggle w-full text-left flex justify-between items-center" data-target="faq-6">
                            <h3 class="text-lg font-poppins font-semibold text-primary-800">
                                Cum pot verifica istoricul unui vehicul?
                            </h3>
                            <svg class="w-6 h-6 text-primary-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq-6" class="faq-content hidden mt-4">
                            <p class="text-primary-600">
                                Fiecare vehicul din catalogul nostru vine cu istoricul complet verificat. 
                                Poți solicita detalii despre istoricul vehiculului prin contactarea noastră. 
                                Toate vehiculele sunt verificate tehnic înainte de a fi puse în vânzare.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item -->
                    <div class="card p-6 animate-on-scroll">
                        <button class="faq-toggle w-full text-left flex justify-between items-center" data-target="faq-7">
                            <h3 class="text-lg font-poppins font-semibold text-primary-800">
                                Oferiți servicii de transport pentru vehicule?
                            </h3>
                            <svg class="w-6 h-6 text-primary-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq-7" class="faq-content hidden mt-4">
                            <p class="text-primary-600">
                                Da, oferim servicii de transport pentru vehicule în toată România. 
                                Costul transportului variază în funcție de distanță și tipul de vehicul. 
                                Contactează-ne pentru o ofertă personalizată.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item -->
                    <div class="card p-6 animate-on-scroll">
                        <button class="faq-toggle w-full text-left flex justify-between items-center" data-target="faq-8">
                            <h3 class="text-lg font-poppins font-semibold text-primary-800">
                                Ce servicii de mentenanță oferiți?
                            </h3>
                            <svg class="w-6 h-6 text-primary-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq-8" class="faq-content hidden mt-4">
                            <p class="text-primary-600">
                                Oferim servicii complete de mentenanță pentru toate vehiculele achiziționate de la noi: 
                                revizii, înlocuiri de piese, verificări tehnice, și suport tehnic 24/7. 
                                Avem un atelier propriu cu tehnicieni specializați.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <section class="section-padding bg-primary-50">
        <div class="container-custom text-center">
            <div class="animate-on-scroll">
                <h2 class="text-3xl md:text-4xl font-poppins font-bold text-primary-800 mb-6">
                    Nu ai găsit răspunsul?
                </h2>
                <p class="text-lg text-primary-600 mb-8 max-w-2xl mx-auto">
                    Dacă ai întrebări suplimentare, nu ezita să ne contactezi. 
                    Echipa noastră este aici să te ajute.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('contact') }}" class="btn-primary text-lg px-8 py-4">
                        Contactează-ne
                    </a>
                    <a href="{{ route('vehicles.index') }}" class="btn-secondary text-lg px-8 py-4">
                        Vezi Catalogul
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // FAQ Toggle functionality
    const faqToggles = document.querySelectorAll('.faq-toggle');
    
    faqToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const content = document.getElementById(targetId);
            const icon = this.querySelector('svg');
            
            // Toggle content
            content.classList.toggle('hidden');
            
            // Rotate icon
            icon.classList.toggle('rotate-180');
            
            // Close other open FAQs
            faqToggles.forEach(otherToggle => {
                if (otherToggle !== this) {
                    const otherTargetId = otherToggle.dataset.target;
                    const otherContent = document.getElementById(otherTargetId);
                    const otherIcon = otherToggle.querySelector('svg');
                    
                    otherContent.classList.add('hidden');
                    otherIcon.classList.remove('rotate-180');
                }
            });
        });
    });
});
</script>
@endpush 