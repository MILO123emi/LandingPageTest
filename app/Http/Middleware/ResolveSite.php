<?php

namespace App\Http\Middleware;

use App\Enums\SiteStatus;
use App\Models\Site;
use App\Support\CurrentSite;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveSite
{
    public function __construct(private CurrentSite $currentSite) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->route('sitePath');
        $site = is_string($path) ? Site::query()->where('path', $path)->first() : null;

        if ($site === null) {
            abort(404);
        }

        if ($site->status !== SiteStatus::Live && ! ($request->user()?->canManage($site) ?? false)) {
            abort(404);
        }

        $this->currentSite->set($site);

        return $next($request);
    }
}
