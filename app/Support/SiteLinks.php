<?php

namespace App\Support;

use App\Models\Site;

/**
 * Outbound links built from owner-editable data. Each one is rebuilt or host-checked
 * here, so a bad value is dropped instead of printed (PLAN §11).
 */
final class SiteLinks
{
    private const MAPS_HOSTS = ['www.google.com', 'google.com', 'maps.google.com', 'maps.app.goo.gl', 'goo.gl'];

    private const SOCIAL_HOSTS = [
        'instagram' => ['instagram.com', 'www.instagram.com'],
        'facebook' => ['facebook.com', 'www.facebook.com', 'm.facebook.com'],
        'tiktok' => ['tiktok.com', 'www.tiktok.com'],
    ];

    public static function whatsappNumber(Site $site): ?string
    {
        $number = $site->settings['whatsapp'] ?? null;

        return is_string($number) && preg_match('/^504\d{8}$/', $number) === 1 ? $number : null;
    }

    public static function whatsapp(Site $site, ?string $message = null): ?string
    {
        $number = self::whatsappNumber($site);

        if ($number === null) {
            return null;
        }

        return 'https://wa.me/'.$number.($message !== null ? '?text='.rawurlencode($message) : '');
    }

    /** "9999-0000" style, the way people write it in Honduras. */
    public static function whatsappLabel(Site $site): ?string
    {
        $number = self::whatsappNumber($site);

        return $number !== null ? substr($number, 3, 4).'-'.substr($number, 7) : null;
    }

    public static function social(Site $site, string $network): ?string
    {
        $url = $site->settings[$network] ?? null;

        return is_string($url) && self::hostIn($url, self::SOCIAL_HOSTS[$network] ?? []) ? $url : null;
    }

    /** "@usuario" for an Instagram profile URL. */
    public static function handle(string $url): string
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        return '@'.explode('/', $path)[0];
    }

    /** @param array<string, mixed> $location */
    public static function maps(array $location): ?string
    {
        $url = $location['maps_url'] ?? null;

        if (is_string($url) && self::hostIn($url, self::MAPS_HOSTS)) {
            return $url;
        }

        [$lat, $lng] = self::coordinates($location);

        return $lat !== null ? "https://www.google.com/maps/search/?api=1&query={$lat}%2C{$lng}" : null;
    }

    /** @param array<string, mixed> $location */
    public static function mapEmbed(array $location): ?string
    {
        $embed = $location['maps_embed_url'] ?? null;

        if (is_string($embed) && str_starts_with($embed, 'https://www.google.com/maps/embed?')) {
            return $embed;
        }

        [$lat, $lng] = self::coordinates($location);

        return $lat !== null ? "https://www.google.com/maps?q={$lat},{$lng}&z=17&output=embed" : null;
    }

    /**
     * @param  array<string, mixed>  $location
     * @return array{0: float|null, 1: float|null}
     */
    private static function coordinates(array $location): array
    {
        $lat = $location['lat'] ?? null;
        $lng = $location['lng'] ?? null;

        if (! is_numeric($lat) || ! is_numeric($lng) || abs((float) $lat) > 90 || abs((float) $lng) > 180) {
            return [null, null];
        }

        return [(float) $lat, (float) $lng];
    }

    /** @param list<string> $hosts */
    private static function hostIn(string $url, array $hosts): bool
    {
        return str_starts_with($url, 'https://') && in_array(strtolower((string) parse_url($url, PHP_URL_HOST)), $hosts, true);
    }
}
