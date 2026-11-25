<?php

use App\Models\Site;
use App\Models\User;

test('the seeder creates generic demo sites and two local admins', function () {
    $this->seed();

    expect(Site::count())->toBe(4)
        ->and(Site::pluck('path')->every(fn (string $path) => str_starts_with($path, 'demo-')))->toBeTrue();

    $admin1 = User::where('email', 'admin1@example.com')->firstOrFail();
    $admin2 = User::where('email', 'admin2@example.com')->firstOrFail();

    expect($admin1->sites()->pluck('path')->sort()->values()->all())->toBe(['demo-mercado', 'demo-verde'])
        ->and($admin2->sites()->pluck('path')->sort()->values()->all())->toBe(['demo-bazar', 'demo-super']);
});

test('the seeder does nothing outside local and testing', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->seed();

    expect(Site::count())->toBe(0)->and(User::count())->toBe(0);
});
