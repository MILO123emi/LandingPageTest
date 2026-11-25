<?php

namespace App\Console\Commands;

use App\Actions\Users\ChangeEmail;
use App\Models\User;
use DomainException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SetAdminEmail extends Command
{
    protected $signature = 'vitrina:set-email {email} {nuevo}';

    protected $description = 'Cambiar el correo de una cuenta y cerrar sus sesiones';

    public function handle(ChangeEmail $changeEmail): int
    {
        $user = User::query()->where('email', Str::lower(trim((string) $this->argument('email'))))->first();
        if ($user === null) {
            $this->error('No existe una cuenta con ese correo.');

            return self::FAILURE;
        }

        $email = Str::lower(trim((string) $this->argument('nuevo')));
        $validator = Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:255', 'unique:users,email']]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        if (! $this->confirm("¿Cambiar el correo de {$user->name} de {$user->email} a {$email}?")) {
            return self::FAILURE;
        }

        try {
            $changeEmail->handle($user, $email, null);
        } catch (DomainException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Correo actualizado. Se cerraron todas las sesiones.');

        return self::SUCCESS;
    }
}
