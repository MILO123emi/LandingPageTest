<?php

namespace App\Console\Commands;

use App\Actions\Users\SetPassword;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ResetAdminPassword extends Command
{
    protected $signature = 'vitrina:reset-password {email}';

    protected $description = 'Asignar una contraseña temporal y cerrar las sesiones de la cuenta';

    public function handle(SetPassword $setPassword): int
    {
        $user = User::query()->where('email', Str::lower(trim((string) $this->argument('email'))))->first();
        if ($user === null) {
            $this->error('No existe una cuenta con ese correo.');

            return self::FAILURE;
        }

        $password = $this->secret('Contraseña');
        $confirmation = $this->secret('Repetí la contraseña');
        $validator = Validator::make(['password' => $password, 'password_confirmation' => $confirmation], [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $setPassword->handle($user, $validator->validated()['password'], true, null);
        $this->info('Contraseña temporal asignada. Se cerraron todas las sesiones.');

        return self::SUCCESS;
    }
}
