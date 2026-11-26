@php
    $instagram = \App\Support\SiteLinks::social($site, 'instagram');
@endphp
<footer class="site-footer">
    <div class="wrap">
        <x-site.brand :site="$site" :logo="$logo" />
        @if (! empty($content['tagline']))
            <p><x-site.text :value="$content['tagline']" /></p>
        @endif
        <nav aria-label="Pie de página">
            <a href="{{ route('home') }}">Todas las tiendas</a>
            @if ($instagram)
                <a href="{{ $instagram }}" target="_blank" rel="noopener">Instagram</a>
            @endif
            @if ($facebook = \App\Support\SiteLinks::social($site, 'facebook'))
                <a href="{{ $facebook }}" target="_blank" rel="noopener">Facebook</a>
            @endif
        </nav>
        <span>© {{ now()->year }} {{ $site->name }}</span>
    </div>
</footer>
