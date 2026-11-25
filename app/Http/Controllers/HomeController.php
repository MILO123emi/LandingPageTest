<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', ['sites' => Site::query()->live()->orderBy('name')->get()]);
    }
}
