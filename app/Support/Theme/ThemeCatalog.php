<?php

namespace App\Support\Theme;

/**
 * Closed lists the owners pick from (PLAN §5 "Dirección visual" and §7). Nothing outside
 * these lists, plus validated hex colours, ever reaches the page CSS.
 */
final class ThemeCatalog
{
    /** Colour roles every palette defines, in CSS custom property order. */
    public const ROLES = ['bg', 'surface', 'ink', 'muted', 'primary', 'on_primary', 'accent', 'accent_2'];

    /** @var array<string, array{label: string, colors: array<string, string>}> */
    public const PALETTES = [
        'matcha' => ['label' => 'Verde matcha', 'colors' => ['bg' => '#FAFAF5', 'surface' => '#EEF1E4', 'ink' => '#1E2617', 'muted' => '#56604B', 'primary' => '#3F5F2A', 'on_primary' => '#FFFFFF', 'accent' => '#C9D8A5', 'accent_2' => '#7A5536']],
        'terracotta' => ['label' => 'Terracota', 'colors' => ['bg' => '#FFFAF5', 'surface' => '#F6EAE0', 'ink' => '#2B1A12', 'muted' => '#6B5446', 'primary' => '#A4462A', 'on_primary' => '#FFFFFF', 'accent' => '#F2C38B', 'accent_2' => '#5B3A29']],
        'ocean' => ['label' => 'Océano', 'colors' => ['bg' => '#F7FBFD', 'surface' => '#E6F1F6', 'ink' => '#0D2333', 'muted' => '#4A6273', 'primary' => '#0B5C8C', 'on_primary' => '#FFFFFF', 'accent' => '#9EDCEB', 'accent_2' => '#0F2E4A']],
        'charcoal' => ['label' => 'Carbón', 'colors' => ['bg' => '#FFFFFF', 'surface' => '#F2F2F0', 'ink' => '#16181B', 'muted' => '#5B6068', 'primary' => '#1F2328', 'on_primary' => '#FFFFFF', 'accent' => '#E8B04A', 'accent_2' => '#6B7280']],
        'citrus' => ['label' => 'Cítrico', 'colors' => ['bg' => '#FFFDF5', 'surface' => '#FFF3D6', 'ink' => '#2A1D05', 'muted' => '#6A5A3A', 'primary' => '#A8420A', 'on_primary' => '#FFFFFF', 'accent' => '#FFD23F', 'accent_2' => '#2F6B2F']],
        'sand' => ['label' => 'Arena', 'colors' => ['bg' => '#FBF8F3', 'surface' => '#F1EADF', 'ink' => '#2A241C', 'muted' => '#6A6052', 'primary' => '#7A5C3A', 'on_primary' => '#FFFFFF', 'accent' => '#E7D3B0', 'accent_2' => '#3E4A3D']],
    ];

    /** @var array<string, array{label: string, head: string, body: string, weight: int, track: string, hero_weight: int}> */
    public const FONT_PAIRS = [
        'modern' => ['label' => 'Moderna', 'head' => 'Inter Variable, system-ui, sans-serif', 'body' => 'Inter Variable, system-ui, sans-serif', 'weight' => 800, 'track' => '-0.035em', 'hero_weight' => 800],
        'warm' => ['label' => 'Cálida', 'head' => 'Fraunces Variable, Georgia, serif', 'body' => 'Inter Variable, system-ui, sans-serif', 'weight' => 500, 'track' => '-0.02em', 'hero_weight' => 400],
        'clean' => ['label' => 'Limpia', 'head' => 'Manrope Variable, system-ui, sans-serif', 'body' => 'Manrope Variable, system-ui, sans-serif', 'weight' => 800, 'track' => '-0.03em', 'hero_weight' => 800],
        'elegant' => ['label' => 'Elegante', 'head' => 'Playfair Display Variable, Georgia, serif', 'body' => 'Inter Variable, system-ui, sans-serif', 'weight' => 600, 'track' => '-0.01em', 'hero_weight' => 600],
        'friendly' => ['label' => 'Amigable', 'head' => 'Nunito Variable, system-ui, sans-serif', 'body' => 'Nunito Variable, system-ui, sans-serif', 'weight' => 800, 'track' => '-0.02em', 'hero_weight' => 800],
        'classic' => ['label' => 'Clásica', 'head' => 'DM Serif Display, Georgia, serif', 'body' => 'DM Sans Variable, system-ui, sans-serif', 'weight' => 400, 'track' => '-0.01em', 'hero_weight' => 400],
        'bold' => ['label' => 'Fuerte', 'head' => 'Inter Variable, system-ui, sans-serif', 'body' => 'Inter Variable, system-ui, sans-serif', 'weight' => 900, 'track' => '-0.04em', 'hero_weight' => 900],
        'clean_inter' => ['label' => 'Limpia con Inter', 'head' => 'Manrope Variable, system-ui, sans-serif', 'body' => 'Inter Variable, system-ui, sans-serif', 'weight' => 800, 'track' => '-0.035em', 'hero_weight' => 800],
    ];

    /** Pairs the owner sees in the design panel; the rest only serve as a shop's "original" pair. */
    public const SELECTABLE_FONT_PAIRS = ['modern', 'warm', 'clean', 'elegant', 'friendly', 'classic'];

    /**
     * Hero gradients. All end dark where the title sits so white text stays readable.
     * `original` is built from the shop's own overlay colour instead.
     *
     * @var array<string, array{label: string, css: string|null}>
     */
    public const GRADIENTS = [
        'original' => ['label' => 'Original', 'css' => null],
        'suave' => ['label' => 'Suave', 'css' => 'linear-gradient(180deg, rgba(0,0,0,0) 45%, rgba(0,0,0,.6))'],
        'profundo' => ['label' => 'Profundo', 'css' => 'linear-gradient(180deg, rgba(0,0,0,.2) 0%, rgba(0,0,0,.86))'],
        'marca' => ['label' => 'Color de la marca', 'css' => 'linear-gradient(180deg, transparent 25%, color-mix(in srgb, var(--primary) 82%, black))'],
        'atardecer' => ['label' => 'Atardecer', 'css' => 'linear-gradient(165deg, rgba(150,60,10,.18) 15%, rgba(60,20,5,.84))'],
        'noche' => ['label' => 'Noche azul', 'css' => 'linear-gradient(180deg, rgba(10,20,50,.12) 25%, rgba(8,14,40,.86))'],
        'diagonal' => ['label' => 'Diagonal', 'css' => 'linear-gradient(30deg, rgba(0,0,0,.82) 10%, rgba(0,0,0,.1) 75%)'],
    ];

    public const DEFAULT_PALETTE = 'charcoal';

    public const DEFAULT_FONT_PAIR = 'modern';

    public const DEFAULT_OVERLAY = '#111111';
}
