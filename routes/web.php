<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::redirect('/'.config('vitrina.admin_path'), '/'.config('vitrina.admin_path').'/login')->name('admin.entry');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

Route::get('{sitePath}', SiteController::class)
    ->where('sitePath', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->middleware('site.resolve')
    ->name('site.show');
