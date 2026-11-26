@props(['title', 'description' => '', 'tokens' => [], 'bodyClass' => null])
<!DOCTYPE html>
<html lang="es-HN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @if ($description !== '')
        <meta name="description" content="{{ $description }}">
    @endif
    @unless (config('vitrina.indexable'))
        <meta name="robots" content="noindex">
    @endunless
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/site.css', 'resources/js/site.ts'])
    @if ($tokens !== [])
        <style>:root { {{ \App\Support\Theme\ThemeTokens::inline($tokens) }} }</style>
    @endif
</head>
<body @if ($bodyClass) class="{{ $bodyClass }}" @endif>
    {{ $slot }}
</body>
</html>
