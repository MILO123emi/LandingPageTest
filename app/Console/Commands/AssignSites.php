<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Models\User;
use App\Support\SecurityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssignSites extends Command
{
    protected $signature = 'vitrina:assign-sites {email} {--add=*} {--remove=*}';

    protected $description = 'Agregar o quitar tiendas de una cuenta';

    public function handle(): int
    {
        $user = User::query()->where('email', Str::lower(trim((string) $this->argument('email'))))->first();
        if ($user === null) {
            $this->error('No existe una cuenta con ese correo.');

            return self::FAILURE;
        }

        $add = (array) $this->option('add');
        $remove = (array) $this->option('remove');
        if ($add === [] && $remove === []) {
            $this->error('Indicá --add o --remove.');

            return self::FAILURE;
        }

        $requested = collect([...$add, ...$remove])->unique();
        $sites = Site::query()->whereIn('slug', $requested)->get();
        $unknown = $requested->diff($sites->pluck('slug'));
        if ($unknown->isNotEmpty()) {
            $this->error('No existen estas tiendas: '.$unknown->implode(', '));

            return self::FAILURE;
        }

        $final = $user->sites()->pluck('slug')->merge($add)->unique()->diff($remove);
        if ($final->isEmpty()) {
            $this->error('La cuenta quedaría sin tiendas.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($user, $sites, $add, $remove): void {
            $user->sites()->syncWithoutDetaching($sites->whereIn('slug', $add)->pluck('id'));
            $user->sites()->detach($sites->whereIn('slug', $remove)->pluck('id'));
        });
        SecurityLog::record('admin.sites_changed', $user);
        $this->info('Tiendas: '.$user->sites()->orderBy('slug')->pluck('slug')->implode(', '));

        return self::SUCCESS;
    }
}
