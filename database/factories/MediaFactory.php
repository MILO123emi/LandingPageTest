<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Media> */
class MediaFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'disk' => 'public',
            'path_original' => 'sites/0/media/'.Str::uuid().'/original.webp',
            'width' => 1600,
            'height' => 1200,
            'mime' => 'image/webp',
            'size_bytes' => 120_000,
            'focal_x' => 0.5,
            'focal_y' => 0.5,
            'alt' => fake()->sentence(4),
        ];
    }
}
