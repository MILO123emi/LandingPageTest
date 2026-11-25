<?php

namespace App\Mail;

use App\Enums\CodePurpose;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VerificationCodeMail extends Mailable
{
    public function __construct(#[\SensitiveParameter] public string $code, public CodePurpose $purpose) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: match ($this->purpose) {
            CodePurpose::EmailChange => 'Tu código para cambiar el correo',
            CodePurpose::PasswordRecovery => 'Tu código para recuperar la contraseña',
        });
    }

    public function content(): Content
    {
        return new Content(view: 'mail.verification-code');
    }
}
