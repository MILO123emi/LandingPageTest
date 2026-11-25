<?php

namespace App\Http\Controllers\Admin;

use App\Data\AdminSiteData;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminHomeController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $sites = $request->user()->sites()->orderBy('name')->get();

        if ($sites->count() === 1) {
            return redirect()->route('admin.sites.show', $sites->first());
        }

        return Inertia::render('admin/Sites', [
            'sites' => $sites->map(AdminSiteData::fromSite(...))->values(),
        ]);
    }
}
