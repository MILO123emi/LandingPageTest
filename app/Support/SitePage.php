<?php

namespace App\Support;

use App\Enums\SectionType;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Section;
use App\Models\Site;
use App\Support\Theme\ThemeTokens;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/**
 * Everything a public landing needs, loaded in a fixed number of queries.
 * Must run after ResolveSite, so the BelongsToSite scope limits every query to $site.
 */
final class SitePage
{
    /**
     * @param  Collection<int, Section>  $sections
     * @param  Collection<int, Media>  $media
     * @param  Collection<int, Product>  $featured
     * @param  Collection<int, Category>  $categories
     * @param  array<string, string>  $tokens
     */
    private function __construct(
        public readonly Site $site,
        public readonly Collection $sections,
        public readonly Collection $media,
        public readonly Collection $featured,
        public readonly Collection $categories,
        public readonly array $tokens,
    ) {}

    public static function load(Site $site): self
    {
        $sections = Section::query()->published()->get();
        $types = $sections->map(fn (Section $section): SectionType => $section->type);

        $featured = $types->contains(SectionType::FeaturedProducts)
            ? Product::query()->featured()->limit(8)->get()
            : new Collection;

        $categories = $types->contains(SectionType::Categories)
            ? Category::query()->visible()->get()
            : new Collection;

        $mediaIds = $sections->toBase()->flatMap(fn (Section $section): array => $section->mediaIds())
            ->merge($featured->pluck('media_id'))
            ->merge($categories->pluck('media_id'));

        return new self($site, $sections, self::loadMedia($site, $mediaIds), $featured, $categories, ThemeTokens::for($site));
    }

    /**
     * The frame of an inner page (catalog, product): header, footer and theme, plus the
     * media the page itself asks for. Landing sections are kept only for the footer text.
     *
     * @param  iterable<mixed>  $mediaIds
     */
    public static function shell(Site $site, iterable $mediaIds = []): self
    {
        $sections = Section::query()->published()->where('type', SectionType::Footer)->get();

        return new self($site, $sections, self::loadMedia($site, collect($mediaIds)), new Collection, new Collection, ThemeTokens::for($site));
    }

    /**
     * @param  Collection<int, mixed>  $ids
     * @return Collection<int, Media>
     */
    private static function loadMedia(Site $site, Collection $ids): Collection
    {
        $ids = $ids->push($site->settings['logo_media_id'] ?? null)
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        return $ids->isEmpty() ? new Collection : Media::query()->whereIn('id', $ids)->get()->keyBy('id');
    }

    /**
     * Body sections that have something to show, in order. The footer is left out because
     * the layout always prints it last.
     *
     * @return Collection<int, Section>
     */
    public function body(): Collection
    {
        return $this->sections->filter(fn (Section $section): bool => match ($section->type) {
            SectionType::Footer => false,
            SectionType::FeaturedProducts => $this->featured->isNotEmpty(),
            SectionType::Categories => $this->categories->isNotEmpty(),
            SectionType::Gallery => ((array) ($section->content['items'] ?? [])) !== [],
            default => true,
        })->values();
    }

    public function catalogUrl(?Category $category = null): ?string
    {
        if ($category !== null && Route::has('site.catalog.category')) {
            return route('site.catalog.category', [$this->site->path, $category->slug]);
        }

        return Route::has('site.catalog') ? route('site.catalog', $this->site->path) : null;
    }

    public function productUrl(Product $product): ?string
    {
        return Route::has('site.product') ? route('site.product', [$this->site->path, $product->slug]) : null;
    }

    public function mediaFor(mixed $id): ?Media
    {
        return is_numeric($id) ? $this->media->get((int) $id) : null;
    }

    public function logo(): ?Media
    {
        return $this->mediaFor($this->site->settings['logo_media_id'] ?? null);
    }

    public function section(SectionType $type): ?Section
    {
        return $this->sections->first(fn (Section $section): bool => $section->type === $type);
    }

    public function has(SectionType $type): bool
    {
        return $this->section($type) !== null;
    }

    public function description(): string
    {
        $description = $this->site->settings['description'] ?? null;

        if (is_string($description) && ! PendingText::isPending($description)) {
            return $description;
        }

        $subtitle = $this->section(SectionType::Hero)?->content['subtitle'] ?? '';

        return is_string($subtitle) && ! PendingText::isPending($subtitle) ? $subtitle : $this->site->name;
    }
}
