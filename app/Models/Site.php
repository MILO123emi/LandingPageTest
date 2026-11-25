<?php

namespace App\Models;

use App\Enums\SiteStatus;
use App\Rules\SitePath;
use Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * @property int $id
 * @property string $slug
 * @property string $path
 * @property string $name
 * @property string|null $domain
 * @property list<string>|null $alt_domains
 * @property SiteStatus $status
 * @property string $locale
 * @property string $timezone
 * @property array<string, mixed>|null $settings
 * @property array<string, mixed>|null $draft_settings
 * @property array<string, mixed>|null $theme
 * @property array<string, mixed>|null $draft_theme
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['slug', 'path', 'name', 'domain', 'alt_domains', 'status', 'locale', 'timezone', 'settings', 'draft_settings', 'theme', 'draft_theme', 'published_at'])]
class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => SiteStatus::class,
            'alt_domains' => 'array',
            'settings' => 'array',
            'draft_settings' => 'array',
            'theme' => 'array',
            'draft_theme' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /** @param Builder<Site> $query */
    public function scopeLive(Builder $query): void
    {
        $query->where('status', SiteStatus::Live);
    }

    protected static function booted(): void
    {
        static::saving(function (Site $site): void {
            if (! SitePath::isAllowed((string) $site->path)) {
                throw new InvalidArgumentException("Ruta de sitio no permitida: {$site->path}");
            }
        });
    }
}
