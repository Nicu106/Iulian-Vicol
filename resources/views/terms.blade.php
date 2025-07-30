@extends('layouts.app')

@section('title', 'Termeni și Condiții - Auto Premium')
@section('description', 'Termenii și condițiile de utilizare a site-ului Auto Premium și serviciilor noastre.')

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary-800 to-primary-900 text-white py-16">
        <div class="container-custom">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-poppins font-bold mb-4">
                    Termeni și Condiții
                </h1>
                <p class="text-xl text-primary-100 max-w-2xl mx-auto">
                    Termenii și condițiile de utilizare a site-ului și serviciilor noastre
                </p>
            </div>
        </div>
    </section>

    <!-- Terms Content -->
    <section class="section-padding bg-white">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto prose prose-lg">
                <div class="animate-on-scroll">
                    <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-6">
                        1. Acceptarea Termenilor
                    </h2>
                    <p class="text-primary-600 mb-6">
                        Prin accesarea și utilizarea site-ului Auto Premium, acceptați să respectați acești termeni și condiții. 
                        Dacă nu sunteți de acord cu orice parte a acestor termeni, vă rugăm să nu utilizați site-ul nostru.
                    </p>

                    <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-6">
                        2. Utilizarea Site-ului
                    </h2>
                    <p class="text-primary-600 mb-6">
                        Site-ul Auto Premium este destinat utilizării personale și necomerciale. Nu aveți dreptul să:
                    </p>
                    <ul class="text-primary-600 mb-6 list-disc pl-6">
                        <li>Utilizați site-ul pentru scopuri ilegale sau neautorizate</li>
                        <li>Reproduceți, distribuiți sau modificați conținutul site-ului fără permisiune</li>
                        <li>Încercați să accesați sistemele noastre fără autorizare</li>
                        <li>Transmiteți virusi sau coduri dăunătoare</li>
                    </ul>

                    <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-6">
                        3. Informații despre Vehicule
                    </h2>
                    <p class="text-primary-600 mb-6">
                        Toate informațiile despre vehicule sunt furnizate cu bună credință, dar nu garantăm că sunt complet 
                        exacte sau actualizate. Vă recomandăm să verificați personal informațiile înainte de a lua o decizie 
                        de achiziție.
                    </p>

                    <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-6">
                        4. Prețuri și Disponibilitate
                    </h2>
                    <p class="text-primary-600 mb-6">
                        Prețurile afișate pot fi modificate fără notificare prealabilă. Disponibilitatea vehiculelor 
                        poate varia în funcție de cerere. Contactați-ne pentru confirmarea prețurilor și disponibilității.
                    </p>

                    <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-6">
                        5. Confidențialitatea
                    </h2>
                    <p class="text-primary-600 mb-6">
                        Protejăm informațiile personale conform Politicii de Confidențialitate. Informațiile furnizate 
                        prin formularul de contact sunt utilizate doar pentru a răspunde solicitărilor dumneavoastră.
                    </p>

                    <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-6">
                        6. Limitarea Răspunderii
                    </h2>
                    <p class="text-primary-600 mb-6">
                        Auto Premium nu poate fi trasă la răspundere pentru daune indirecte, incidentale sau consecvente 
                        care pot rezulta din utilizarea site-ului sau serviciilor noastre.
                    </p>

                    <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-6">
                        7. Proprietate Intelectuală
                    </h2>
                    <p class="text-primary-600 mb-6">
                        Toate drepturile de proprietate intelectuală asupra conținutului site-ului aparțin Auto Premium 
                        sau licențiatorilor săi. Reproducerea fără permisiune este interzisă.
                    </p>

                    <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-6">
                        8. Modificări ale Termenilor
                    </h2>
                    <p class="text-primary-600 mb-6">
                        Ne rezervăm dreptul de a modifica acești termeni în orice moment. Modificările vor fi afișate 
                        pe această pagină și vor intra în vigoare la data publicării.
                    </p>

                    <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-6">
                        9. Contact
                    </h2>
                    <p class="text-primary-600 mb-6">
                        Pentru întrebări despre acești termeni, ne puteți contacta la:
                    </p>
                    <div class="bg-primary-50 p-6 rounded-lg">
                        <p class="text-primary-600">
                            <strong>Email:</strong> contact@autopremium.ro<br>
                            <strong>Telefon:</strong> +40 123 456 789<br>
                            <strong>Adresa:</strong> Strada Exemplu, Nr. 123, Sector 1, București
                        </p>
                    </div>

                    <h2 class="text-2xl font-poppins font-bold text-primary-800 mb-6">
                        10. Legea Aplicabilă
                    </h2>
                    <p class="text-primary-600 mb-6">
                        Acești termeni sunt guvernați de legislația română. Orice dispută va fi rezolvată în instanțele 
                        competente din România.
                    </p>

                    <div class="bg-primary-50 p-6 rounded-lg mt-8">
                        <p class="text-primary-600 text-sm">
                            <strong>Ultima actualizare:</strong> {{ date('d.m.Y') }}<br>
                            Acești termeni și condiții sunt valabili de la data publicării și se aplică tuturor utilizatorilor site-ului.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection 