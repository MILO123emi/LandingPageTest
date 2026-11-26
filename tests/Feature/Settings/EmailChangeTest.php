<?php

use App\Enums\CodePurpose;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function requestEmailCode($test, string $email = 'nuevo@example.com'): string
{
    $test->post(route('email.store'), ['email' => $email, 'current_password' => 'password'])
        ->assertSessionHasNoErrors();
    $code = '';
    Mail::assertSent(VerificationCodeMail::class, function ($mail) use (&$code) {
        $code = $mail->code;

        return true;
    });

    return $code;
}

test('a code is sent to the new address and nothing changes yet', function () {
    $email = $this->user->email;
    requestEmailCode($this);
    Mail::assertSent(VerificationCodeMail::class, fn ($mail) => $mail->hasTo('nuevo@example.com') && $mail->purpose === CodePurpose::EmailChange);
    expect($this->user->refresh()->email)->toBe($email);
});

test('the email changes only with the right code and other sessions close', function () {
    $code = requestEmailCode($this);
    $keep = app('session')->getId();
    $this->withCookie(config('session.cookie'), $keep);
    foreach ([$keep, 'other'] as $id) {
        DB::table('sessions')->insert(['id' => $id, 'user_id' => $this->user->id, 'payload' => '', 'last_activity' => now()->timestamp]);
    }
    $this->put(route('email.update'), ['code' => $code])->assertSessionHasNoErrors()->assertRedirect(route('email.edit'));
    expect($this->user->refresh()->email)->toBe('nuevo@example.com')
        ->and(DB::table('sessions')->pluck('id')->all())->toBe([$keep]);
});

test('a wrong code changes nothing', function () {
    $email = $this->user->email;
    $code = requestEmailCode($this);
    $this->put(route('email.update'), ['code' => $code === '111111' ? '222222' : '111111'])->assertSessionHasErrors('code');
    expect($this->user->refresh()->email)->toBe($email);
});

test('an expired code is refused', function () {
    $email = $this->user->email;
    $code = requestEmailCode($this);
    $this->travel(11)->minutes();
    $this->put(route('email.update'), ['code' => $code])->assertSessionHasErrors('code');
    expect($this->user->refresh()->email)->toBe($email);
});

test('five wrong codes block the change', function () {
    $email = $this->user->email;
    $code = requestEmailCode($this);
    foreach (range(1, 5) as $ignored) {
        $this->put(route('email.update'), ['code' => $code === '111111' ? '222222' : '111111'])->assertSessionHasErrors('code');
    }
    $this->travel(61)->seconds();
    $this->put(route('email.update'), ['code' => $code])->assertSessionHasErrors('code');
    expect($this->user->refresh()->email)->toBe($email);
});

test('an email of another account is refused', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $this->post(route('email.store'), ['email' => 'taken@example.com', 'current_password' => 'password'])->assertSessionHasErrors('email');
    Mail::assertNothingSent();
});

test('the current password is required', function () {
    $this->post(route('email.store'), ['email' => 'nuevo@example.com'])->assertSessionHasErrors('current_password');
    Mail::assertNothingSent();
});

test('a wrong password hides whether the new email is taken', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $this->post(route('email.store'), ['email' => 'taken@example.com', 'current_password' => 'incorrecta-123'])
        ->assertSessionHasErrors('current_password')
        ->assertSessionDoesntHaveErrors('email');
    Mail::assertNothingSent();
});

test('uppercase and spaces are normalized', function () {
    $code = requestEmailCode($this, ' Nuevo@Example.COM ');
    Mail::assertSent(VerificationCodeMail::class, fn ($mail) => $mail->hasTo('nuevo@example.com'));
    $this->put(route('email.update'), ['code' => $code])->assertSessionHasNoErrors();
    expect($this->user->refresh()->email)->toBe('nuevo@example.com');
});

test('if another account takes the address before confirming, the code field shows the error', function () {
    $email = $this->user->email;
    $code = requestEmailCode($this);
    User::factory()->create(['email' => 'nuevo@example.com']);
    $this->put(route('email.update'), ['code' => $code])->assertSessionHasErrors('code');
    expect($this->user->refresh()->email)->toBe($email);
});
