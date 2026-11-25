<?php

namespace App\Data;

use App\Models\Site;

final class AdminSiteData
{
    /** @return array{slug: string, name: string, path: string, status: string, adminUrl: string, publicUrl: string} */
    public static function fromSite(Site $site): array
    {
        return [
            'slug' => $site->slug,
            'name' => $site->name,
            'path' => $site->path,
            'status' => $site->status->value,
            'adminUrl' => route('admin.sites.show', $site),
            'publicUrl' => url($site->path),
        ];
    }
}
