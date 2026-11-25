<?php

use App\Models\Site;
use App\Models\User;
use App\Rules\SitePath;
use Illuminate\Support\Facades\Validator;

test('a site can be created with a valid path', function () {
    $site = Site::factory()->create(['path' => 'demo-verde']);

    expect($site->fresh()->path)->toBe('demo-verde');
});

test('reserved and malformed paths are rejected on save', function (string $path) {
    Site::factory()->create(['path' => $path]);
})->with(['admin', 'login', 'register', 'settings', 'up', 'Demo-Verde', 'demo_verde', 'demo verde', '-demo', ''])
    ->throws(InvalidArgumentException::class);

test('the admin path cannot be used as a site path', function () {
    Site::factory()->create(['path' => config('vitrina.admin_path')]);
})->throws(InvalidArgumentException::class);

test('the validation rule fails for reserved paths', function () {
    $validator = Validator::make(['path' => 'login'], ['path' => [new SitePath]]);

    expect($validator->fails())->toBeTrue();
});

test('an invalid admin path is rejected', function (string $value) {
    SitePath::assertValidAdminPath($value);
})->with(['', 'login', 'Panel', 'panel/secreto', 'admin'])->throws(RuntimeException::class);

test('a valid admin path passes', function () {
    SitePath::assertValidAdminPath('panel-cambiar');

    expect(true)->toBeTrue();
});

test('a user only manages the sites attached to them', function () {
    $user = User::factory()->create();
    [$own, $other] = Site::factory()->count(2)->create();
    $user->sites()->attach($own);

    expect($user->canManage($own))->toBeTrue()
        ->and($user->canManage($other))->toBeFalse();
});
