<?php

namespace App\Support\Theme;

use App\Models\Site;

/**
 * Turns a site's theme JSON into CSS custom properties. Every value is either taken from
 * ThemeCatalog or is a validated #RRGGBB colour, so the output is safe inside <style>.
 *
 * Theme JSON (sites.theme / sites.draft_theme):
 *   palette        "original" | ThemeCatalog::PALETTES key
 *   colors         the shop's own colours, used when palette = original
 *   font           "original" | ThemeCatalog::SELECTABLE_FONT_PAIRS key
 *   font_original  ThemeCatalog::FONT_PAIRS key the shop started with
 *   gradient       ThemeCatalog::GRADIENTS key
 *   overlay        #RRGGBB used by the "original" gradient
 *   door_button    "primary" | "accent", colour of the button on the home page card
 */
final class ThemeTokens
{
    /** @return array<string, string> CSS custom property => value */
    public static function for(Site $site, bool $draft = false): array
    {
        $theme = ($draft ? $site->draft_theme : null) ?? $site->theme ?? [];

        return self::fromTheme($theme);
    }

    /**
     * @param  array<string, mixed>  $theme
     * @return array<string, string>
     */
    public static function fromTheme(array $theme): array
    {
        $colors = self::colors($theme);
        $font = self::fontPair($theme);
        $overlay = self::hex($theme['overlay'] ?? null) ?? ThemeCatalog::DEFAULT_OVERLAY;

        $tokens = [];

        foreach (ThemeCatalog::ROLES as $role) {
            $tokens['--'.str_replace('_', '-', $role)] = $colors[$role];
        }

        $doorAccent = ($theme['door_button'] ?? 'primary') === 'accent';

        return $tokens + [
            '--font-head' => $font['head'],
            '--font-body' => $font['body'],
            '--head-weight' => (string) $font['weight'],
            '--head-track' => $font['track'],
            '--hero-weight' => (string) $font['hero_weight'],
            '--hero-overlay' => self::gradient($theme, $overlay),
            '--door-bg' => $doorAccent ? 'var(--accent)' : 'var(--primary)',
            '--door-ink' => $doorAccent ? 'var(--ink)' : 'var(--on-primary)',
        ];
    }

    /** @param array<string, string> $tokens */
    public static function inline(array $tokens): string
    {
        return collect($tokens)->map(fn (string $value, string $name): string => "{$name}: {$value}")->implode('; ');
    }

    public static function hex(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value) === 1 ? strtoupper($value) : null;
    }

    /**
     * @param  array<string, mixed>  $theme
     * @return array<string, string>
     */
    private static function colors(array $theme): array
    {
        $palette = $theme['palette'] ?? 'original';
        $fallback = ThemeCatalog::PALETTES[ThemeCatalog::DEFAULT_PALETTE]['colors'];

        if (is_string($palette) && isset(ThemeCatalog::PALETTES[$palette])) {
            return ThemeCatalog::PALETTES[$palette]['colors'];
        }

        $own = is_array($theme['colors'] ?? null) ? $theme['colors'] : [];
        $colors = [];

        foreach (ThemeCatalog::ROLES as $role) {
            $colors[$role] = self::hex($own[$role] ?? null) ?? $fallback[$role];
        }

        return $colors;
    }

    /**
     * @param  array<string, mixed>  $theme
     * @return array{label: string, head: string, body: string, weight: int, track: string, hero_weight: int}
     */
    private static function fontPair(array $theme): array
    {
        $font = $theme['font'] ?? 'original';

        if (is_string($font) && in_array($font, ThemeCatalog::SELECTABLE_FONT_PAIRS, true)) {
            return ThemeCatalog::FONT_PAIRS[$font];
        }

        $original = $theme['font_original'] ?? null;

        return is_string($original) && isset(ThemeCatalog::FONT_PAIRS[$original])
            ? ThemeCatalog::FONT_PAIRS[$original]
            : ThemeCatalog::FONT_PAIRS[ThemeCatalog::DEFAULT_FONT_PAIR];
    }

    /** @param array<string, mixed> $theme */
    private static function gradient(array $theme, string $overlay): string
    {
        $key = $theme['gradient'] ?? 'original';
        $css = is_string($key) ? (ThemeCatalog::GRADIENTS[$key]['css'] ?? null) : null;

        if ($css !== null) {
            return $css;
        }

        [$r, $g, $b] = array_map('hexdec', str_split(substr($overlay, 1), 2));

        return "linear-gradient(180deg, rgba({$r},{$g},{$b},.05) 30%, rgba({$r},{$g},{$b},.76))";
    }
}
