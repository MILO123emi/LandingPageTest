<?php

use App\Actions\Users\ChangeEmail;
use App\Actions\Users\CloseSessions;
use App\Actions\Users\SetPassword;
use App\Enums\CodePurpose;
use App\Models\User;
use App\Support\VerificationCodes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

function sessionRow(string $id, ?int $userId): void
{
    DB::table('sessions')->insert(['id' => $id, 'user_id' => $userId, 'ip_address' => null, 'user_agent' => null, 'payload' => '', 'last_activity' => now()->timestamp]);
}

test('closing sessions keeps only the given one and rotates the remember token', function () {
    $user = User::factory()->create();
    $token = $user->remember_token;
    sessionRow('a', $user->id);
    sessionRow('b', $user->id);
    sessionRow('c', User::factory()->create()->id);

    app(CloseSessions::class)->handle($user, 'a');

    expect(DB::table('sessions')->orderBy('id')->pluck('id')->all())->toBe(['a', 'c'])
        ->and($user->refresh()->remember_token)->not->toBe($token);
});

test('setting a password stores the flag and closes other sessions', function () {
    $user = User::factory()->create();
    sessionRow('a', $user->id);
    sessionRow('b', $user->id);

    app(SetPassword::class)->handle($user, 'clave-nueva-123', true, 'a');

    expect(Hash::check('clave-nueva-123', $user->refresh()->password))->toBeTrue()
        ->and($user->must_change_password)->toBeTrue()
        ->and(DB::table('sessions')->pluck('id')->all())->toBe(['a']);
});

test('changing the email normalizes it, drops pending codes and closes sessions', function () {
    $user = User::factory()->create();
    app(VerificationCodes::class)->issue($user, CodePurpose::EmailChange, 'nuevo@example.com');
    app(VerificationCodes::class)->issue($user, CodePurpose::PasswordRecovery);
    sessionRow('a', $user->id);

    app(ChangeEmail::class)->handle($user, ' Nuevo@Example.COM ');

    expect($user->refresh()->email)->toBe('nuevo@example.com')
        ->and($user->verificationCodes()->count())->toBe(0)
        ->and(DB::table('sessions')->count())->toBe(0);
});

test('changing to an email of another account fails', function () {
    $user = User::factory()->create();
    $email = $user->email;
    User::factory()->create(['email' => 'taken@example.com']);

    expect(fn () => app(ChangeEmail::class)->handle($user, 'taken@example.com'))->toThrow(DomainException::class);
    expect($user->refresh()->email)->toBe($email);
});

test('security events never include secrets', function () {
    $user = User::factory()->create();
    Log::shouldReceive('channel')->with('security')->andReturnSelf();
    Log::shouldReceive('info')->once()->withArgs(fn ($event, $context) => $event === 'password.changed'
        && $context['user_id'] === $user->id
        && ! str_contains(json_encode($context), 'clave-nueva-123'));

    app(SetPassword::class)->handle($user, 'clave-nueva-123', false);
});
