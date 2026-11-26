<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->word().' '.fake()->word();

        return [
            'site_id' => Site::factory(),
            'name' => Str::ucfirst($name),
            'slug' => Str::slug($name),
            'position' => fake()->numberBetween(1, 50),
            'is_visible' => true,
        ];
    }
}
