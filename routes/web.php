<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\VehicleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// 2026-10-05: v2 is ivmotorclass.com. The home page is the new one at "/"; every address
// the old site used answers with a 301 to its new page, so links and search results keep
// working. The route names stay because older code still builds URLs from them.
Route::get('/', [App\Http\Controllers\HomePageController::class, 'index'])->name('home');
Route::redirect('/inicio', '/', 301)->name('inicio');
Route::redirect('/despre', '/contacto', 301)->name('about');
Route::redirect('/contact', '/contacto', 301)->name('contact');
Route::redirect('/catalog', '/catalogo', 301)->name('catalog');
// old site's form, kept for any cached page; rate-limited like the others
Route::post('/contact/send', [App\Http\Controllers\ContactController::class, 'store'])->middleware('throttle:5,60')->name('contact.send');

// Lightweight image resize with caching (local files only)
// No session, no cookies, no CSRF token: this route answers with an image and
// never reads or writes a session, but sitting in the `web` group it booted one —
// started a session, read the cookie, queued a Set-Cookie — on every cold image
// request. It only runs when a derivative is missing (built ones are served by
// nginx straight off disk), and that is exactly the request that is already
// paying 1.4-1.8 s of GD.
// the link-preview picture for a car (WhatsApp, Facebook…)
Route::get('/og/{slug}', [App\Http\Controllers\ShareImageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9-]+')->name('og.car');
Route::get('/img/{w}', [App\Http\Controllers\ImageController::class, 'resize'])
    ->withoutMiddleware([
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    ])
    ->whereNumber('w')->name('img.resize');

// Sell Your Car routes
Route::get('/vende', [App\Http\Controllers\SellCarController::class, 'index'])->name('sell-car');
// the address the live site uses, kept so nothing that links to it breaks
Route::redirect('/sell-car', '/vende', 301);
// The only unauthenticated POST on the site that writes a row and accepts files.
// throttle:sell-car is defined in AppServiceProvider — 4 an hour and 10 a day per
// IP, which no honest seller reaches and a script does in seconds.
// PublicFormLimits puts back the memory and time limits that bootstrap/app.php
// removes for the admin's 359 MB uploads.
Route::post('/vende', [App\Http\Controllers\SellCarController::class, 'store'])
    ->middleware(['throttle:sell-car', \App\Http\Middleware\PublicFormLimits::class])
    ->name('sell-car.store');

// Detaliu vehicul (din baza de date)
Route::get('/vehicles/{slug}', fn (string $slug) => redirect('/coche/' . $slug, 301))->name('vehicle.show');
Route::post('/inquiries', [App\Http\Controllers\InquiryController::class, 'store'])->middleware('throttle:5,60')->name('inquiries.store');

// Pagina mașini salvate (doar frontend, fără backend)
// The design system is internal: signed-in only, reached from the panel's menu.
// A guest is sent to the login and brought back here after it.
Route::get('/brandbook', [App\Http\Controllers\BrandbookController::class, 'index'])->middleware('auth')->name('brandbook');
Route::get('/catalogo', [App\Http\Controllers\BrandCatalogController::class, 'index'])->name('catalogo');
// /muestras/*: private proposals for the client, behind a 4-digit PIN
Route::middleware(App\Http\Middleware\MuestrasPin::class)->group(function () {
    Route::get('/muestras/opiniones', [App\Http\Controllers\ReviewShowroomController::class, 'index'])->name('muestras.opiniones');
    Route::get('/muestras/opiniones/{v}', [App\Http\Controllers\ReviewShowroomController::class, 'show'])->where('v', '[0-9]+(\.[0-9]+)?')->name('muestras.opiniones.show');
    Route::get('/muestras/por-que', [App\Http\Controllers\WhyShowroomController::class, 'index'])->name('muestras.porque');
    Route::get('/muestras/por-que/{v}', [App\Http\Controllers\WhyShowroomController::class, 'show'])->where('v', '[0-9]+(\.[0-9]+)?')->name('muestras.porque.show');
});
Route::post('/muestras/pin', [App\Http\Middleware\MuestrasPin::class, 'check'])->middleware('throttle:12,1');
Route::get('/coche/{slug}', [App\Http\Controllers\CarPageController::class, 'show'])->name('coche');
Route::get('/contacto', [App\Http\Controllers\ContactPageController::class, 'index'])->name('contacto');
Route::get('/por-que-nosotros', [App\Http\Controllers\WhyUsController::class, 'index'])->name('why');
// A private showroom for the client: four ways to show the reviews. Not linked, noindex,
// not in the sitemap — App\Http\Controllers\ReviewShowroomController.

// Recommendations without accounts — App\Support\Referral has the rules.
// What the page script reports as a WhatsApp or e-mail press leaves the site.
Route::post('/r/e', [App\Http\Controllers\ReferralController::class, 'event'])
    ->middleware('throttle:120,1')->name('refer.event');
// The panel's statistics: one anonymous beacon per page and press — App\Support\Stats.
Route::post('/s', [App\Http\Controllers\BeaconController::class, 'hit'])
    ->middleware('throttle:120,1')->name('stats.hit');
// "No contar este dispositivo": a signed link that keeps a phone of the team out of them.
Route::get('/no-contar', [App\Http\Controllers\StatsMeController::class, 'show'])
    ->middleware(['signed', 'throttle:20,1'])->name('stats.me');
Route::get('/no-contar/quitar', [App\Http\Controllers\StatsMeController::class, 'undo'])
    ->middleware(['signed', 'throttle:20,1'])->name('stats.me.undo');
Route::get('/r/{code}', [App\Http\Controllers\ReferralController::class, 'visit'])->name('refer.visit');
Route::get('/recomienda', [App\Http\Controllers\ReferralController::class, 'show'])->name('refer');
// A customer writes their own opinion through the link the owner sent them
// (App\Models\ReviewLink): the token is the only key. Not in the sitemap, noindex.
Route::get('/opinion/{token}', [App\Http\Controllers\OpinionController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{20}')->name('opinion');
Route::post('/opinion/{token}', [App\Http\Controllers\OpinionController::class, 'store'])
    ->where('token', '[A-Za-z0-9]{20}')
    ->middleware(['throttle:5,60', \App\Http\Middleware\PublicFormLimits::class])->name('opinion.store');
Route::post('/recomienda', [App\Http\Controllers\ReferralController::class, 'store'])
    ->middleware('throttle:referral')->name('refer.store');

Route::redirect('/saved-vehicles', '/catalogo', 301)->name('saved-vehicles');

Route::get('/dashboard', function () {
    return redirect()->route('admin.home');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/admin', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.home');
    Route::get('/admin/estadisticas', [App\Http\Controllers\Admin\StatsController::class, 'index'])->name('admin.stats');
    // The sections that do not fit the phone bar, as a page. The "Más" tab opens
    // them in a sheet; this is where it goes when the sheet cannot open.
    Route::view('/admin/mas', 'admin.more')->name('admin.more');

    Route::get('/admin/recomendaciones', [App\Http\Controllers\Admin\ReferralController::class, 'index'])->name('admin.referrals.index');
    Route::get('/admin/recomendaciones/como-funciona', [App\Http\Controllers\Admin\ReferralController::class, 'how'])->name('admin.referrals.how');
    Route::get('/admin/recomendaciones/nuevo', [App\Http\Controllers\Admin\ReferralController::class, 'create'])->name('admin.referrals.create');
    Route::get('/admin/recomendaciones/{referrer}', [App\Http\Controllers\Admin\ReferralController::class, 'show'])->name('admin.referrals.show');
    Route::post('/admin/recomendaciones', [App\Http\Controllers\Admin\ReferralController::class, 'store'])->name('admin.referrals.store');
    Route::post('/admin/recomendaciones/premios/{reward}', [App\Http\Controllers\Admin\ReferralController::class, 'reward'])->name('admin.referrals.reward');
    Route::delete('/admin/recomendaciones/{referrer}', [App\Http\Controllers\Admin\ReferralController::class, 'destroy'])->name('admin.referrals.destroy');
    Route::get('/admin/vehicles', [VehicleController::class, 'index'])->name('admin.vehicles.index');
    Route::get('/admin/inquiries', [App\Http\Controllers\Admin\InquiryAdminController::class, 'index'])->name('admin.inquiries.index');
    Route::delete('/admin/inquiries/{inquiry}', [App\Http\Controllers\Admin\InquiryAdminController::class, 'destroy'])->name('admin.inquiries.destroy');
    Route::get('/admin/contacts', [App\Http\Controllers\Admin\ContactMessageController::class, 'index'])->name('admin.contacts.index');
    Route::delete('/admin/contacts/{contact}', [App\Http\Controllers\Admin\ContactMessageController::class, 'destroy'])->name('admin.contacts.destroy');
    // The car form's photos, sent one at a time, and its heartbeat (see VehiclePhotoController).
    // Not under /admin/vehicles/: {slug} there would take "fotos" for a car.
    Route::post('/admin/fotos', [App\Http\Controllers\Admin\VehiclePhotoController::class, 'store'])->name('admin.photos.store');
    Route::delete('/admin/fotos', [App\Http\Controllers\Admin\VehiclePhotoController::class, 'destroy'])->name('admin.photos.destroy');
    Route::get('/admin/ping', [App\Http\Controllers\Admin\VehiclePhotoController::class, 'ping'])->name('admin.ping');
    Route::get('/admin/vehicles/create', [VehicleController::class, 'create'])->name('admin.vehicles.create');
    // fixed paths before /{slug}, or {slug} swallows them
    Route::post('/admin/vehicles/bulk-action', [VehicleController::class, 'bulkAction'])->name('admin.vehicles.bulk-action');
    Route::post('/admin/vehicles/bulk-pricing', [VehicleController::class, 'bulkPricingUpdate'])->name('admin.vehicles.bulk-pricing');
    Route::get('/admin/vehicles/export-pricing', [VehicleController::class, 'exportPricingReport'])->name('admin.vehicles.export-pricing');
    Route::post('/admin/vehicles', [VehicleController::class, 'store'])->name('admin.vehicles.store');
    Route::get('/admin/vehicles/{slug}', [VehicleController::class, 'show'])->name('admin.vehicles.show');
    Route::get('/admin/vehicles/{slug}/edit', [VehicleController::class, 'edit'])->name('admin.vehicles.edit');
    // The coches.net sheet read into the form, and the A4 placard for the bonnet (Admin\SheetController).
    Route::post('/admin/fichas/leer', [App\Http\Controllers\Admin\SheetController::class, 'read'])->name('admin.sheets.read');
    Route::get('/admin/fichas/{slug}/original', [App\Http\Controllers\Admin\SheetController::class, 'original'])->name('admin.sheets.original');
    Route::get('/admin/vehicles/{slug}/cartel', [App\Http\Controllers\Admin\SheetController::class, 'placard'])->name('admin.vehicles.placard');
    Route::get('/admin/vehicles/{slug}/cartel.pdf', [App\Http\Controllers\Admin\SheetController::class, 'placardPdf'])->name('admin.vehicles.placard-pdf');
    Route::post('/admin/vehicles/{slug}', [VehicleController::class, 'update'])->name('admin.vehicles.update');
    Route::delete('/admin/vehicles/{slug}', [VehicleController::class, 'destroy'])->name('admin.vehicles.destroy');
    
    // Page Content - Testimonials
    Route::get('/admin/testimonials', [App\Http\Controllers\Admin\TestimonialController::class, 'index'])->name('admin.testimonials.index');
    Route::get('/admin/testimonials/create', [App\Http\Controllers\Admin\TestimonialController::class, 'create'])->name('admin.testimonials.create');
    Route::post('/admin/testimonials', [App\Http\Controllers\Admin\TestimonialController::class, 'store'])->name('admin.testimonials.store');
    Route::get('/admin/testimonials/{testimonial}/edit', [App\Http\Controllers\Admin\TestimonialController::class, 'edit'])->name('admin.testimonials.edit');
    Route::put('/admin/testimonials/{testimonial}', [App\Http\Controllers\Admin\TestimonialController::class, 'update'])->name('admin.testimonials.update');
    Route::delete('/admin/testimonials/{testimonial}', [App\Http\Controllers\Admin\TestimonialController::class, 'destroy'])->name('admin.testimonials.destroy');
    // the links a customer writes their own opinion through (Admin\ReviewLinkController)
    Route::post('/admin/testimonials/enlaces', [App\Http\Controllers\Admin\ReviewLinkController::class, 'store'])->name('admin.review-links.store');
    Route::get('/admin/testimonials/enlaces/{link}', [App\Http\Controllers\Admin\ReviewLinkController::class, 'show'])->whereNumber('link')->name('admin.review-links.show');
    Route::post('/admin/testimonials/enlaces/{link}/cerrar', [App\Http\Controllers\Admin\ReviewLinkController::class, 'close'])->whereNumber('link')->name('admin.review-links.close');

    // Sell Cars admin routes
    Route::get('/admin/sell-cars', [App\Http\Controllers\Admin\SellCarController::class, 'index'])->name('admin.sell-cars.index');
    Route::get('/admin/sell-cars/{vehicle}', [App\Http\Controllers\Admin\SellCarController::class, 'show'])->name('admin.sell-cars.show');
    Route::get('/admin/sell-cars/{vehicle}/edit', [App\Http\Controllers\Admin\SellCarController::class, 'edit'])->name('admin.sell-cars.edit');
    Route::put('/admin/sell-cars/{vehicle}', [App\Http\Controllers\Admin\SellCarController::class, 'update'])->name('admin.sell-cars.update');
    Route::post('/admin/sell-cars/{vehicle}/approve', [App\Http\Controllers\Admin\SellCarController::class, 'approve'])->name('admin.sell-cars.approve');
    Route::post('/admin/sell-cars/{vehicle}/reject', [App\Http\Controllers\Admin\SellCarController::class, 'reject'])->name('admin.sell-cars.reject');
    Route::get('/admin/sell-cars/{vehicle}/download-photos', [App\Http\Controllers\Admin\SellCarController::class, 'downloadPhotos'])->name('admin.sell-cars.download-photos');
    Route::delete('/admin/sell-cars/{vehicle}', [App\Http\Controllers\Admin\SellCarController::class, 'destroy'])->name('admin.sell-cars.destroy');
    
    // Advanced admin actions
    Route::post('/admin/vehicles/{slug}/status', [VehicleController::class, 'setStatus'])->name('admin.vehicles.status');
    Route::post('/admin/vehicles/{slug}/toggle-featured', [VehicleController::class, 'toggleFeatured'])->name('admin.vehicles.toggle-featured');
    
    // Enhanced pricing and offers management
    /* /admin/vehicles/pricing-analytics is gone. The route pointed at
       VehicleController@pricingAnalytics, which does not exist and never
       has, so opening it raised "Call to undefined method" — a 500, not a
       page. Its view was 350 lines of Bootstrap still written in Romanian
       and nothing in the panel linked to either. Removed rather than
       translated: there was no page to translate. */
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// The four routes that stood here — /check-php, /test-controller-settings,
// /test-upload-form and POST /test-upload — were all marked "TEMPORARY" by their
// author. They printed the server's PHP configuration and offered an
// unauthenticated file upload into a web-served directory: SEC-05 and SEC-02 in
// docs/vault/70-Audit.
//
// They read as harmless because BrandbookOnly was serving a holding page over
// them. Probed with the lockdown switched off, which is what launch does, all
// three GETs answered 200 with the real content. Removed 2026-09-08.

// Who is behind the site, the conditions of use, personal data and cookies
// (LegalController). /aviso-legal and /cookies are the names people look for.
Route::get('/terminos', [App\Http\Controllers\LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacidad', [App\Http\Controllers\LegalController::class, 'privacy'])->name('legal.privacy');
Route::redirect('/aviso-legal', '/terminos', 301);
Route::redirect('/politica-de-privacidad', '/privacidad', 301);
Route::redirect('/cookies', '/privacidad#cookies', 301);

// The sitemap (with every car's photographs) and /llms.txt, from the database, so a
// car added in the panel is in them the same minute — App\Http\Controllers\SeoController.
Route::get('/sitemap.xml', [App\Http\Controllers\SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/llms.txt', [App\Http\Controllers\SeoController::class, 'llms'])->name('llms');
