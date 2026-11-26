<?php

namespace App\Http\Controllers;

use App\Enums\SectionType;
use App\Models\Media;
use App\Models\Scopes\SiteScope;
use App\Models\Section;
use App\Models\Site;
use App\Support\Theme\ThemeTokens;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * The home page shows every live shop at once, so it reads across sites. The global
     * site scope is lifted on purpose and every query is limited to the live site ids.
     */
    public function __invoke(): View
    {
        $sites = Site::query()->live()->orderBy('name')->get()
            ->sortBy(fn (Site $site): int => (int) ($site->settings['home_position'] ?? 99))
            ->values();
        $siteIds = $sites->modelKeys();

        $heroes = Section::query()->withoutGlobalScope(SiteScope::class)
            ->whereIn('site_id', $siteIds)
            ->where('type', SectionType::Hero)
            ->where('is_visible', true)
            ->get()
            ->keyBy('site_id');

        $mediaIds = $heroes->toBase()->map(fn (Section $hero): mixed => $hero->content['background_media_id'] ?? null)
            ->merge($sites->toBase()->map(fn (Site $site): mixed => $site->settings['logo_media_id'] ?? null))
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->values();

        $media = Media::query()->withoutGlobalScope(SiteScope::class)
            ->whereIn('site_id', $siteIds)
            ->whereIn('id', $mediaIds)
            ->get();

        $doors = $sites->map(function (Site $site) use ($heroes, $media): array {
            $hero = $heroes->get($site->id);
            $find = fn (mixed $id): ?Media => is_numeric($id)
                ? $media->first(fn (Media $item): bool => $item->id === (int) $id && $item->site_id === $site->id)
                : null;

            return [
                'site' => $site,
                'tokens' => ThemeTokens::for($site),
                'background' => $find($hero?->content['background_media_id'] ?? null),
                'logo' => $find($site->settings['logo_media_id'] ?? null),
                'tagline' => $site->settings['tagline'] ?? null,
            ];
        });

        return view('home', ['doors' => $doors]);
    }
}
