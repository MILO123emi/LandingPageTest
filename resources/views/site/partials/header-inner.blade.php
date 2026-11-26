{{-- Header for inner pages (catalog, product). $navLink is ['label' => ..., 'href' => ...]. --}}
@php
    $wa = \App\Support\SiteLinks::whatsapp($site);
@endphp
<header class="site-header scrolled">
    <div class="wrap">
        <x-site.brand :site="$site" :logo="$logo" :href="route('site.show', $site->path)" />
        <nav class="nav" aria-label="Principal">
            <a href="{{ $navLink['href'] }}">{{ $navLink['label'] }}</a>
            @if ($wa)
                <a class="btn btn-primary" href="{{ $wa }}" target="_blank" rel="noopener"><x-site.icon name="chat" stroke="2" />WhatsApp</a>
            @endif
        </nav>
    </div>
</header>
