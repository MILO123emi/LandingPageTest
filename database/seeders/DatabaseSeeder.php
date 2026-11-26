<?php

namespace Database\Seeders;

use App\Enums\SiteStatus;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Seeder demo omitido: los datos reales se cargan con vitrina:import-content.');

            return;
        }

        $sites = [];

        foreach ([
            ['name' => 'Tienda Demo Verde', 'slug' => 'tienda-demo-verde', 'path' => 'demo-verde'],
            ['name' => 'Mercado Demo', 'slug' => 'mercado-demo', 'path' => 'demo-mercado'],
            ['name' => 'Super Demo', 'slug' => 'super-demo', 'path' => 'demo-super'],
            ['name' => 'Bazar Demo', 'slug' => 'bazar-demo', 'path' => 'demo-bazar'],
        ] as $attributes) {
            $sites[$attributes['path']] = Site::create([
                ...$attributes,
                'status' => SiteStatus::Live,
                'settings' => [],
            ]);
        }

        $admin1 = User::factory()->create([
            'name' => 'Admin Demo 1',
            'email' => 'admin1@example.com',
        ]);
        $admin1->sites()->attach([$sites['demo-verde']->id, $sites['demo-mercado']->id]);

        $admin2 = User::factory()->create([
            'name' => 'Admin Demo 2',
            'email' => 'admin2@example.com',
        ]);
        $admin2->sites()->attach([$sites['demo-super']->id, $sites['demo-bazar']->id]);

        $this->call(DemoContentSeeder::class);
    }
}
