<?php

use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SiteDashboardController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Auth\PasswordRecoveryController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

$slug = '[a-z0-9]+(?:-[a-z0-9]+)*';

Route::get('/', HomeController::class)->name('home');

Route::redirect('/'.config('vitrina.admin_path'), '/'.config('vitrina.admin_path').'/login')->name('admin.entry');

Route::middleware(['auth', 'password.changed'])->prefix('admin')->group(function () {
    Route::get('/', AdminHomeController::class)->name('dashboard');
    Route::prefix('{site:slug}')->middleware('site.manage')->whereNumber(['category', 'product'])->group(function () {
        Route::get('/', SiteDashboardController::class)->name('admin.sites.show');
        Route::post('publicar', [SiteSettingsController::class, 'publish'])->name('admin.sites.publish');

        Route::get('categorias', [CategoryController::class, 'index'])->name('admin.categories.index');
        Route::post('categorias', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::put('categorias/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::post('categorias/{category}/mover', [CategoryController::class, 'move'])->name('admin.categories.move');
        Route::delete('categorias/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

        Route::get('productos', [AdminProductController::class, 'index'])->name('admin.products.index');
        Route::get('productos/nuevo', [AdminProductController::class, 'create'])->name('admin.products.create');
        Route::post('productos', [AdminProductController::class, 'store'])->name('admin.products.store');
        Route::get('productos/{product}/editar', [AdminProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('productos/{product}', [AdminProductController::class, 'update'])->name('admin.products.update');
        Route::patch('productos/{product}/estado', [AdminProductController::class, 'toggle'])->name('admin.products.toggle');
        Route::post('productos/{product}/mover', [AdminProductController::class, 'move'])->name('admin.products.move');
        Route::delete('productos/{product}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');

        Route::get('ajustes', [SiteSettingsController::class, 'edit'])->name('admin.settings.edit');
        Route::put('ajustes', [SiteSettingsController::class, 'update'])->name('admin.settings.update');
        Route::post('ajustes/mapa', [SiteSettingsController::class, 'previewLocation'])
            ->middleware('throttle:20,1')
            ->name('admin.settings.map');
    });
});

require __DIR__.'/settings.php';

Route::prefix(config('vitrina.admin_path'))->middleware('guest')->group(function () {
    Route::get('recuperar', [PasswordRecoveryController::class, 'create'])->name('password.recovery.create');
    Route::post('recuperar', [PasswordRecoveryController::class, 'store'])->middleware('throttle:recovery')->name('password.recovery.store');
    Route::get('recuperar/codigo', [PasswordRecoveryController::class, 'edit'])->name('password.recovery.edit');
    Route::put('recuperar/codigo', [PasswordRecoveryController::class, 'update'])->middleware('throttle:6,1')->name('password.recovery.update');
});

Route::prefix('{sitePath}')
    ->where(['sitePath' => $slug])
    ->middleware('site.resolve')
    ->group(function () use ($slug) {
        Route::get('/', SiteController::class)->name('site.show');
        Route::get('catalogo', [CatalogController::class, 'index'])->name('site.catalog');
        Route::get('catalogo/{categorySlug}', [CatalogController::class, 'category'])
            ->where('categorySlug', $slug)
            ->name('site.catalog.category');
        Route::get('producto/{productSlug}', ProductController::class)
            ->where('productSlug', $slug)
            ->name('site.product');
    });
