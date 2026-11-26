<?php

use App\Enums\SectionType;
use App\Models\Section;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    [$this->mine, $this->other] = Site::factory()->count(2)->create([
        'settings' => ['whatsapp' => '50411112222', 'show_prices' => false, 'tagline' => 'Hola'],
    ]);
    $this->admin = User::factory()->create();
    $this->admin->sites()->attach($this->mine);

    $this->location = Section::factory()->create([
        'site_id' => $this->mine->id,
        'type' => SectionType::Location,
        'content' => ['title' => 'Visítanos', 'address' => 'Centro', 'lat' => 1.0, 'lng' => 2.0],
    ]);
});

function saveSettings(array $data)
{
    return test()->actingAs(test()->admin)->put(route('admin.settings.update', test()->mine), [
        'whatsapp' => '', 'instagram' => '', 'facebook' => '', 'tiktok' => '',
        'show_prices' => false, 'unit' => '', 'maps_link' => '', 'lat' => null, 'lng' => null,
        ...$data,
    ]);
}

test('an admin cannot change the settings of another site', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.update', $this->other), ['whatsapp' => '9499-7224'])
        ->assertForbidden();

    expect($this->other->fresh()->draft_settings)->toBeNull();
});

test('settings go to the draft and keep the keys the form does not edit', function () {
    saveSettings(['whatsapp' => '+504 9499-7224', 'show_prices' => true])->assertSessionHasNoErrors();

    $site = $this->mine->fresh();
    expect($site->draft_settings)->toMatchArray(['whatsapp' => '50494997224', 'show_prices' => true, 'tagline' => 'Hola'])
        ->and($site->settings['whatsapp'])->toBe('50411112222');
});

test('whatsapp numbers that are not Honduran are rejected', function (string $number) {
    saveSettings(['whatsapp' => $number])->assertSessionHasErrors('whatsapp');
})->with(['123', '+1 555 123 4567', '0499-7224']);

test('social links on a foreign host are rejected', function (string $field, string $value) {
    saveSettings([$field => $value])->assertSessionHasErrors($field);
})->with([
    ['instagram', 'https://evil.example/instagram.com/tienda'],
    ['facebook', 'https://facebook.com.evil.example/tienda'],
    ['tiktok', 'javascript:alert(1)'],
]);

test('social handles are stored as profile URLs', function () {
    saveSettings(['instagram' => '@mi.tienda', 'tiktok' => 'mitienda', 'facebook' => 'https://m.facebook.com/MiTienda?ref=x'])
        ->assertSessionHasNoErrors();

    expect($this->mine->fresh()->draft_settings)->toMatchArray([
        'instagram' => 'https://www.instagram.com/mi.tienda/',
        'tiktok' => 'https://www.tiktok.com/@mitienda',
        'facebook' => 'https://www.facebook.com/MiTienda/',
    ]);
});

test('a pasted maps link sets the location in the section draft', function () {
    $link = 'https://www.google.com/maps/place/Tienda/@15.7793202,-86.7927194,1139m/data=!3m2!1e3!4b1!8m2!3d15.7793202!4d-86.7901445';

    saveSettings(['maps_link' => $link])->assertSessionHasNoErrors();

    $section = $this->location->fresh();
    expect($section->draft_content)->toMatchArray(['lat' => 15.7793202, 'lng' => -86.7901445, 'maps_url' => $link, 'address' => 'Centro'])
        ->and($section->content['lat'])->toEqual(1);
});

test('typed coordinates win over the link', function () {
    saveSettings(['lat' => '15.5', 'lng' => '-86.5', 'maps_link' => 'https://www.google.com/maps/@1,2,15z'])->assertSessionHasNoErrors();

    expect($this->location->fresh()->draft_content)->toMatchArray(['lat' => 15.5, 'lng' => -86.5]);
});

test('a link that is not from Google Maps is rejected', function () {
    saveSettings(['maps_link' => 'https://evil.example/@15.1,-86.1'])->assertSessionHasErrors('maps_link');
});

test('the map preview resolves a short link without leaving Google', function () {
    Http::fake([
        'maps.app.goo.gl/*' => Http::response('', 302, ['Location' => 'https://www.google.com/maps/place/X/data=!3d15.1!4d-86.2']),
    ]);

    $this->actingAs($this->admin)
        ->postJson(route('admin.settings.map', $this->mine), ['maps_link' => 'https://maps.app.goo.gl/abc123'])
        ->assertOk()
        ->assertJson(['lat' => 15.1, 'lng' => -86.2]);
});

test('a short link that redirects outside Google is not followed', function () {
    Http::fake([
        'maps.app.goo.gl/*' => Http::response('', 302, ['Location' => 'https://evil.example/@15.1,-86.2']),
        'evil.example/*' => Http::response('', 500),
    ]);

    $this->actingAs($this->admin)
        ->postJson(route('admin.settings.map', $this->mine), ['maps_link' => 'https://maps.app.goo.gl/abc123'])
        ->assertStatus(422);

    Http::assertSentCount(1);
});

test('publishing copies drafts over the published values', function () {
    saveSettings(['whatsapp' => '9499-7224', 'lat' => '15.5', 'lng' => '-86.5']);

    $this->post(route('admin.sites.publish', $this->mine))->assertRedirect();

    $site = $this->mine->fresh();
    $section = $this->location->fresh();
    expect($site->settings['whatsapp'])->toBe('50494997224')
        ->and($site->draft_settings)->toBeNull()
        ->and($site->published_at)->not->toBeNull()
        ->and($section->content['lat'])->toBe(15.5)
        ->and($section->draft_content)->toBeNull();
});

test('an admin cannot publish another site', function () {
    $this->actingAs($this->admin)->post(route('admin.sites.publish', $this->other))->assertForbidden();
});
