<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\VehicleController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/despre', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
Route::get('/catalog', [App\Http\Controllers\CatalogController::class, 'index'])->name('catalog');

// Detaliu vehicul (din baza de date)
Route::get('/vehicles/{slug}', [App\Http\Controllers\VehicleController::class, 'show'])->name('vehicle.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::redirect('/admin', '/admin/vehicles')->name('admin.home');
    Route::get('/admin/vehicles', [VehicleController::class, 'index'])->name('admin.vehicles.index');
    Route::get('/admin/vehicles/create', [VehicleController::class, 'create'])->name('admin.vehicles.create');
    Route::post('/admin/vehicles', [VehicleController::class, 'store'])->name('admin.vehicles.store');
    Route::get('/admin/vehicles/{slug}', [VehicleController::class, 'show'])->name('admin.vehicles.show');
    Route::get('/admin/vehicles/{slug}/edit', [VehicleController::class, 'edit'])->name('admin.vehicles.edit');
    Route::patch('/admin/vehicles/{slug}', [VehicleController::class, 'update'])->name('admin.vehicles.update');
    Route::delete('/admin/vehicles/{slug}', [VehicleController::class, 'destroy'])->name('admin.vehicles.destroy');
    
    // Advanced admin actions
Route::post('/admin/vehicles/{slug}/toggle-featured', [VehicleController::class, 'toggleFeatured'])->name('admin.vehicles.toggle-featured');
Route::post('/admin/vehicles/bulk-action', [VehicleController::class, 'bulkAction'])->name('admin.vehicles.bulk-action');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
