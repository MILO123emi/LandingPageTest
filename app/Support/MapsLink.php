<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Reads coordinates out of a pasted Google Maps link. Short "Compartir" links are
 * followed hop by hop, and only while every hop stays on a Google host.
 */
final class MapsLink
{
    private const LONG_HOSTS = ['www.google.com', 'google.com', 'maps.google.com', 'www.google.hn', 'google.hn'];

    private const SHORT_HOSTS = ['maps.app.goo.gl', 'goo.gl'];

    private const MAX_HOPS = 4;

    public static function isGoogle(string $url): bool
    {
        return self::host($url, [...self::LONG_HOSTS, ...self::SHORT_HOSTS]);
    }

    /**
     * Coordinates in a long link, most precise format first: the place pin
     * (!3d…!4d…), then a query (?query=lat,lng), then the map centre (@lat,lng).
     *
     * @return array{lat: float, lng: float}|null
     */
    public static function coordinates(string $url): ?array
    {
        if (! self::host($url, self::LONG_HOSTS)) {
            return null;
        }

        $url = rawurldecode($url);
        $number = '(-?\d{1,3}(?:\.\d+)?)';
        $patterns = [
            "/!3d{$number}!4d{$number}/",
            "/[?&](?:query|q|ll|destination)={$number},\s*{$number}/",
            "/@{$number},{$number}/",
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $match) === 1) {
                $lat = (float) $match[1];
                $lng = (float) $match[2];

                if (abs($lat) <= 90 && abs($lng) <= 180) {
                    return ['lat' => $lat, 'lng' => $lng];
                }
            }
        }

        return null;
    }

    /**
     * Coordinates from any Google Maps link, resolving short links first.
     *
     * @return array{lat: float, lng: float}|null
     */
    public static function resolve(string $url): ?array
    {
        $url = trim($url);

        for ($hop = 0; $hop <= self::MAX_HOPS; $hop++) {
            if (self::host($url, self::LONG_HOSTS)) {
                return self::coordinates($url);
            }

            if (! self::host($url, self::SHORT_HOSTS)) {
                return null;
            }

            $next = self::redirectTarget($url);

            if ($next === null) {
                return null;
            }

            $url = $next;
        }

        return null;
    }

    private static function redirectTarget(string $url): ?string
    {
        try {
            $response = Http::withOptions(['allow_redirects' => false])
                ->connectTimeout(3)
                ->timeout(4)
                ->get($url);
        } catch (ConnectionException) {
            return null;
        }

        $location = $response->header('Location');

        return $response->redirect() && $location !== '' ? $location : null;
    }

    /** @param list<string> $hosts */
    private static function host(string $url, array $hosts): bool
    {
        return str_starts_with($url, 'https://')
            && in_array(strtolower((string) parse_url($url, PHP_URL_HOST)), $hosts, true);
    }
}
