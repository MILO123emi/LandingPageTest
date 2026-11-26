<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Sites\PublishSite;
use App\Data\AdminSiteData;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Site;
use Inertia\Inertia;
use Inertia\Response;

class SiteDashboardController extends Controller
{
    public function __invoke(Site $site): Response
    {
        return Inertia::render('admin/SiteDashboard', [
            'site' => AdminSiteData::fromSite($site),
            'hasChanges' => PublishSite::hasChanges($site),
            'counts' => [
                'products' => Product::query()->count(),
                'categories' => Category::query()->count(),
            ],
        ]);
    }
}
