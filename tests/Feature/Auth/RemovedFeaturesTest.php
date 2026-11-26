<?php

use Illuminate\Support\Facades\Route;

test('removed starter features have no routes', function () {
    $uris = collect(Route::getRoutes()->getRoutes())->map->uri()->implode(' ');

    expect($uris)->not->toContain('two-factor')
        ->not->toContain('passkey')
        ->not->toContain('verify-email')
        ->not->toContain('email/verification');
    expect(Route::has('profile.destroy'))->toBeFalse();
});
