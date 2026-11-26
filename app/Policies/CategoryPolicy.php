<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Support\CurrentSite;

class CategoryPolicy
{
    /** The record must belong to the site in the URL, and the user must manage that site. */
    public function manage(User $user, Category $model): bool
    {
        $site = app(CurrentSite::class)->get();

        return $site !== null
            && $model->site_id === $site->id
            && $user->canManage($site);
    }
}
