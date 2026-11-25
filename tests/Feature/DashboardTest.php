<?php

use App\Models\User;

test('guests get a not found response', function () {
    $response = $this->get(route('dashboard'));
    $response->assertNotFound();
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});
