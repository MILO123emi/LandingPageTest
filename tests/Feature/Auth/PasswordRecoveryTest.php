<?php

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Mail::fake();
});

function recoveryCode($test, User $user): string
{
    $test->post(route('password.recovery.store'), ['email' => $user->email])->assertRedirect(route('password.recovery.edit'));
    $code = '';
    Mail::assertSent(VerificationCodeMail::class, function ($mail) use (&$code) {
        $code = $mail->code;

        return true;
    });

    return $code;
}

test('the recovery screen is reachable from the hidden entry only', function () {
    $this->get('/'.config('vitrina.admin_path').'/recuperar')->assertOk();
    $this->get('/forgot-password')->assertNotFound();
});

test('known and unknown emails get the same response', function () {
    $user = User::factory()->create();
    $this->post(route('password.recovery.store'), ['email' => $user->email])->assertRedirect(route('password.recovery.edit'));
    $this->post(route('password.recovery.store'), ['email' => 'unknown@example.com'])->assertRedirect(route('password.recovery.edit'));
    Mail::assertSentCount(1);
});

test('uppercase and spaces in the email still send the code', function () {
    User::factory()->create(['email' => 'admin@example.com']);
    $this->post(route('password.recovery.store'), ['email' => ' Admin@Example.COM '])->assertRedirect(route('password.recovery.edit'));
    Mail::assertSent(VerificationCodeMail::class, fn ($mail) => $mail->hasTo('admin@example.com'));
});

test('the right code sets the new password and closes every session', function () {
    $user = User::factory()->create();
    $code = recoveryCode($this, $user);
    foreach (['a', 'b'] as $id) {
        DB::table('sessions')->insert(['id' => $id, 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->timestamp]);
    }
    $this->withSession(['recovery.email' => $user->email])->put(route('password.recovery.update'), [
        'code' => $code, 'password' => 'clave-nueva-123', 'password_confirmation' => 'clave-nueva-123',
    ])->assertSessionHasNoErrors()->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Listo. Ya podés entrar con tu contraseña nueva.')
        ->assertSessionMissing('recovery.email');
    expect(Hash::check('clave-nueva-123', $user->refresh()->password))->toBeTrue()
        ->and(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);
    $this->assertGuest();
});

test('recovering clears a temporary password flag', function () {
    $user = User::factory()->mustChangePassword()->create();
    $code = recoveryCode($this, $user);
    $this->withSession(['recovery.email' => $user->email])->put(route('password.recovery.update'), [
        'code' => $code, 'password' => 'clave-nueva-123', 'password_confirmation' => 'clave-nueva-123',
    ])->assertSessionHasNoErrors();
    expect($user->refresh()->must_change_password)->toBeFalse();
});

test('a wrong code changes nothing', function () {
    $user = User::factory()->create();
    $hash = $user->password;
    $code = recoveryCode($this, $user);
    $this->withSession(['recovery.email' => $user->email])->put(route('password.recovery.update'), [
        'code' => $code === '111111' ? '222222' : '111111', 'password' => 'clave-nueva-123', 'password_confirmation' => 'clave-nueva-123',
    ])->assertSessionHasErrors('code');
    expect($user->refresh()->password)->toBe($hash);
});

test('an unknown email never resets anything', function () {
    $user = User::factory()->create();
    $hash = $user->password;
    $this->withSession(['recovery.email' => 'unknown@example.com'])->put(route('password.recovery.update'), [
        'code' => '123456', 'password' => 'clave-nueva-123', 'password_confirmation' => 'clave-nueva-123',
    ])->assertSessionHasErrors('code');
    expect($user->refresh()->password)->toBe($hash);
    Mail::assertNothingSent();
});

test('requesting codes is throttled', function () {
    foreach (range(1, 3) as $ignored) {
        $this->post(route('password.recovery.store'), ['email' => 'unknown@example.com'])->assertRedirect();
    }
    $this->post(route('password.recovery.store'), ['email' => 'unknown@example.com'])->assertTooManyRequests();
});

test('requesting codes is throttled per email across addresses', function () {
    foreach (['10.0.0.1', '10.0.0.1', '10.0.0.1', '10.0.0.2', '10.0.0.2'] as $ip) {
        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(route('password.recovery.store'), ['email' => 'unknown@example.com'])->assertRedirect();
    }
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.3'])
        ->post(route('password.recovery.store'), ['email' => 'unknown@example.com'])->assertTooManyRequests();
});

test('the old fortify reset routes are gone', function () {
    expect(Route::has('password.request'))->toBeFalse()
        ->and(Route::has('password.email'))->toBeFalse()
        ->and(Route::has('password.update'))->toBeFalse();
});
