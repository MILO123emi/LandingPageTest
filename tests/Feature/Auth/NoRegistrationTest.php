<?php

use Illuminate\Support\Facades\Route;

test('there is no registration route', function () {
    expect(Route::has('register'))->toBeFalse()
        ->and(Route::has('register.store'))->toBeFalse();

    $this->get('/register')->assertNotFound();
    $this->get('/'.config('vitrina.admin_path').'/register')->assertNotFound();
    $this->post('/'.config('vitrina.admin_path').'/register', [])->assertNotFound();
});
