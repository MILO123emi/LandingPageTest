<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 40rem; margin: auto; padding: 2rem 1rem; color: #1d1d1f; background: #fbfbfd; }
        a { display: inline-flex; align-items: center; min-height: 44px; color: inherit; }
    </style>
</head>
<body>
    <h1>Nuestras tiendas</h1>
    @if ($sites->isEmpty())
        <p>Pronto vas a encontrar nuestras tiendas acá.</p>
    @else
        <ul>
            @foreach ($sites as $site)
                <li><a href="{{ url($site->path) }}">{{ $site->name }}</a></li>
            @endforeach
        </ul>
    @endif
</body>
</html>
