<?php

test('application name is configured as a string', function () {
    expect(config('app.name'))->toBeString();
});

test('laravel test application is bootstrapped', function () {
    expect(app()->isBooted())->toBeTrue();
});
