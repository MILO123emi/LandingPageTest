<?php

use App\Enums\SectionType;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Section;
use App\Models\Site;

function landing(array $settings = [], array $theme = []): Site
{
    return Site::factory()->create(['path' => 'demo-verde', 'name' => 'Tienda Demo Verde', 'settings' => $settings, 'theme' => $theme]);
}

test('sections render in order and hidden ones are left out', function () {
    $site = landing();
    Section::factory()->for($site)->type(SectionType::About, ['title' => 'Segunda sección'])->create(['position' => 2]);
    Section::factory()->for($site)->type(SectionType::Hero, ['title' => 'Primera sección'])->create(['position' => 1]);
    Section::factory()->for($site)->type(SectionType::Contact, ['title' => 'Sección oculta'])->hidden()->create(['position' => 3]);

    $this->get('/demo-verde')->assertOk()
        ->assertSeeInOrder(['Primera sección', 'Segunda sección'])
        ->assertDontSee('Sección oculta');
});

test('a landing never shows sections, products or media of another site', function () {
    $site = landing();
    $other = Site::factory()->create(['path' => 'demo-ajena']);
    Section::factory()->for($site)->type(SectionType::FeaturedProducts, ['title' => 'Destacados'])->create();
    Section::factory()->for($other)->type(SectionType::About, ['title' => 'Texto de la otra tienda'])->create();
    $foreign = Media::factory()->for($other)->create(['path_original' => 'sites/9/media/ajena/original.webp']);
    Section::factory()->for($site)->type(SectionType::Hero, ['title' => 'Hola', 'background_media_id' => $foreign->id])->create();
    Product::factory()->featured()->for(Category::factory()->for($other))->create(['name' => 'Producto ajeno', 'site_id' => $other->id]);

    $this->get('/demo-verde')->assertOk()
        ->assertDontSee('Texto de la otra tienda')
        ->assertDontSee('Producto ajeno')
        ->assertDontSee('sites/9/media/ajena', false);
});

test('owner text is always escaped and pending markers are highlighted', function () {
    $site = landing();
    Section::factory()->for($site)->type(SectionType::About, ['title' => '<script>alert(1)</script>', 'body' => 'Abierto [PENDIENTE: horario]'])->create();

    $this->get('/demo-verde')->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;', false)
        ->assertSee('<span class="pendiente">[PENDIENTE: horario]</span>', false);
});

test('featured products and categories only appear when there is something to show', function () {
    $site = landing();
    Section::factory()->for($site)->type(SectionType::FeaturedProducts, ['title' => 'Lo más pedido'])->create();
    Section::factory()->for($site)->type(SectionType::Categories, ['title' => 'Nuestras categorías'])->create();

    $this->get('/demo-verde')->assertDontSee('Lo más pedido')->assertDontSee('Nuestras categorías');

    $category = Category::factory()->for($site)->create(['name' => 'Despensa']);
    Product::factory()->featured()->for($category)->create(['name' => 'Café molido', 'site_id' => $site->id]);
    Product::factory()->for($category)->create(['name' => 'Producto no destacado', 'site_id' => $site->id]);

    $this->get('/demo-verde')->assertSee('Lo más pedido')->assertSee('Café molido')
        ->assertSee('Nuestras categorías')->assertSee('Despensa')
        ->assertDontSee('Producto no destacado');
});

test('prices stay out of the html while show_prices is off', function () {
    $site = landing(['show_prices' => false]);
    Section::factory()->for($site)->type(SectionType::FeaturedProducts, [])->create();
    $category = Category::factory()->for($site)->create();
    Product::factory()->featured()->for($category)->create(['site_id' => $site->id, 'price' => 123.45]);

    $this->get('/demo-verde')->assertDontSee('123.45');

    $site->update(['settings' => ['show_prices' => true]]);
    $this->get('/demo-verde')->assertSee('L 123.45');
});

test('only valid whatsapp numbers and google maps links are printed', function () {
    $site = landing(['whatsapp' => '50499990000', 'instagram' => 'https://evil.example.com/x']);
    Section::factory()->for($site)->type(SectionType::Location, ['maps_url' => 'https://evil.example.com/maps', 'lat' => 15.77, 'lng' => -86.79])->create();

    $this->get('/demo-verde')->assertOk()
        ->assertSee('https://wa.me/50499990000', false)
        ->assertSee('9999-0000')
        ->assertDontSee('evil.example.com', false)
        ->assertSee('https://www.google.com/maps?q=15.77,-86.79&amp;z=17&amp;output=embed', false);

    $site->update(['settings' => ['whatsapp' => '123']]);
    $this->get('/demo-verde')->assertDontSee('wa.me', false);
});

test('the theme prints catalogue colours and rejects anything that is not a hex colour', function () {
    $site = landing([], ['palette' => 'original', 'colors' => ['primary' => '#D70512', 'bg' => 'red;}body{display:none'], 'font' => 'warm']);
    Section::factory()->for($site)->type(SectionType::Hero, ['title' => 'Hola'])->create();

    $this->get('/demo-verde')->assertOk()
        ->assertSee('--primary: #D70512', false)
        ->assertSee('--bg: #FFFFFF', false)
        ->assertSee('--font-head: Fraunces Variable', false)
        ->assertDontSee('display:none', false);
});

test('the home page shows each live shop with its own colours', function () {
    Site::factory()->create(['name' => 'Tienda Uno', 'theme' => ['palette' => 'ocean'], 'settings' => ['tagline' => 'Lema uno']]);
    Site::factory()->draft()->create(['name' => 'Tienda Oculta']);

    $this->get('/')->assertOk()
        ->assertSee('Tienda Uno')->assertSee('Lema uno')
        ->assertSee('--primary: #0B5C8C', false)
        ->assertDontSee('Tienda Oculta');
});

test('public pages ask search engines not to index them until launch', function () {
    landing();

    $this->get('/demo-verde')->assertSee('<meta name="robots" content="noindex">', false);

    config(['vitrina.indexable' => true]);
    $this->get('/demo-verde')->assertDontSee('noindex', false);
});
