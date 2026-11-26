<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Support\CurrentSite;

class ProductPolicy
{
    /** The record must belong to the site in the URL, and the user must manage that site. */
    public function manage(User $user, Product $model): bool
    {
        $site = app(CurrentSite::class)->get();

        return $site !== null
            && $model->site_id === $site->id
            && $user->canManage($site);
    }
}
