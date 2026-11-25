<?php

use App\Models\Site;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    [$this->a1, $this->a2, $this->b1] = Site::factory()->count(3)->sequence(
        ['name' => 'Alfa'], ['name' => 'Beta'], ['name' => 'Gamma'],
    )->create();
    $this->admin = User::factory()->create();
    $this->admin->sites()->attach([$this->a1->id, $this->a2->id]);
});

test('guests get 404 instead of a redirect', function (string $method, string $uri) {
    $this->call($method, $uri)->assertNotFound();
})->with([
    ['GET', '/admin'],
    ['GET', '/admin/cualquiera'],
    ['GET', '/settings/profile'],
    ['PATCH', '/settings/profile'],
]);

test('an admin with several sites sees only theirs', function () {
    $this->actingAs($this->admin)->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Sites')
            ->has('sites', 2)
            ->where('sites.0.name', 'Alfa')
            ->where('sites.1.name', 'Beta')
            ->has('adminSites', 2));
});

test('an admin with one site goes straight to it', function () {
    $user = User::factory()->create();
    $user->sites()->attach($this->b1);

    $this->actingAs($user)->get('/admin')->assertRedirect(route('admin.sites.show', $this->b1));
});

test('an admin without sites sees the empty state', function () {
    $this->actingAs(User::factory()->create())->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/Sites')->has('sites', 0));
});

test('an admin can open their own site panel', function () {
    $this->actingAs($this->admin)->get(route('admin.sites.show', $this->a2))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/SiteDashboard')
            ->where('currentSite.slug', $this->a2->slug)
            ->where('site.publicUrl', url($this->a2->path)));
});

test('an admin cannot open a site of someone else', function () {
    $this->actingAs($this->admin)->get(route('admin.sites.show', $this->b1))->assertForbidden();
});

test('access is checked on every request', function () {
    $this->actingAs($this->admin)->get(route('admin.sites.show', $this->a1))->assertOk();
    $this->admin->sites()->detach($this->a1);

    $this->actingAs($this->admin)->get(route('admin.sites.show', $this->a1))->assertForbidden();
});

test('login sends the admin to the panel', function () {
    $this->post(route('login.store'), ['email' => $this->admin->email, 'password' => 'password'])
        ->assertRedirect('/admin');
});
