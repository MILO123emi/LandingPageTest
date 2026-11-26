<?php

use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    [$this->mine, $this->other] = Site::factory()->count(2)->create();
    $this->admin = User::factory()->create();
    $this->admin->sites()->attach($this->mine);

    $this->category = Category::factory()->create(['site_id' => $this->mine->id, 'position' => 1]);
    $this->foreignCategory = Category::factory()->create(['site_id' => $this->other->id]);
    $this->foreignProduct = Product::factory()->create(['category_id' => $this->foreignCategory->id]);
});

function productData(array $overrides = []): array
{
    return [
        'name' => 'Banano maduro',
        'category_id' => test()->category->id,
        'description' => 'Del día.',
        'price' => '12.50',
        'unit' => 'lb',
        'is_visible' => true,
        'is_available' => true,
        'is_featured' => false,
        ...$overrides,
    ];
}

test('the product list only shows products of the site in the URL', function () {
    Product::factory()->count(2)->create(['category_id' => $this->category->id]);

    $this->actingAs($this->admin)->get(route('admin.products.index', $this->mine))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/products/Index')
            ->has('products.data', 2)
            ->has('categories', 1));
});

test('an admin cannot open the catalog of another site', function (string $route) {
    $this->actingAs($this->admin)->get(route($route, $this->other))->assertForbidden();
})->with(['admin.products.index', 'admin.products.create', 'admin.categories.index', 'admin.settings.edit']);

test('a product of another site cannot be reached through my site', function () {
    $this->actingAs($this->admin);
    $params = [$this->mine, $this->foreignProduct->id];

    $this->get(route('admin.products.edit', $params))->assertNotFound();
    $this->put(route('admin.products.update', $params), productData())->assertNotFound();
    $this->patch(route('admin.products.toggle', $params), ['field' => 'is_visible', 'value' => false])->assertNotFound();
    $this->post(route('admin.products.move', $params), ['direction' => 'up'])->assertNotFound();
    $this->delete(route('admin.products.destroy', $params))->assertNotFound();

    expect($this->foreignProduct->fresh())->not->toBeNull()
        ->and(Product::withoutGlobalScopes()->find($this->foreignProduct->id)->is_visible)->toBeTrue();
});

test('a category of another site cannot be reached through my site', function () {
    $this->actingAs($this->admin);
    $params = [$this->mine, $this->foreignCategory->id];

    $this->put(route('admin.categories.update', $params), ['name' => 'Robada'])->assertNotFound();
    $this->delete(route('admin.categories.destroy', $params))->assertNotFound();

    expect(Category::withoutGlobalScopes()->find($this->foreignCategory->id)->name)->not->toBe('Robada');
});

test('a product cannot be put in a category of another site', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.products.store', $this->mine), productData(['category_id' => $this->foreignCategory->id]))
        ->assertSessionHasErrors('category_id');
});

test('creating a product gives it a unique slug, the site and the last position', function () {
    Product::factory()->create(['category_id' => $this->category->id, 'slug' => 'banano-maduro', 'position' => 7]);

    $this->actingAs($this->admin)
        ->post(route('admin.products.store', $this->mine), productData())
        ->assertRedirect(route('admin.products.index', $this->mine));

    $product = Product::withoutGlobalScopes()->where('slug', 'banano-maduro-2')->firstOrFail();

    expect($product->site_id)->toBe($this->mine->id)
        ->and($product->position)->toBe(8)
        ->and($product->price)->toBe('12.50');
});

test('a product photo is stored as media of the same site', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->post(route('admin.products.store', $this->mine), productData(['photo' => UploadedFile::fake()->image('foto.jpg', 800, 600)]))
        ->assertSessionHasNoErrors();

    $media = Media::withoutGlobalScopes()->sole();

    expect($media->site_id)->toBe($this->mine->id)
        ->and($media->path_original)->toStartWith("sites/{$this->mine->id}/media/")
        ->and($media->width)->toBe(800);
    Storage::disk('public')->assertExists($media->path_original);
});

test('files that are not photos are rejected', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->post(route('admin.products.store', $this->mine), productData(['photo' => UploadedFile::fake()->create('x.svg', 10, 'image/svg+xml')]))
        ->assertSessionHasErrors('photo');

    expect(Media::withoutGlobalScopes()->count())->toBe(0);
});

test('updating a product keeps the slug and can remove the photo', function () {
    $media = Media::factory()->create(['site_id' => $this->mine->id]);
    $product = Product::factory()->create(['category_id' => $this->category->id, 'slug' => 'viejo', 'media_id' => $media->id]);

    $this->actingAs($this->admin)
        ->put(route('admin.products.update', [$this->mine, $product->id]), productData(['name' => 'Nuevo nombre', 'remove_photo' => true]))
        ->assertSessionHasNoErrors();

    $product = Product::withoutGlobalScopes()->find($product->id);
    expect($product->name)->toBe('Nuevo nombre')
        ->and($product->slug)->toBe('viejo')
        ->and($product->media_id)->toBeNull();
});

test('the quick toggles only accept the three flags', function () {
    $product = Product::factory()->create(['category_id' => $this->category->id]);
    $this->actingAs($this->admin);

    $this->patch(route('admin.products.toggle', [$this->mine, $product->id]), ['field' => 'is_featured', 'value' => true])->assertSessionHasNoErrors();
    $this->patch(route('admin.products.toggle', [$this->mine, $product->id]), ['field' => 'site_id', 'value' => 1])->assertSessionHasErrors('field');

    expect(Product::withoutGlobalScopes()->find($product->id)->is_featured)->toBeTrue();
});

test('moving swaps neighbours even when positions start out equal', function () {
    [$a, $b, $c] = Product::factory()->count(3)->create(['category_id' => $this->category->id, 'position' => 0]);

    $this->actingAs($this->admin)->post(route('admin.products.move', [$this->mine, $c->id]), ['direction' => 'up']);

    $order = Product::withoutGlobalScopes()->where('category_id', $this->category->id)->orderBy('position')->orderBy('id')->pluck('id')->all();
    expect($order)->toBe([$a->id, $c->id, $b->id]);
});

test('categories are created at the end and reordered', function () {
    $this->actingAs($this->admin)->post(route('admin.categories.store', $this->mine), ['name' => 'Frutas frescas', 'is_visible' => true]);
    $new = Category::withoutGlobalScopes()->where('slug', 'frutas-frescas')->firstOrFail();

    expect($new->position)->toBe(2)->and($new->site_id)->toBe($this->mine->id);

    $this->post(route('admin.categories.move', [$this->mine, $new->id]), ['direction' => 'up']);

    expect(Category::withoutGlobalScopes()->where('site_id', $this->mine->id)->orderBy('position')->pluck('id')->all())
        ->toBe([$new->id, $this->category->id]);
});

test('a category with products cannot be deleted', function () {
    Product::factory()->create(['category_id' => $this->category->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.categories.destroy', [$this->mine, $this->category->id]))
        ->assertSessionHasErrors('category');

    expect(Category::withoutGlobalScopes()->find($this->category->id))->not->toBeNull();
});

test('an empty category can be deleted', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.categories.destroy', [$this->mine, $this->category->id]))
        ->assertSessionHasNoErrors();

    expect(Category::withoutGlobalScopes()->find($this->category->id))->toBeNull();
});
