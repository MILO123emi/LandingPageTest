<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->word().' '.fake()->word().' '.fake()->word();

        return [
            'category_id' => Category::factory(),
            'site_id' => fn (array $attributes): int => Category::withoutGlobalScopes()->whereKey($attributes['category_id'])->firstOrFail()->site_id,
            'name' => Str::ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'is_visible' => true,
            'is_available' => true,
            'is_featured' => false,
            'position' => fake()->numberBetween(1, 50),
        ];
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
