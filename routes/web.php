<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\VehicleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::view('/despre', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
Route::get('/catalog', [App\Http\Controllers\CatalogController::class, 'index'])->name('catalog');
Route::post('/contact/send', [App\Http\Controllers\ContactController::class, 'store'])->name('contact.send');

// Lightweight image resize with caching (local files only)
// No session, no cookies, no CSRF token: this route answers with an image and
// never reads or writes a session, but sitting in the `web` group it booted one —
// started a session, read the cookie, queued a Set-Cookie — on every cold image
// request. It only runs when a derivative is missing (built ones are served by
// nginx straight off disk), and that is exactly the request that is already
// paying 1.4-1.8 s of GD.
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
Route::get('/vehicles/{slug}', [App\Http\Controllers\VehicleController::class, 'show'])->name('vehicle.show');
Route::post('/inquiries', [App\Http\Controllers\InquiryController::class, 'store'])->name('inquiries.store');

// Pagina mașini salvate (doar frontend, fără backend)
Route::get('/brandbook', [App\Http\Controllers\BrandbookController::class, 'index'])->name('brandbook');
Route::get('/catalogo', [App\Http\Controllers\BrandCatalogController::class, 'index'])->name('catalogo');
Route::get('/coche/{slug}', [App\Http\Controllers\CarPageController::class, 'show'])->name('coche');
Route::get('/inicio', [App\Http\Controllers\HomePageController::class, 'index'])->name('inicio');
Route::get('/contacto', [App\Http\Controllers\ContactPageController::class, 'index'])->name('contacto');

Route::view('/saved-vehicles', 'pages.saved-vehicles')->name('saved-vehicles');

Route::get('/dashboard', function () {
    return redirect()->route('admin.home');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/admin', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.home');
    Route::get('/admin/vehicles', [VehicleController::class, 'index'])->name('admin.vehicles.index');
    Route::get('/admin/inquiries', [App\Http\Controllers\Admin\InquiryAdminController::class, 'index'])->name('admin.inquiries.index');
    Route::delete('/admin/inquiries/{inquiry}', [App\Http\Controllers\Admin\InquiryAdminController::class, 'destroy'])->name('admin.inquiries.destroy');
    Route::get('/admin/contacts', [App\Http\Controllers\Admin\ContactMessageController::class, 'index'])->name('admin.contacts.index');
    Route::delete('/admin/contacts/{contact}', [App\Http\Controllers\Admin\ContactMessageController::class, 'destroy'])->name('admin.contacts.destroy');
    Route::get('/admin/vehicles/create', [VehicleController::class, 'create'])->name('admin.vehicles.create');
    Route::post('/admin/vehicles', [VehicleController::class, 'store'])->name('admin.vehicles.store');
    Route::get('/admin/vehicles/{slug}', [VehicleController::class, 'show'])->name('admin.vehicles.show');
    Route::get('/admin/vehicles/{slug}/edit', [VehicleController::class, 'edit'])->name('admin.vehicles.edit');
    Route::post('/admin/vehicles/{slug}', [VehicleController::class, 'update'])->name('admin.vehicles.update');
    Route::delete('/admin/vehicles/{slug}', [VehicleController::class, 'destroy'])->name('admin.vehicles.destroy');
    
    // Page Content - Testimonials
    Route::get('/admin/testimonials', [App\Http\Controllers\Admin\TestimonialController::class, 'index'])->name('admin.testimonials.index');
    Route::get('/admin/testimonials/create', [App\Http\Controllers\Admin\TestimonialController::class, 'create'])->name('admin.testimonials.create');
    Route::post('/admin/testimonials', [App\Http\Controllers\Admin\TestimonialController::class, 'store'])->name('admin.testimonials.store');
    Route::get('/admin/testimonials/{testimonial}/edit', [App\Http\Controllers\Admin\TestimonialController::class, 'edit'])->name('admin.testimonials.edit');
    Route::put('/admin/testimonials/{testimonial}', [App\Http\Controllers\Admin\TestimonialController::class, 'update'])->name('admin.testimonials.update');
    Route::delete('/admin/testimonials/{testimonial}', [App\Http\Controllers\Admin\TestimonialController::class, 'destroy'])->name('admin.testimonials.destroy');

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
    Route::post('/admin/vehicles/bulk-action', [VehicleController::class, 'bulkAction'])->name('admin.vehicles.bulk-action');
    
    // Enhanced pricing and offers management
    Route::post('/admin/vehicles/bulk-pricing', [VehicleController::class, 'bulkPricingUpdate'])->name('admin.vehicles.bulk-pricing');
    Route::get('/admin/vehicles/export-pricing', [VehicleController::class, 'exportPricingReport'])->name('admin.vehicles.export-pricing');
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
