<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Public catalog queries. Must run after ResolveSite, so the BelongsToSite scope limits
 * every query to the current site.
 */
final class Catalog
{
    public const PER_PAGE = 24;

    public const RELATED = 4;

    private const MAX_TERM = 80;

    /**
     * Products a visitor may see: visible and inside a visible category.
     *
     * @return Builder<Product>
     */
    public static function products(): Builder
    {
        return Product::query()
            ->visible()
            ->whereHas('category', fn (Builder $query) => $query->where('is_visible', true));
    }

    /** @return LengthAwarePaginator<int, Product> */
    public static function page(?Category $category, string $term): LengthAwarePaginator
    {
        return self::products()
            ->with('category')
            ->when($category, fn (Builder $query, Category $category) => $query->where('category_id', $category->id))
            ->when($term !== '', fn (Builder $query) => $query->whereRaw("products.name LIKE ? ESCAPE '!'", ['%'.self::escapeLike($term).'%']))
            ->orderBy('position')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /**
     * Visible categories that have at least one visible product, with that count.
     *
     * @return Collection<int, Category>
     */
    public static function categories(): Collection
    {
        return Category::query()
            ->visible()
            ->withCount(['products' => fn (Builder $query) => $query->where('is_visible', true)])
            ->get()
            ->filter(fn (Category $category): bool => $category->products_count > 0)
            ->values();
    }

    public static function findCategory(string $slug): ?Category
    {
        return Category::query()->visible()->where('slug', $slug)->first();
    }

    public static function findProduct(string $slug): ?Product
    {
        return self::products()->with('category')->where('slug', $slug)->first();
    }

    /** @return Collection<int, Product> */
    public static function related(Product $product): Collection
    {
        return self::products()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->orderBy('position')
            ->orderBy('name')
            ->limit(self::RELATED)
            ->get();
    }

    /** Collapses spaces and trims the search box text to a sane length. */
    public static function term(mixed $value): string
    {
        return is_string($value) ? Str::limit(Str::squish($value), self::MAX_TERM, '') : '';
    }

    private static function escapeLike(string $term): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
    }
}
