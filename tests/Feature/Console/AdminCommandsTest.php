<?php

use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

test('create-admin creates an account that must change its password', function () {
    $site = Site::factory()->create();
    $this->artisan('vitrina:create-admin')
        ->expectsQuestion('Nombre', 'Demo Admin')
        ->expectsQuestion('Correo', ' Admin@Example.COM ')
        ->expectsQuestion('Contraseña', 'clave-nueva-123')
        ->expectsQuestion('Repetí la contraseña', 'clave-nueva-123')
        ->expectsChoice('Tiendas', [$site->slug], [$site->slug])
        ->expectsOutput('Cuenta creada. Al entrar va a tener que cambiar la contraseña.')
        ->assertSuccessful();
    $user = User::query()->sole();
    expect($user->email)->toBe('admin@example.com')
        ->and($user->must_change_password)->toBeTrue()
        ->and(Hash::check('clave-nueva-123', $user->password))->toBeTrue()
        ->and($user->sites()->pluck('sites.id')->all())->toBe([$site->id]);
});

test('create-admin --sin-cambio skips the change', function () {
    $site = Site::factory()->create();
    $this->artisan('vitrina:create-admin --sin-cambio')
        ->expectsQuestion('Nombre', 'Demo Admin')->expectsQuestion('Correo', 'admin@example.com')
        ->expectsQuestion('Contraseña', 'clave-nueva-123')->expectsQuestion('Repetí la contraseña', 'clave-nueva-123')
        ->expectsChoice('Tiendas', [$site->slug], [$site->slug])->expectsOutput('Cuenta creada.')->assertSuccessful();
    expect(User::query()->sole()->must_change_password)->toBeFalse();
});

test('create-admin refuses a short password, a taken email and mismatched confirmation', function (string $email, string $password, string $confirmation) {
    $site = Site::factory()->create();
    User::factory()->create(['email' => 'taken@example.com']);
    $this->artisan('vitrina:create-admin')
        ->expectsQuestion('Nombre', 'Demo Admin')->expectsQuestion('Correo', $email)
        ->expectsQuestion('Contraseña', $password)->expectsQuestion('Repetí la contraseña', $confirmation)
        ->expectsChoice('Tiendas', [$site->slug], [$site->slug])->assertFailed();
    expect(User::query()->count())->toBe(1)->and(DB::table('site_user')->count())->toBe(0);
})->with([
    ['new@example.com', 'corta', 'corta'],
    [' Taken@Example.COM ', 'clave-nueva-123', 'clave-nueva-123'],
    ['new@example.com', 'clave-nueva-123', 'otra-clave-123'],
]);

test('create-admin fails when there are no sites', function () {
    $this->artisan('vitrina:create-admin')->expectsOutput('No hay tiendas. Importá el contenido primero.')->assertFailed();
    expect(User::query()->count())->toBe(0);
});

test('reset-password sets a temporary password and closes sessions', function () {
    $user = User::factory()->create(['email' => 'admin@example.com']);
    DB::table('sessions')->insert(['id' => 'a', 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->timestamp]);
    $this->artisan('vitrina:reset-password', ['email' => ' Admin@Example.COM '])
        ->expectsQuestion('Contraseña', 'clave-nueva-123')->expectsQuestion('Repetí la contraseña', 'clave-nueva-123')->assertSuccessful();
    expect($user->refresh()->must_change_password)->toBeTrue()
        ->and(Hash::check('clave-nueva-123', $user->password))->toBeTrue()
        ->and(DB::table('sessions')->count())->toBe(0);
});

test('reset-password fails for an unknown email', function () {
    $this->artisan('vitrina:reset-password unknown@example.com')->expectsOutput('No existe una cuenta con ese correo.')->assertFailed();
});

test('set-email changes the email after confirming', function () {
    $user = User::factory()->create(['name' => 'Demo Admin', 'email' => 'admin@example.com']);
    $this->artisan('vitrina:set-email', ['email' => ' Admin@Example.COM ', 'nuevo' => ' Nuevo@Example.COM '])
        ->expectsConfirmation('¿Cambiar el correo de Demo Admin de admin@example.com a nuevo@example.com?', 'yes')
        ->assertSuccessful();
    expect($user->refresh()->email)->toBe('nuevo@example.com');
});

test('set-email does nothing when not confirmed', function () {
    $user = User::factory()->create(['name' => 'Demo Admin', 'email' => 'admin@example.com']);
    $this->artisan('vitrina:set-email admin@example.com nuevo@example.com')
        ->expectsConfirmation('¿Cambiar el correo de Demo Admin de admin@example.com a nuevo@example.com?', 'no')->assertFailed();
    expect($user->refresh()->email)->toBe('admin@example.com');
});

test('set-email refuses an email of another account', function () {
    $user = User::factory()->create(['email' => 'admin@example.com']);
    User::factory()->create(['email' => 'taken@example.com']);
    $this->artisan('vitrina:set-email admin@example.com taken@example.com')->assertFailed();
    expect($user->refresh()->email)->toBe('admin@example.com');
});

test('assign-sites adds and removes', function () {
    $user = User::factory()->create(['email' => 'admin@example.com']);
    [$old, $new] = Site::factory()->count(2)->create();
    $user->sites()->attach($old);
    $this->artisan('vitrina:assign-sites', ['email' => ' Admin@Example.COM ', '--add' => [$new->slug], '--remove' => [$old->slug]])->assertSuccessful();
    expect($user->sites()->pluck('sites.id')->all())->toBe([$new->id]);
});

test('assign-sites refuses unknown slugs, no options and leaving zero sites', function (string $scenario) {
    $user = User::factory()->create(['email' => 'admin@example.com']);
    $site = Site::factory()->create();
    $user->sites()->attach($site);
    $options = match ($scenario) {
        'unknown' => ['--add' => ['missing']],
        'none' => [],
        'zero' => ['--remove' => [$site->slug]],
    };
    $message = match ($scenario) {
        'unknown' => 'No existen estas tiendas: missing',
        'none' => 'Indicá --add o --remove.',
        'zero' => 'La cuenta quedaría sin tiendas.',
    };
    $this->artisan('vitrina:assign-sites', ['email' => $user->email, ...$options])->expectsOutput($message)->assertFailed();
    expect($user->sites()->pluck('sites.id')->all())->toBe([$site->id]);
})->with(['unknown', 'none', 'zero']);
