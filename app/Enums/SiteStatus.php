<?php

namespace App\Enums;

enum SiteStatus: string
{
    case Draft = 'draft';
    case Live = 'live';
}
