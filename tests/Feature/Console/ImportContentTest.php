<?php

use App\Enums\SiteStatus;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Scopes\SiteScope;
use App\Models\Section;
use App\Models\Site;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->root = storage_path('framework/testing/content-'.uniqid());
    File::ensureDirectoryExists($this->root.'/tienda/img');
    config(['vitrina.content_path' => $this->root]);

    $png = imagecreatetruecolor(40, 30);
    imagepng($png, $this->root.'/tienda/img/hero.png');
    imagepng(imagecreatetruecolor(20, 20), $this->root.'/tienda/img/producto.png');

    $this->write = function (array $overrides = []): void {
        File::put($this->root.'/tienda/import.json', json_encode(array_replace_recursive([
            'site' => ['name' => 'Tienda Importada', 'slug' => 'tienda-importada', 'path' => 'importada', 'status' => 'live'],
            'settings' => ['whatsapp' => '50499990000', 'logo' => 'img/hero.png'],
            'theme' => ['palette' => 'original', 'colors' => ['primary' => '#D70512']],
            'sections' => [
                ['type' => 'hero', 'content' => ['title' => 'Bienvenidos', 'background_media_id' => ['file' => 'img/hero.png', 'alt' => 'Fachada']]],
                ['type' => 'gallery', 'content' => ['items' => [['media_id' => ['file' => 'img/hero.png', 'alt' => 'Foto']]]]],
            ],
            'categories' => [['slug' => 'despensa', 'name' => 'Despensa', 'image' => 'img/producto.png']],
            'products' => [['slug' => 'cafe', 'name' => 'Café', 'category' => 'despensa', 'image' => 'img/producto.png', 'featured' => true]],
        ], $overrides)));
    };
});

afterEach(fn () => File::deleteDirectory($this->root));

test('it publishes a shop with media, sections and catalogue', function () {
    ($this->write)();

    $this->artisan('vitrina:import-content', ['folder' => 'tienda', '--publish' => true])->assertSuccessful();

    $site = Site::where('slug', 'tienda-importada')->firstOrFail();
    expect($site->status)->toBe(SiteStatus::Live)
        ->and($site->theme['colors']['primary'])->toBe('#D70512')
        ->and($site->settings['logo_media_id'])->toBeInt();

    $hero = Section::withoutGlobalScope(SiteScope::class)->where('site_id', $site->id)->where('type', 'hero')->firstOrFail();
    $media = Media::withoutGlobalScope(SiteScope::class)->findOrFail($hero->content['background_media_id']);
    expect($media->site_id)->toBe($site->id)->and($media->width)->toBe(40)->and($media->alt)->toBe('Fachada');
    Storage::disk('public')->assertExists($media->path_original);

    $this->get('/importada')->assertOk()->assertSee('Bienvenidos')->assertSee('Fachada');
});

test('running it twice does not duplicate anything', function () {
    ($this->write)();
    $this->artisan('vitrina:import-content', ['folder' => 'tienda', '--publish' => true])->assertSuccessful();
    $this->artisan('vitrina:import-content', ['folder' => 'tienda', '--publish' => true])->assertSuccessful();

    $count = fn (string $model): int => $model::withoutGlobalScope(SiteScope::class)->count();
    expect($count(Section::class))->toBe(2)
        ->and($count(Media::class))->toBe(2)
        ->and($count(Category::class))->toBe(1)
        ->and($count(Product::class))->toBe(1);
});

test('without --publish it stays as a draft the public cannot see', function () {
    ($this->write)();

    $this->artisan('vitrina:import-content', ['folder' => 'tienda'])->assertSuccessful();

    $site = Site::where('slug', 'tienda-importada')->firstOrFail();
    expect($site->status)->toBe(SiteStatus::Draft)->and($site->draft_theme['colors']['primary'])->toBe('#D70512');
    $this->get('/importada')->assertNotFound();
});

test('it refuses files outside the content folder and bad folder names', function () {
    ($this->write)(['sections' => [['type' => 'hero', 'content' => ['background_media_id' => ['file' => '../../../../composer.json']]]]]);

    $this->artisan('vitrina:import-content', ['folder' => 'tienda', '--publish' => true])->assertFailed();
    $this->artisan('vitrina:import-content', ['folder' => '../tienda'])->assertFailed();
    expect(Site::where('slug', 'tienda-importada')->exists())->toBeFalse();
});

test('unknown section types fail without saving anything', function () {
    ($this->write)(['sections' => [['type' => 'banner_libre', 'content' => []]]]);

    $this->artisan('vitrina:import-content', ['folder' => 'tienda', '--publish' => true])->assertFailed();
    expect(Site::where('slug', 'tienda-importada')->exists())->toBeFalse();
});
