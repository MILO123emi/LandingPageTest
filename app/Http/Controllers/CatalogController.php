<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\Catalog;
use App\Support\CurrentSite;
use App\Support\SitePage;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public catalog. Categories and products are looked up by slug here, after ResolveSite,
 * instead of with route model binding: binding runs before the site is known.
 */
class CatalogController extends Controller
{
    public function index(Request $request, CurrentSite $currentSite): View
    {
        return $this->render($request, $currentSite, null);
    }

    public function category(Request $request, CurrentSite $currentSite, string $sitePath, string $categorySlug): View
    {
        $category = Catalog::findCategory($categorySlug) ?? abort(404);

        return $this->render($request, $currentSite, $category);
    }

    private function render(Request $request, CurrentSite $currentSite, ?Category $category): View
    {
        $term = Catalog::term($request->query('q'));
        $products = Catalog::page($category, $term);

        return view('site.catalog', [
            'page' => SitePage::shell($currentSite->require(), collect($products->items())->map(fn (Product $product) => $product->media_id)),
            'category' => $category,
            'categories' => Catalog::categories(),
            'products' => $products,
            'term' => $term,
        ]);
    }
}
