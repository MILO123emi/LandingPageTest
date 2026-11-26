<?php

use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\Admin\SiteDashboardController;
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
    Route::get('{site:slug}', SiteDashboardController::class)
        ->middleware('site.manage')
        ->name('admin.sites.show');
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
