<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Models\User;
use App\Support\SecurityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'vitrina:create-admin {--sin-cambio : No pedir el cambio de contraseña al primer ingreso}';

    protected $description = 'Crear una cuenta de administración y asignar sus tiendas';

    public function handle(): int
    {
        $slugs = Site::query()->orderBy('slug')->pluck('slug')->all();
        if ($slugs === []) {
            $this->error('No hay tiendas. Importá el contenido primero.');

            return self::FAILURE;
        }

        $name = $this->ask('Nombre');
        $email = Str::lower(trim((string) $this->ask('Correo')));
        $password = $this->secret('Contraseña');
        $confirmation = $this->secret('Repetí la contraseña');
        $selected = $this->choice('Tiendas', $slugs, multiple: true);
        $validator = Validator::make([
            'name' => $name, 'email' => $email, 'password' => $password, 'password_confirmation' => $confirmation,
        ], [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $mustChange = ! $this->option('sin-cambio');
        $data = $validator->validated();
        $user = DB::transaction(function () use ($data, $mustChange, $selected): User {
            $user = new User($data);
            $user->forceFill(['must_change_password' => $mustChange])->save();
            $user->sites()->attach(Site::query()->whereIn('slug', (array) $selected)->pluck('id'));

            return $user;
        });
        SecurityLog::record('admin.created', $user);
        $this->info($mustChange ? 'Cuenta creada. Al entrar va a tener que cambiar la contraseña.' : 'Cuenta creada.');

        return self::SUCCESS;
    }
}
