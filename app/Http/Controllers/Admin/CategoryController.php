<?php

namespace App\Http\Controllers\Admin;

use App\Data\AdminSiteData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\Site;
use App\Support\Ordering;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Site $site): Response
    {
        return Inertia::render('admin/categories/Index', [
            'site' => AdminSiteData::fromSite($site),
            'categories' => Category::query()
                ->withCount('products')
                ->orderBy('position')
                ->orderBy('id')
                ->get()
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'isVisible' => $category->is_visible,
                    'productsCount' => (int) $category->getAttribute('products_count'),
                    'publicUrl' => route('site.catalog.category', [$site->path, $category->slug]),
                ]),
        ]);
    }

    public function store(CategoryRequest $request, Site $site): RedirectResponse
    {
        Category::query()->create([
            ...$request->validated(),
            'slug' => Ordering::uniqueSlug(Category::query(), $request->string('name')->value(), 90),
            'position' => Ordering::next(Category::query()),
        ]);

        $this->toast('Categoría creada.');

        return back();
    }

    public function update(CategoryRequest $request, Site $site, int $category): RedirectResponse
    {
        $model = $this->find($category);
        $model->update($request->validated());

        $this->toast('Categoría guardada.');

        return back();
    }

    public function move(Request $request, Site $site, int $category): RedirectResponse
    {
        $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);

        Ordering::move(Category::query(), $this->find($category), $request->string('direction')->value());

        return back();
    }

    public function destroy(Site $site, int $category): RedirectResponse
    {
        $model = $this->find($category);

        if ($model->products()->exists()) {
            return back()->withErrors(['category' => 'Esta categoría todavía tiene productos. Movelos a otra categoría o borralos primero.']);
        }

        $model->delete();

        $this->toast('Categoría borrada.');

        return back();
    }

    private function find(int $id): Category
    {
        $category = Category::query()->findOrFail($id);
        Gate::authorize('manage', $category);

        return $category;
    }
}
