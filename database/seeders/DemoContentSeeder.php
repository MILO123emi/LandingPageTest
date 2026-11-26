<?php

namespace Database\Seeders;

use App\Enums\SectionType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Section;
use App\Models\Site;
use App\Support\CurrentSite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Generic sections, categories and products for the demo sites, so no page looks empty
 * while developing (PLAN §5 "Contenido semilla"). Real shops come from vitrina:import-content.
 */
class DemoContentSeeder extends Seeder
{
    /** @var array<string, array{theme: array<string, string>, settings: array<string, mixed>, kind: string}> */
    private const SITES = [
        'demo-verde' => [
            'theme' => ['palette' => 'matcha', 'font' => 'warm', 'gradient' => 'original', 'overlay' => '#14160C'],
            'settings' => ['tagline' => 'Productos naturales para tu bienestar.', 'home_position' => 1],
            'kind' => 'natural',
        ],
        'demo-mercado' => [
            'theme' => ['palette' => 'ocean', 'font' => 'clean', 'gradient' => 'original', 'overlay' => '#061E10'],
            'settings' => ['tagline' => 'Tu mercado de confianza.', 'home_position' => 2],
            'kind' => 'market',
        ],
        'demo-super' => [
            'theme' => ['palette' => 'citrus', 'font_original' => 'bold', 'gradient' => 'noche', 'door_button' => 'accent'],
            'settings' => ['tagline' => 'Precios bajos todos los días.', 'home_position' => 3],
            'kind' => 'market',
        ],
        'demo-bazar' => [
            'theme' => ['palette' => 'terracotta', 'font' => 'friendly', 'gradient' => 'atardecer'],
            'settings' => ['tagline' => 'Todo para tu hogar.', 'home_position' => 4],
            'kind' => 'home',
        ],
    ];

    /** @var array<string, array{highlights: list<array{icon: string, text: string}>, categories: list<string>, products: list<array{0: string, 1: string}>}> */
    private const KINDS = [
        'natural' => [
            'highlights' => [['icon' => 'leaf', 'text' => 'Vitaminas y suplementos'], ['icon' => 'cup', 'text' => 'Tés e infusiones'], ['icon' => 'sprout', 'text' => 'Hierbas y raíces']],
            'categories' => ['Suplementos', 'Tés', 'Hierbas', 'Cuidado personal'],
            'products' => [['Té verde en polvo', 'Bolsa de 100 g.'], ['Magnesio', 'Cápsulas de 400 mg.'], ['Manzanilla', 'Flores secas para infusión.'], ['Aceite de coco', 'Frasco de 500 ml.'], ['Jarabe de miel', 'Para la garganta.'], ['Sal de baño', 'Sin fragancia.'], ['Vitamina C', 'Tabletas masticables.'], ['Jengibre', 'Raíz seca en trozos.']],
        ],
        'market' => [
            'highlights' => [['icon' => 'basket', 'text' => 'Frutas y verduras frescas'], ['icon' => 'tag', 'text' => 'Buenos precios'], ['icon' => 'clock', 'text' => 'Abierto todos los días']],
            'categories' => ['Frutas y verduras', 'Lácteos', 'Despensa', 'Limpieza'],
            'products' => [['Queso fresco', 'Libra.'], ['Café molido', 'Tueste medio, 400 g.'], ['Salsa picante', 'Botella de 150 ml.'], ['Arroz', 'Bolsa de 5 lb.'], ['Frijoles rojos', 'Bolsa de 2 lb.'], ['Detergente', 'Bolsa de 1 kg.'], ['Leche entera', 'Galón.'], ['Aguacates', 'Por unidad.']],
        ],
        'home' => [
            'highlights' => [['icon' => 'home', 'text' => 'Todo para la cocina'], ['icon' => 'sparkle', 'text' => 'Limpieza del hogar'], ['icon' => 'tag', 'text' => 'Precios de mayoreo']],
            'categories' => ['Cocina', 'Vidrio', 'Limpieza', 'Organización'],
            'products' => [['Juego de vasos', 'Seis piezas de vidrio.'], ['Recipientes con tapa', 'Set de cinco tamaños.'], ['Escoba', 'Cerdas suaves.'], ['Cubeta', 'Diez litros.'], ['Organizador', 'Para despensa.'], ['Jarra', 'Dos litros, con tapa.'], ['Paños de cocina', 'Paquete de tres.'], ['Canasta plástica', 'Para ropa.']],
        ],
    ];

    public function run(): void
    {
        $current = app(CurrentSite::class);

        foreach (self::SITES as $path => $data) {
            $site = Site::query()->where('path', $path)->first();

            if ($site === null) {
                continue;
            }

            $site->update([
                'theme' => $data['theme'],
                'settings' => [...($site->settings ?? []), ...$data['settings'], 'whatsapp' => '50400000000', 'show_prices' => false],
            ]);

            $current->set($site);
            $this->sections($site, self::KINDS[$data['kind']]);
            $this->catalog($site, self::KINDS[$data['kind']]);
        }

        $current->clear();
    }

    /** @param array{highlights: list<array{icon: string, text: string}>, categories: list<string>, products: list<array{0: string, 1: string}>} $kind */
    private function sections(Site $site, array $kind): void
    {
        $sections = [
            [SectionType::Hero, ['title' => 'Bienvenido a '.$site->name, 'subtitle' => $site->settings['tagline'] ?? '', 'cta_label' => 'Ver productos', 'cta_target' => 'catalog']],
            [SectionType::About, ['title' => 'Una tienda pensada para ti', 'body' => 'Este es un texto de ejemplo. Aquí la dueña cuenta qué vende, desde cuándo y por qué vale la pena visitarla.', 'highlights' => $kind['highlights']]],
            [SectionType::HowItWorks, ['title' => 'Cómo comprar', 'steps' => [
                ['title' => 'Mira los productos', 'body' => 'Revisa el catálogo y anota lo que te interesa.'],
                ['title' => 'Escríbenos por WhatsApp', 'body' => 'Te confirmamos si lo tenemos antes de que vengas.'],
                ['title' => 'Pasa por la tienda', 'body' => 'La dirección y el horario están más abajo.'],
            ]]],
            [SectionType::FeaturedProducts, ['title' => 'Productos destacados', 'subtitle' => 'Algunos de los productos que encuentras aquí.']],
            [SectionType::Categories, ['title' => 'Categorías']],
            [SectionType::Location, ['title' => 'Visítanos', 'address' => '[PENDIENTE: dirección de ejemplo]', 'hours' => [['days' => 'Lunes a sábado', 'time' => '8:00 a. m. a 7:00 p. m.'], ['days' => 'Domingo', 'time' => '9:00 a. m. a 1:00 p. m.']]]],
            [SectionType::Contact, ['title' => '¿Buscas algo en especial?', 'body' => 'Escríbenos y te decimos si lo tenemos, antes de que hagas el viaje.']],
            [SectionType::Footer, ['tagline' => '']],
        ];

        foreach ($sections as $position => [$type, $content]) {
            Section::create(['site_id' => $site->id, 'type' => $type, 'position' => $position + 1, 'is_visible' => true, 'content' => $content]);
        }
    }

    /** @param array{highlights: list<array{icon: string, text: string}>, categories: list<string>, products: list<array{0: string, 1: string}>} $kind */
    private function catalog(Site $site, array $kind): void
    {
        $categories = [];

        foreach ($kind['categories'] as $position => $name) {
            $categories[] = Category::create(['site_id' => $site->id, 'name' => $name, 'slug' => Str::slug($name), 'position' => $position + 1]);
        }

        foreach ($kind['products'] as $position => [$name, $description]) {
            Product::create([
                'site_id' => $site->id,
                'category_id' => $categories[$position % count($categories)]->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $description,
                'is_featured' => true,
                'position' => $position + 1,
            ]);
        }
    }
}
