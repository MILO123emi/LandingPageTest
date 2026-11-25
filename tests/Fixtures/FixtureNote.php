<?php

namespace Tests\Fixtures;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class FixtureNote extends Model
{
    use BelongsToSite;

    protected $table = 'fixture_notes';

    protected $guarded = [];
}
