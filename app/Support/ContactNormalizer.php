<?php

namespace App\Support;

/**
 * Turns what an owner types ("9499-7224", "@tienda", a profile URL) into the one
 * stored shape that SiteLinks accepts. Returns null when the value cannot be trusted.
 */
final class ContactNormalizer
{
    private const SOCIAL = [
        'instagram' => ['hosts' => ['instagram.com', 'www.instagram.com', 'm.instagram.com'], 'base' => 'https://www.instagram.com/', 'at' => false],
        'facebook' => ['hosts' => ['facebook.com', 'www.facebook.com', 'm.facebook.com', 'web.facebook.com'], 'base' => 'https://www.facebook.com/', 'at' => false],
        'tiktok' => ['hosts' => ['tiktok.com', 'www.tiktok.com', 'm.tiktok.com'], 'base' => 'https://www.tiktok.com/', 'at' => true],
    ];

    /** "9499-7224", "+504 9499 7224" or "50494997224" → "50494997224". */
    public static function whatsapp(string $value): ?string
    {
        $digits = (string) preg_replace('/\D+/', '', $value);

        if (strlen($digits) === 8) {
            $digits = '504'.$digits;
        }

        return preg_match('/^504[2-9]\d{7}$/', $digits) === 1 ? $digits : null;
    }

    /** "@usuario", "usuario" or a profile URL on the network's own host → canonical profile URL. */
    public static function social(string $network, string $value): ?string
    {
        $config = self::SOCIAL[$network] ?? null;
        $value = trim($value);

        if ($config === null || $value === '') {
            return null;
        }

        $looksLikeUrl = str_contains($value, '://') || str_contains($value, '/') || in_array(strtolower($value), $config['hosts'], true);

        if ($looksLikeUrl) {
            $url = str_contains($value, '://') ? $value : 'https://'.$value;
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));

            if (! in_array($host, $config['hosts'], true)) {
                return null;
            }

            $handle = explode('/', trim((string) parse_url($url, PHP_URL_PATH), '/'))[0];
        } else {
            $handle = $value;
        }

        $handle = ltrim($handle, '@');

        if (preg_match('/^[A-Za-z0-9._-]{1,60}$/', $handle) !== 1) {
            return null;
        }

        return $config['base'].($config['at'] ? '@' : '').$handle.($config['at'] ? '' : '/');
    }
}
