<?php

use App\Models\Site;
use App\Support\CurrentSite;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\FixtureNote;

beforeEach(function () {
    Schema::create('fixture_notes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('site_id')->constrained()->cascadeOnDelete();
        $table->string('body');
        $table->timestamps();
    });

    [$this->siteA, $this->siteB] = Site::factory()->count(2)->create();
    $this->current = app(CurrentSite::class);
});

test('queries only see rows of the current site', function () {
    $this->current->set($this->siteA);
    FixtureNote::create(['body' => 'A']);
    $this->current->set($this->siteB);
    FixtureNote::create(['body' => 'B']);

    $this->current->set($this->siteA);

    expect(FixtureNote::pluck('body')->all())->toBe(['A']);
});

test('guessing an id of another site finds nothing', function () {
    $this->current->set($this->siteB);
    $foreign = FixtureNote::create(['body' => 'B']);

    $this->current->set($this->siteA);

    expect(FixtureNote::find($foreign->id))->toBeNull();
});

test('without a current site queries return nothing', function () {
    $this->current->set($this->siteA);
    FixtureNote::create(['body' => 'A']);
    $this->current->clear();

    expect(FixtureNote::count())->toBe(0);
});

test('creating assigns the current site', function () {
    $this->current->set($this->siteA);

    expect(FixtureNote::create(['body' => 'A'])->site_id)->toBe($this->siteA->id);
});

test('creating with another site id is refused', function () {
    $this->current->set($this->siteA);

    FixtureNote::create(['body' => 'x', 'site_id' => $this->siteB->id]);
})->throws(LogicException::class);

test('creating without current site and without site id is refused', function () {
    FixtureNote::create(['body' => 'x']);
})->throws(LogicException::class);

test('console code can bypass the scope explicitly', function () {
    FixtureNote::create(['body' => 'A', 'site_id' => $this->siteA->id]);
    FixtureNote::create(['body' => 'B', 'site_id' => $this->siteB->id]);

    expect(FixtureNote::withoutGlobalScope(App\Models\Scopes\SiteScope::class)->count())->toBe(2);
});
