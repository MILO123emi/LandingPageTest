<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $site_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $media_id
 * @property int $position
 * @property bool $is_visible
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['site_id', 'name', 'slug', 'description', 'media_id', 'position', 'is_visible'])]
class Category extends Model
{
    use BelongsToSite;

    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    /** @return BelongsTo<Media, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @param Builder<Category> $query */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_visible', true)->orderBy('position')->orderBy('id');
    }
}
