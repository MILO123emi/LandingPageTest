<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Catalog;
use App\Support\CurrentSite;
use App\Support\SitePage;
use Illuminate\View\View;

/** Public product page. Looked up by slug after ResolveSite, see CatalogController. */
class ProductController extends Controller
{
    public function __invoke(CurrentSite $currentSite, string $sitePath, string $productSlug): View
    {
        $product = Catalog::findProduct($productSlug) ?? abort(404);
        $related = Catalog::related($product);

        return view('site.product', [
            'page' => SitePage::shell($currentSite->require(), $related->map(fn (Product $item) => $item->media_id)->push($product->media_id)),
            'product' => $product,
            'related' => $related,
        ]);
    }
}
