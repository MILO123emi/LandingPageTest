<?php

use App\Http\Controllers\Settings\EmailController;
use App\Http\Controllers\Settings\ForcedPasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::get('settings/email', [EmailController::class, 'edit'])->name('email.edit');
    Route::post('settings/email', [EmailController::class, 'store'])->middleware('throttle:3,1')->name('email.store');
    Route::put('settings/email', [EmailController::class, 'update'])->middleware('throttle:6,1')->name('email.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');
});

Route::middleware(['auth'])->group(function () {
    Route::get('settings/cambiar-contrasena', [ForcedPasswordController::class, 'edit'])->name('password.change');
    Route::put('settings/cambiar-contrasena', [ForcedPasswordController::class, 'update'])
        ->middleware('throttle:6,1')->name('password.change.update');
});
