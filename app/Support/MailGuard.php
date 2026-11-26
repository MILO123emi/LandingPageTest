<?php

namespace App\Support;

use RuntimeException;

class MailGuard
{
    public static function ensureDeliverable(): void
    {
        if (! app()->environment(['local', 'testing']) && in_array(config('mail.default'), ['log', 'array'], true)) {
            throw new RuntimeException('El envío de correos no está configurado: MAIL_MAILER no puede ser "log" fuera del entorno local.');
        }
    }
}
