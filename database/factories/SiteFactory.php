<?php

namespace Database\Factories;

use App\Enums\SiteStatus;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Site> */
class SiteFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => 'sitio-'.fake()->unique()->numberBetween(1, 999999),
            'path' => 'tienda-'.fake()->unique()->numberBetween(1, 999999),
            'status' => SiteStatus::Live,
            'settings' => [],
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['status' => SiteStatus::Draft]);
    }
}
