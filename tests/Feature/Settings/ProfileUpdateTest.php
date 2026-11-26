<?php

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
});

test('email is stored in lowercase so the user can still log in', function () {
    Mail::fake();
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post(route('email.store'), [
            'current_password' => 'password',
            'email' => 'Test@Example.com',
        ])
        ->assertSessionHasNoErrors();

    $code = '';
    Mail::assertSent(VerificationCodeMail::class, function ($mail) use (&$code) {
        $code = $mail->code;

        return true;
    });
    $this->put(route('email.update'), ['code' => $code])->assertSessionHasNoErrors();

    expect($user->refresh()->email)->toBe('test@example.com');

    $this->post(route('logout'));

    $this->post(route('login.store'), [
        'email' => 'Test@Example.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('email must be unique regardless of case', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('email.edit'))
        ->post(route('email.store'), [
            'current_password' => 'password',
            'email' => 'Taken@Example.com',
        ]);

    $response
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('email.edit'));
});

test('the profile form cannot change the email', function () {
    $user = User::factory()->create();
    $email = $user->email;
    $this->actingAs($user)->patch(route('profile.update'), ['name' => 'Demo Admin', 'email' => 'nuevo@example.com'])
        ->assertSessionHasNoErrors();
    expect($user->refresh()->email)->toBe($email);
});
