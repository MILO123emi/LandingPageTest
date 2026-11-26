<?php

use App\Support\ContactNormalizer;
use App\Support\MapsLink;

test('coordinates are read from each Google Maps link format', function (string $url, float $lat, float $lng) {
    expect(MapsLink::coordinates($url))->toBe(['lat' => $lat, 'lng' => $lng]);
})->with([
    'place pin' => ['https://www.google.com/maps/place/X/@15.77,-86.79,1139m/data=!3m2!1e3!4b1!8m2!3d15.7793202!4d-86.7901445', 15.7793202, -86.7901445],
    'query' => ['https://www.google.com/maps/search/?api=1&query=15.787746%2C-86.789279', 15.787746, -86.789279],
    'q param' => ['https://maps.google.com/maps?q=15.5,-86.5&z=17', 15.5, -86.5],
    'map centre' => ['https://www.google.com/maps/@15.7750164,-86.7669924,17z', 15.7750164, -86.7669924],
]);

test('links outside Google or without coordinates give nothing', function (string $url) {
    expect(MapsLink::coordinates($url))->toBeNull();
})->with([
    'https://evil.example/maps/@15.1,-86.1,17z',
    'http://www.google.com/maps/@15.1,-86.1,17z',
    'https://www.google.com/maps/place/Super+El+Rey',
    'https://www.google.com/maps/@95.1,-86.1,17z',
]);

test('whatsapp numbers are normalized to 504 plus 8 digits', function (string $input, ?string $expected) {
    expect(ContactNormalizer::whatsapp($input))->toBe($expected);
})->with([
    ['9499-7224', '50494997224'],
    ['+504 9499 7224', '50494997224'],
    ['50432954145', '50432954145'],
    ['2443-0000', '50424430000'],
    ['1499-7224', null],
    ['94997224 5', null],
]);
