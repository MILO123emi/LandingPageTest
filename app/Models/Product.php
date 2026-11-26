<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $site_id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $media_id
 * @property string|null $price
 * @property string|null $unit
 * @property bool $is_visible
 * @property bool $is_available
 * @property bool $is_featured
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['site_id', 'category_id', 'name', 'slug', 'description', 'media_id', 'price', 'unit', 'is_visible', 'is_available', 'is_featured', 'position'])]
class Product extends Model
{
    use BelongsToSite;

    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_visible' => 'boolean',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Media, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /** @param Builder<Product> $query */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    /** @param Builder<Product> $query */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_visible', true)->where('is_featured', true)->orderBy('position')->orderBy('id');
    }
}
