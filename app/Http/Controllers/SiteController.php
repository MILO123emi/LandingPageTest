<?php

namespace App\Http\Controllers;

use App\Support\CurrentSite;
use App\Support\SitePage;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function __invoke(CurrentSite $currentSite): View
    {
        return view('site.show', ['page' => SitePage::load($currentSite->require())]);
    }
}
