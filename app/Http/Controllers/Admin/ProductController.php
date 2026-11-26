<?php

namespace App\Http\Controllers\Admin;

use App\Data\AdminSiteData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Site;
use App\Support\Catalog;
use App\Support\MediaStore;
use App\Support\Ordering;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    private const PER_PAGE = 25;

    private const FLAGS = ['is_visible', 'is_available', 'is_featured'];

    public function index(Request $request, Site $site): Response
    {
        $term = Catalog::term($request->query('q'));
        $categoryId = $request->integer('categoria') ?: null;

        $products = Product::query()
            ->with(['category:id,name', 'media'])
            ->when($term !== '', fn (Builder $query) => $query->whereRaw("products.name LIKE ? ESCAPE '!'", ['%'.Catalog::escapeLike($term).'%']))
            ->when($categoryId !== null, fn (Builder $query) => $query->where('category_id', $categoryId))
            ->orderBy('category_id')
            ->orderBy('position')
            ->orderBy('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category->name,
                'price' => $product->price,
                'unit' => $product->unit,
                'photo' => $product->media?->url(),
                'isVisible' => $product->is_visible,
                'isAvailable' => $product->is_available,
                'isFeatured' => $product->is_featured,
                'editUrl' => route('admin.products.edit', [$site, $product->id]),
            ]);

        return Inertia::render('admin/products/Index', [
            'site' => AdminSiteData::fromSite($site),
            'products' => $products,
            'categories' => $this->categoryOptions(),
            'filters' => ['q' => $term, 'categoria' => $categoryId],
            'canReorder' => $categoryId !== null && $term === '',
        ]);
    }

    public function create(Site $site): Response
    {
        return Inertia::render('admin/products/Form', [
            'site' => AdminSiteData::fromSite($site),
            'product' => null,
            'categories' => $this->categoryOptions(),
            'defaultUnit' => $site->draft_settings['unit'] ?? $site->settings['unit'] ?? null,
        ]);
    }

    public function store(ProductRequest $request, Site $site): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'remove_photo']);
        $category = Product::query()->where('category_id', $data['category_id']);

        $product = new Product([
            ...$data,
            'slug' => Ordering::uniqueSlug(Product::query(), $request->string('name')->value(), 130),
            'position' => Ordering::next($category),
        ]);

        if ($request->hasFile('photo')) {
            $product->media_id = MediaStore::store($site, $request->file('photo'), $product->name)->id;
        }

        $product->save();
        $this->toast('Producto creado.');

        return to_route('admin.products.index', $site);
    }

    public function edit(Site $site, int $product): Response
    {
        $model = $this->find($product)->load('media');

        return Inertia::render('admin/products/Form', [
            'site' => AdminSiteData::fromSite($site),
            'product' => [
                'id' => $model->id,
                'name' => $model->name,
                'categoryId' => $model->category_id,
                'description' => $model->description,
                'price' => $model->price,
                'unit' => $model->unit,
                'photo' => $model->media?->url(),
                'isVisible' => $model->is_visible,
                'isAvailable' => $model->is_available,
                'isFeatured' => $model->is_featured,
                'publicUrl' => route('site.product', [$site->path, $model->slug]),
            ],
            'categories' => $this->categoryOptions(),
            'defaultUnit' => null,
        ]);
    }

    public function update(ProductRequest $request, Site $site, int $product): RedirectResponse
    {
        $model = $this->find($product);
        $data = $request->safe()->except(['photo', 'remove_photo']);

        if ((int) $data['category_id'] !== $model->category_id) {
            $data['position'] = Ordering::next(Product::query()->where('category_id', $data['category_id']));
        }

        $model->fill($data);

        if ($request->hasFile('photo')) {
            $model->media_id = MediaStore::store($site, $request->file('photo'), $model->name)->id;
        } elseif ($request->boolean('remove_photo')) {
            $model->media_id = null;
        }

        $model->save();
        $this->toast('Producto guardado.');

        return to_route('admin.products.index', $site);
    }

    public function toggle(Request $request, Site $site, int $product): RedirectResponse
    {
        $validated = $request->validate([
            'field' => ['required', Rule::in(self::FLAGS)],
            'value' => ['required', 'boolean'],
        ]);

        $this->find($product)->update([$validated['field'] => $request->boolean('value')]);

        return back();
    }

    public function move(Request $request, Site $site, int $product): RedirectResponse
    {
        $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);
        $model = $this->find($product);

        Ordering::move(Product::query()->where('category_id', $model->category_id), $model, $request->string('direction')->value());

        return back();
    }

    public function destroy(Site $site, int $product): RedirectResponse
    {
        $this->find($product)->delete();
        $this->toast('Producto borrado.');

        return to_route('admin.products.index', $site);
    }

    private function find(int $id): Product
    {
        $product = Product::query()->findOrFail($id);
        Gate::authorize('manage', $product);

        return $product;
    }

    /** @return list<array{id: int, name: string}> */
    private function categoryOptions(): array
    {
        return array_values(Category::query()
            ->orderBy('position')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => ['id' => $category->id, 'name' => $category->name])
            ->all());
    }
}
