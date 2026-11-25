<?php

use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\Admin\SiteDashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::redirect('/'.config('vitrina.admin_path'), '/'.config('vitrina.admin_path').'/login')->name('admin.entry');

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/', AdminHomeController::class)->name('dashboard');
    Route::get('{site:slug}', SiteDashboardController::class)
        ->middleware('site.manage')
        ->name('admin.sites.show');
});

require __DIR__.'/settings.php';

Route::get('{sitePath}', SiteController::class)
    ->where('sitePath', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->middleware('site.resolve')
    ->name('site.show');
