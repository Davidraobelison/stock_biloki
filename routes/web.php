<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CaisseController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.dashboard'));

Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', CategoryController::class);
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::resource('products', ProductController::class);

    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/{product}', [StockController::class, 'show'])->name('stock.show');
    Route::post('/stock/{product}/adjust', [StockController::class, 'adjust'])->name('stock.adjust');

    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

    Route::get('/caisse', [CaisseController::class, 'index'])->name('caisse.index');
    Route::post('/caisse', [CaisseController::class, 'store'])->name('caisse.store');
    Route::post('/caisse/{session}/close', [CaisseController::class, 'close'])->name('caisse.close');
    Route::post('/caisse/{session}/mouvement', [CaisseController::class, 'storeMouvement'])->name('caisse.mouvement');

    Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');
    Route::get('/clients/export', [ClientController::class, 'export'])->name('clients.export');
    Route::resource('clients', ClientController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
