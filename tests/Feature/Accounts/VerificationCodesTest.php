<?php

use App\Enums\CodePurpose;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Support\MailGuard;
use App\Support\VerificationCodes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->codes = app(VerificationCodes::class);
});

test('issuing returns six digits and stores only a hash', function () {
    $code = $this->codes->issue($this->user, CodePurpose::PasswordRecovery);

    expect($code)->toMatch('/^\d{6}$/');
    $row = $this->user->verificationCodes()->sole();
    expect($row->code_hash)->not->toBe($code)
        ->and(Hash::check($code, $row->code_hash))->toBeTrue();
});

test('issuing replaces the previous code of the same purpose only', function () {
    $this->codes->issue($this->user, CodePurpose::EmailChange, 'nuevo@example.com');
    $this->codes->issue($this->user, CodePurpose::PasswordRecovery);
    $this->codes->issue($this->user, CodePurpose::PasswordRecovery);

    expect($this->user->verificationCodes()->count())->toBe(2);
});

test('a correct code is consumed once', function () {
    $code = $this->codes->issue($this->user, CodePurpose::EmailChange, 'nuevo@example.com');

    expect($this->codes->verify($this->user, CodePurpose::EmailChange, $code)->new_email)->toBe('nuevo@example.com');
    expect(fn () => $this->codes->verify($this->user, CodePurpose::EmailChange, $code))->toThrow(ValidationException::class);
});

test('a wrong code counts an attempt', function () {
    $code = $this->codes->issue($this->user, CodePurpose::PasswordRecovery);
    $wrong = $code === '111111' ? '222222' : '111111';

    expect(fn () => $this->codes->verify($this->user, CodePurpose::PasswordRecovery, $wrong))->toThrow(ValidationException::class);
    expect($this->user->verificationCodes()->sole()->attempts)->toBe(1);
});

test('five wrong codes lock even the right one', function () {
    $code = $this->codes->issue($this->user, CodePurpose::PasswordRecovery);
    $wrong = $code === '111111' ? '222222' : '111111';

    foreach (range(1, 5) as $ignored) {
        try {
            $this->codes->verify($this->user, CodePurpose::PasswordRecovery, $wrong);
        } catch (ValidationException) {
        }
    }

    expect(fn () => $this->codes->verify($this->user, CodePurpose::PasswordRecovery, $code))->toThrow(ValidationException::class);
});

test('a code expires after ten minutes', function () {
    $code = $this->codes->issue($this->user, CodePurpose::PasswordRecovery);
    $this->travel(11)->minutes();

    expect(fn () => $this->codes->verify($this->user, CodePurpose::PasswordRecovery, $code))->toThrow(ValidationException::class);
    expect($this->codes->pending($this->user, CodePurpose::PasswordRecovery))->toBeNull();
});

test('mail cannot use the log driver outside local', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['mail.default' => 'log']);

    expect(fn () => MailGuard::ensureDeliverable())->toThrow(RuntimeException::class);
});

test('the mail shows the code', function () {
    (new VerificationCodeMail('123456', CodePurpose::EmailChange))
        ->assertHasSubject('Tu código para cambiar el correo')
        ->assertSeeInHtml('123456')
        ->assertSeeInHtml('Vence en 10 minutos');
});
