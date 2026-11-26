<?php

namespace App\Actions\Sites;

use App\Models\Section;
use App\Models\Site;
use Illuminate\Support\Facades\DB;

/**
 * Copies every draft (settings, theme, sections) over the published values.
 * Revisions, discard and restore are added around this in F6.
 */
final class PublishSite
{
    public static function hasChanges(Site $site): bool
    {
        return $site->draft_settings !== null
            || $site->draft_theme !== null
            || Section::query()->where(fn ($query) => $query
                ->whereNotNull('draft_content')
                ->orWhereNotNull('draft_position')
                ->orWhereNotNull('draft_is_visible'))->exists();
    }

    public static function handle(Site $site): void
    {
        DB::transaction(function () use ($site): void {
            $site->forceFill([
                'settings' => $site->draft_settings ?? $site->settings,
                'theme' => $site->draft_theme ?? $site->theme,
                'draft_settings' => null,
                'draft_theme' => null,
                'published_at' => now(),
            ])->save();

            Section::query()->lockForUpdate()->get()->each(function (Section $section): void {
                $section->forceFill([
                    'content' => $section->draft_content ?? $section->content,
                    'position' => $section->draft_position ?? $section->position,
                    'is_visible' => $section->draft_is_visible ?? $section->is_visible,
                    'draft_content' => null,
                    'draft_position' => null,
                    'draft_is_visible' => null,
                ])->save();
            });
        });
    }
}
