<?php

namespace App\Support;

use App\Models\Site;
use LogicException;

final class CurrentSite
{
    private ?Site $site = null;

    public function set(Site $site): void
    {
        $this->site = $site;
    }

    public function get(): ?Site
    {
        return $this->site;
    }

    public function require(): Site
    {
        return $this->site ?? throw new LogicException('No current site resolved.');
    }

    public function clear(): void
    {
        $this->site = null;
    }
}
