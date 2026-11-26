<?php

namespace App\Support;

/**
 * Preset icons for highlights and steps (24×24, stroke). Owners pick a key; the SVG paths
 * are fixed markup from this file and never come from user input.
 */
final class Icons
{
    /** @var array<string, array{label: string, paths: string}> */
    public const SET = [
        'basket' => ['label' => 'Canasta', 'paths' => '<path d="M3 10h18l-2 10H5L3 10Z"/><path d="m8 10 4-6 4 6"/>'],
        'tag' => ['label' => 'Precio', 'paths' => '<path d="M3 12V4h8l10 10-8 8L3 12Z"/><circle cx="7.5" cy="7.5" r="1.5"/>'],
        'cart' => ['label' => 'Carrito', 'paths' => '<path d="M3 4h2l2.4 11h11L21 7H6.2"/><circle cx="9" cy="19.5" r="1.5"/><circle cx="17" cy="19.5" r="1.5"/>'],
        'leaf' => ['label' => 'Natural', 'paths' => '<path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15"/><path d="M5 19 13 11"/>'],
        'heart' => ['label' => 'Salud', 'paths' => '<path d="M12 20s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.5-7 10-7 10Z"/>'],
        'star' => ['label' => 'Calidad', 'paths' => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3Z"/>'],
        'clock' => ['label' => 'Horario', 'paths' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>'],
        'truck' => ['label' => 'Envíos', 'paths' => '<path d="M3 6h11v10H3z"/><path d="M14 9h4l3 3v4h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/>'],
        'shield' => ['label' => 'Confianza', 'paths' => '<path d="M12 3 5 6v5c0 4.5 3 8 7 10 4-2 7-5.5 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>'],
        'chat' => ['label' => 'WhatsApp', 'paths' => '<path d="M4 20l1.4-4.2A8 8 0 1 1 8.6 19L4 20Z"/>'],
        'cup' => ['label' => 'Café y té', 'paths' => '<path d="M4 9h12v5a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V9Z"/><path d="M16 10h1.5a2.5 2.5 0 0 1 0 5H16"/><path d="M8 3v3M12 3v3"/>'],
        'pin' => ['label' => 'Ubicación', 'paths' => '<path d="M12 21s-7-6-7-12a7 7 0 0 1 14 0c0 6-7 12-7 12Z"/><circle cx="12" cy="9" r="2.5"/>'],
        'sprout' => ['label' => 'Hierbas', 'paths' => '<path d="M12 21v-9"/><path d="M12 12c0-4-3-7-8-7 0 4 3 7 8 7Z"/><path d="M12 14c0-4 3-7 8-7 0 4-3 7-8 7Z"/>'],
        'sparkle' => ['label' => 'Limpieza', 'paths' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4"/><path d="m6 6 2.5 2.5M15.5 15.5 18 18M6 18l2.5-2.5M15.5 8.5 18 6"/>'],
        'home' => ['label' => 'Hogar', 'paths' => '<path d="M4 11 12 4l8 7"/><path d="M6 10v10h12V10"/><path d="M10 20v-5h4v5"/>'],
    ];

    public static function paths(?string $key): ?string
    {
        return $key !== null ? (self::SET[$key]['paths'] ?? null) : null;
    }
}
