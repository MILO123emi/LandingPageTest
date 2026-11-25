<?php

namespace App\Http\Middleware;

use App\Models\Site;
use App\Support\CurrentSite;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageSite
{
    public function __construct(private CurrentSite $currentSite) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $site = $request->route('site');

        if (! $site instanceof Site || ! ($request->user()?->can('manage', $site) ?? false)) {
            abort(403);
        }

        $this->currentSite->set($site);

        return $next($request);
    }
}
