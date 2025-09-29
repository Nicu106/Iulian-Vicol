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

// Sell Your Car routes
Route::get('/sell-car', [App\Http\Controllers\SellCarController::class, 'index'])->name('sell-car');
Route::post('/sell-car', [App\Http\Controllers\SellCarController::class, 'store'])->name('sell-car.store');

// Detaliu vehicul (din baza de date)
Route::get('/vehicles/{slug}', [App\Http\Controllers\VehicleController::class, 'show'])->name('vehicle.show');
Route::post('/inquiries', [App\Http\Controllers\InquiryController::class, 'store'])->name('inquiries.store');

// Pagina mașini salvate (doar frontend, fără backend)
Route::view('/saved-vehicles', 'pages.saved-vehicles')->name('saved-vehicles');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/admin', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.home');
    Route::get('/admin/vehicles', [VehicleController::class, 'index'])->name('admin.vehicles.index');
    Route::get('/admin/inquiries', [App\Http\Controllers\Admin\InquiryAdminController::class, 'index'])->name('admin.inquiries.index');
    Route::get('/admin/contacts', [App\Http\Controllers\Admin\ContactMessageController::class, 'index'])->name('admin.contacts.index');
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
Route::post('/admin/vehicles/{slug}/toggle-featured', [VehicleController::class, 'toggleFeatured'])->name('admin.vehicles.toggle-featured');
Route::post('/admin/vehicles/bulk-action', [VehicleController::class, 'bulkAction'])->name('admin.vehicles.bulk-action');
    
    // Enhanced pricing and offers management
    Route::post('/admin/vehicles/bulk-pricing', [VehicleController::class, 'bulkPricingUpdate'])->name('admin.vehicles.bulk-pricing');
    Route::get('/admin/vehicles/export-pricing', [VehicleController::class, 'exportPricingReport'])->name('admin.vehicles.export-pricing');
    Route::get('/admin/vehicles/pricing-analytics', [VehicleController::class, 'pricingAnalytics'])->name('admin.vehicles.pricing-analytics');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// TEMPORARY: Check PHP settings
Route::get('/check-php', function() {
    return [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'max_input_time' => ini_get('max_input_time'),
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
    ];
});

// TEMPORARY: Test PHP settings in controller context
Route::get('/test-controller-settings', function() {
    // Force PHP settings for this request
    ini_set('upload_max_filesize', '0');
    ini_set('post_max_size', '0');
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', '0');
    
    return [
        'before_upload_max' => '2M (default)',
        'after_upload_max' => ini_get('upload_max_filesize'),
        'before_post_max' => '8M (default)',
        'after_post_max' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time')
    ];
});

// TEMPORARY: Test file upload directly (no CSRF for debugging)
Route::get('/test-upload-form', function() {
    return '
    <form method="POST" enctype="multipart/form-data" action="/test-upload">
        <input type="file" name="test_file" required>
        <button type="submit">Test Upload</button>
    </form>';
});

Route::post('/test-upload', function(\Illuminate\Http\Request $request) {
    $file = $request->file('test_file');
    
    if ($file && $file->isValid()) {
        try {
            $path = $file->store('test-uploads', 'public');
            return [
                'success' => true,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'stored_path' => $path,
                'public_url' => \Illuminate\Support\Facades\Storage::url($path)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize()
            ];
        }
    } else {
        return [
            'success' => false,
            'has_file' => $request->hasFile('test_file'),
            'file_object' => $file ? 'exists' : 'null',
            'error' => $file ? $file->getError() : 'no file'
        ];
    }
});
