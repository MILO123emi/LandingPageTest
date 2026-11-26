<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('an admin with a temporary password is sent to change it', function (string $path) {
    $this->actingAs(User::factory()->mustChangePassword()->create())
        ->get($path)->assertRedirect(route('password.change'));
})->with(['/admin', '/settings/profile']);

test('public pages and logout still work with a temporary password', function () {
    $this->actingAs(User::factory()->mustChangePassword()->create())
        ->get('/')->assertOk();
    $this->post(route('logout'))->assertRedirect(route('home'));
});

test('changing the temporary password clears the flag', function () {
    $user = User::factory()->mustChangePassword()->create();
    $this->actingAs($user)->put(route('password.change.update'), [
        'password' => 'clave-nueva-123', 'password_confirmation' => 'clave-nueva-123',
    ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

    expect($user->refresh()->must_change_password)->toBeFalse()
        ->and(Hash::check('clave-nueva-123', $user->password))->toBeTrue();
});

test('the new password needs at least ten characters', function () {
    $this->actingAs(User::factory()->mustChangePassword()->create())
        ->put(route('password.change.update'), [
            'password' => 'corta-123', 'password_confirmation' => 'corta-123',
        ])->assertSessionHasErrors('password');
});

test('admins without the flag skip the change screen', function () {
    $this->actingAs(User::factory()->create())->get(route('password.change'))
        ->assertRedirect(route('dashboard'));
});

test('admins without the flag cannot set a password without the current one', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->put(route('password.change.update'), [
        'password' => 'clave-nueva-123', 'password_confirmation' => 'clave-nueva-123',
    ])->assertForbidden();

    expect(Hash::check('clave-nueva-123', $user->refresh()->password))->toBeFalse();
});
