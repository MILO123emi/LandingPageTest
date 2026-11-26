<?php

namespace Database\Factories;

use App\Enums\SectionType;
use App\Models\Section;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Section> */
class SectionFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'type' => SectionType::About,
            'position' => fake()->numberBetween(1, 50),
            'is_visible' => true,
            'content' => ['title' => fake()->sentence(3), 'body' => fake()->paragraph()],
        ];
    }

    /** @param array<string, mixed> $content */
    public function type(SectionType $type, array $content = []): static
    {
        return $this->state(['type' => $type, 'content' => $content]);
    }

    public function hidden(): static
    {
        return $this->state(['is_visible' => false]);
    }
}
