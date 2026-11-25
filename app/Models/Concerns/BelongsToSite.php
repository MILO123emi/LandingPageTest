<?php

namespace App\Models\Concerns;

use App\Models\Scopes\SiteScope;
use App\Models\Site;
use App\Support\CurrentSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

trait BelongsToSite
{
    public static function bootBelongsToSite(): void
    {
        static::addGlobalScope(new SiteScope);

        static::creating(function (Model $model): void {
            $current = app(CurrentSite::class)->get();
            $siteId = $model->getAttribute('site_id');

            if ($current === null) {
                if ($siteId === null) {
                    throw new LogicException('Cannot create a site-owned model without a site.');
                }

                return;
            }

            if ($siteId !== null && (int) $siteId !== $current->getKey()) {
                throw new LogicException('Cannot create a model for another site.');
            }

            $model->setAttribute('site_id', $current->getKey());
        });
    }

    /** @return BelongsTo<Site, $this> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
