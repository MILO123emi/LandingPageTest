<?php

use App\Models\Site;
use App\Models\User;

test('a live site resolves by its path', function () {
    $site = Site::factory()->create(['path' => 'demo-verde', 'name' => 'Tienda Demo Verde']);

    $this->get('/demo-verde')->assertOk()->assertSee('Tienda Demo Verde');
});

test('unknown or malformed paths are 404', function (string $uri) {
    Site::factory()->create(['path' => 'demo-verde']);

    $this->get($uri)->assertNotFound();
})->with(['/no-existe', '/Demo-Verde', '/demo_verde', '/login']);

test('a draft site is hidden from guests and other admins', function () {
    Site::factory()->draft()->create(['path' => 'demo-oculto']);

    $this->get('/demo-oculto')->assertNotFound();
    $this->actingAs(User::factory()->create())->get('/demo-oculto')->assertNotFound();
});

test('a draft site is visible to its admin', function () {
    $site = Site::factory()->draft()->create(['path' => 'demo-oculto']);
    $user = User::factory()->create();
    $user->sites()->attach($site);

    $this->actingAs($user)->get('/demo-oculto')->assertOk();
});

test('the home page lists only live sites', function () {
    Site::factory()->create(['name' => 'Sitio Publicado']);
    Site::factory()->draft()->create(['name' => 'Sitio Borrador']);

    $this->get('/')->assertOk()->assertSee('Sitio Publicado')->assertDontSee('Sitio Borrador');
});

test('public pages never reveal the admin path', function () {
    Site::factory()->create(['path' => 'demo-verde']);
    $adminPath = config('vitrina.admin_path');

    $this->get('/')->assertDontSee($adminPath);
    $this->get('/demo-verde')->assertDontSee($adminPath);
});

test('the hidden entry leads to the login screen', function () {
    $adminPath = config('vitrina.admin_path');

    $this->get('/'.$adminPath)->assertRedirect(route('login'));
    $this->get(route('login'))->assertOk();
    expect(route('login', absolute: false))->toBe('/'.$adminPath.'/login');
});

test('the health check still answers', function () {
    $this->get('/up')->assertOk();
});
