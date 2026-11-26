<x-layouts.site title="Nuestras tiendas" description="Las tiendas de la familia en un solo lugar." :tokens="\App\Support\Theme\ThemeTokens::fromTheme([])">
    @if ($doors->isEmpty())
        <main class="portal-empty"><p>Pronto vas a encontrar nuestras tiendas aquí.</p></main>
    @else
        <main class="portal">
            <h1 class="portal-title">Elige tu tienda</h1>
            @foreach ($doors as $door)
                <a class="door" href="{{ route('site.show', $door['site']->path) }}" style="{{ \App\Support\Theme\ThemeTokens::inline($door['tokens']) }}">
                    @if ($door['background'])
                        <img class="bg" src="{{ $door['background']->url() }}" alt="" style="object-position: {{ $door['background']->objectPosition() }}" @if ($loop->index > 1) loading="lazy" @endif>
                    @else
                        <div class="ph" aria-hidden="true"></div>
                    @endif
                    @if ($door['logo'])
                        <img class="logo" src="{{ $door['logo']->url() }}" alt="" width="72" height="72">
                    @else
                        <span class="brand-mark" aria-hidden="true">{{ mb_substr($door['site']->name, 0, 1) }}</span>
                    @endif
                    <h2>{{ $door['site']->name }}</h2>
                    @if (is_string($door['tagline']) && $door['tagline'] !== '')
                        <p>{{ $door['tagline'] }}</p>
                    @endif
                    <span class="go">Entrar a la tienda</span>
                </a>
            @endforeach
        </main>
    @endif
</x-layouts.site>
