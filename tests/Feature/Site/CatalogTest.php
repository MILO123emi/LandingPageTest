<?php

use App\Enums\SectionType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Section;
use App\Models\Site;

function catalogSite(array $settings = []): Site
{
    return Site::factory()->create(['path' => 'demo-verde', 'name' => 'Tienda Demo Verde', 'settings' => $settings]);
}

function shelf(Site $site, array $attributes = []): Category
{
    return Category::factory()->for($site)->create($attributes);
}

function item(Category $category, array $attributes = []): Product
{
    return Product::factory()->for($category)->create(['site_id' => $category->site_id, ...$attributes]);
}

test('the catalog lists visible products and leaves out hidden ones and other sites', function () {
    $site = catalogSite();
    $other = Site::factory()->create(['path' => 'demo-ajena']);
    $granos = shelf($site, ['name' => 'Granos', 'slug' => 'granos']);
    $oculta = shelf($site, ['name' => 'Oculta', 'slug' => 'oculta', 'is_visible' => false]);

    item($granos, ['name' => 'Arroz blanco', 'slug' => 'arroz-blanco']);
    item($granos, ['name' => 'Producto escondido', 'slug' => 'escondido', 'is_visible' => false]);
    item($oculta, ['name' => 'En categoría oculta', 'slug' => 'en-oculta']);
    item(shelf($other, ['slug' => 'granos']), ['name' => 'Arroz ajeno', 'slug' => 'arroz-ajeno']);

    $this->get('/demo-verde/catalogo')->assertOk()
        ->assertSee('Arroz blanco')
        ->assertSee('1 producto')
        ->assertSee('/demo-verde/producto/arroz-blanco', false)
        ->assertDontSee('Producto escondido')
        ->assertDontSee('En categoría oculta')
        ->assertDontSee('Arroz ajeno');
});

test('the catalog shows 24 products per page with numbered pages', function () {
    $site = catalogSite();
    $granos = shelf($site);
    foreach (range(1, 30) as $n) {
        item($granos, ['name' => sprintf('Producto %02d', $n), 'slug' => "producto-{$n}", 'position' => $n]);
    }

    $this->get('/demo-verde/catalogo')->assertOk()
        ->assertSee('30 productos')
        ->assertSee('Producto 24')
        ->assertDontSee('Producto 25')
        ->assertSee('aria-label="Página 2"', false)
        ->assertSee('rel="next"', false);

    $this->get('/demo-verde/catalogo?page=2')->assertOk()
        ->assertSee('Producto 30')
        ->assertDontSee('Producto 24');

    $this->get('/demo-verde/catalogo?page=9')->assertOk()->assertSee('Esta página no existe');
});

test('search filters by name, keeps the term in page links and treats wildcards as text', function () {
    $site = catalogSite();
    $granos = shelf($site);
    item($granos, ['name' => 'Café molido', 'slug' => 'cafe-molido']);
    item($granos, ['name' => 'Azúcar morena', 'slug' => 'azucar']);
    foreach (range(1, 25) as $n) {
        item($granos, ['name' => "Café en grano {$n}", 'slug' => "cafe-grano-{$n}"]);
    }

    $this->get('/demo-verde/catalogo?q='.urlencode('  Café   molido '))->assertOk()
        ->assertSee('1 resultado para “Café molido”', false)
        ->assertDontSee('Azúcar morena');

    $this->get('/demo-verde/catalogo?q=Café')->assertOk()
        ->assertSee('q=Caf%C3%A9&amp;page=2', false);

    $this->get('/demo-verde/catalogo?q=%25')->assertOk()
        ->assertSee('No encontramos “%”', false);

    $this->get('/demo-verde/catalogo?q[]=x')->assertOk()->assertSee('27 productos');
});

test('a category page filters its products and unknown or hidden categories return 404', function () {
    $site = catalogSite();
    $other = Site::factory()->create(['path' => 'demo-ajena']);
    $granos = shelf($site, ['name' => 'Granos', 'slug' => 'granos']);
    $bebidas = shelf($site, ['name' => 'Bebidas', 'slug' => 'bebidas']);
    shelf($site, ['slug' => 'oculta', 'is_visible' => false]);
    shelf($other, ['slug' => 'ajena']);
    item($granos, ['name' => 'Frijol rojo', 'slug' => 'frijol']);
    item($bebidas, ['name' => 'Jugo de naranja', 'slug' => 'jugo']);

    $this->get('/demo-verde/catalogo/granos')->assertOk()
        ->assertSee('Frijol rojo')
        ->assertDontSee('Jugo de naranja')
        ->assertSee('aria-current="page">Granos', false);

    $this->get('/demo-verde/catalogo/oculta')->assertNotFound();
    $this->get('/demo-verde/catalogo/ajena')->assertNotFound();
    $this->get('/demo-verde/catalogo/no-existe')->assertNotFound();
});

test('the product page shows the product and up to four related ones from its category', function () {
    $site = catalogSite(['whatsapp' => '50499998888', 'show_prices' => true]);
    $granos = shelf($site, ['name' => 'Granos', 'slug' => 'granos']);
    $bebidas = shelf($site, ['slug' => 'bebidas']);
    item($granos, ['name' => 'Arroz blanco', 'slug' => 'arroz', 'price' => '42.50', 'unit' => 'lb', 'description' => 'Grano largo.']);
    foreach (range(1, 5) as $n) {
        item($granos, ['name' => "Relacionado {$n}", 'slug' => "rel-{$n}", 'position' => $n]);
    }
    item($bebidas, ['name' => 'Jugo de naranja', 'slug' => 'jugo']);

    $this->get('/demo-verde/producto/arroz')->assertOk()
        ->assertSee('<h1>Arroz blanco</h1>', false)
        ->assertSee('Grano largo.')
        ->assertSee('L 42.50 / lb')
        ->assertSee('https://wa.me/50499998888?text='.rawurlencode('Hola, ¿tienen Arroz blanco?'), false)
        ->assertSee('/demo-verde/catalogo/granos', false)
        ->assertSeeInOrder(['Relacionado 1', 'Relacionado 4'])
        ->assertDontSee('Relacionado 5')
        ->assertDontSee('Jugo de naranja');
});

test('hidden products, products in hidden categories and other sites products return 404', function () {
    $site = catalogSite();
    $other = Site::factory()->create(['path' => 'demo-ajena']);
    item(shelf($site), ['slug' => 'escondido', 'is_visible' => false]);
    item(shelf($site, ['is_visible' => false]), ['slug' => 'en-oculta']);
    item(shelf($other), ['slug' => 'ajeno']);

    $this->get('/demo-verde/producto/escondido')->assertNotFound();
    $this->get('/demo-verde/producto/en-oculta')->assertNotFound();
    $this->get('/demo-verde/producto/ajeno')->assertNotFound();
    $this->get('/demo-ajena/producto/ajeno')->assertOk();
});

test('prices only show when the site turns them on', function () {
    $site = catalogSite(['show_prices' => false]);
    item(shelf($site), ['name' => 'Arroz', 'slug' => 'arroz', 'price' => '42.50']);

    $this->get('/demo-verde/catalogo')->assertOk()->assertDontSee('L 42.50');
    $this->get('/demo-verde/producto/arroz')->assertOk()->assertDontSee('L 42.50');
});

test('catalog pages of a draft site are hidden from visitors', function () {
    $site = Site::factory()->draft()->create(['path' => 'demo-verde']);
    item(shelf($site), ['slug' => 'arroz']);

    $this->get('/demo-verde/catalogo')->assertNotFound();
    $this->get('/demo-verde/producto/arroz')->assertNotFound();
});

test('product text is escaped in the catalog and on the product page', function () {
    $site = catalogSite();
    item(shelf($site), ['name' => '<script>alert(1)</script>', 'slug' => 'malo', 'description' => '<img src=x onerror=alert(1)>']);

    $this->get('/demo-verde/catalogo')->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    $this->get('/demo-verde/producto/malo')->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x', false);
});

test('the landing links to the catalog and to each featured product', function () {
    $site = catalogSite();
    Section::factory()->for($site)->type(SectionType::FeaturedProducts, ['title' => 'Destacados'])->create();
    item(shelf($site), ['name' => 'Arroz', 'slug' => 'arroz', 'is_featured' => true]);

    $this->get('/demo-verde')->assertOk()
        ->assertSee('href="'.url('/demo-verde/catalogo').'"', false)
        ->assertSee('href="'.url('/demo-verde/producto/arroz').'"', false);
});
