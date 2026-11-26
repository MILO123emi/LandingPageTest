<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $site_id
 * @property string $disk
 * @property string $path_original
 * @property array<string, string>|null $variants
 * @property string|null $lqip
 * @property int $width
 * @property int $height
 * @property string $mime
 * @property int $size_bytes
 * @property float $focal_x
 * @property float $focal_y
 * @property string $alt
 * @property array<string, mixed>|null $palette
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['site_id', 'disk', 'path_original', 'variants', 'lqip', 'width', 'height', 'mime', 'size_bytes', 'focal_x', 'focal_y', 'alt', 'palette'])]
class Media extends Model
{
    use BelongsToSite;

    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'variants' => 'array',
            'palette' => 'array',
            'width' => 'integer',
            'height' => 'integer',
            'size_bytes' => 'integer',
            'focal_x' => 'float',
            'focal_y' => 'float',
        ];
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path_original);
    }

    /** Width-keyed srcset from the WebP variants, empty until the image pipeline creates them. */
    public function srcset(): string
    {
        $disk = Storage::disk($this->disk);

        return collect($this->variants ?? [])
            ->sortKeys()
            ->map(fn (string $path, int|string $width): string => $disk->url($path).' '.$width.'w')
            ->implode(', ');
    }

    /** CSS object-position from the focal point, clamped to 0–100 %. */
    public function objectPosition(): string
    {
        $x = round(max(0, min(1, $this->focal_x)) * 100, 1);
        $y = round(max(0, min(1, $this->focal_y)) * 100, 1);

        return "{$x}% {$y}%";
    }
}
