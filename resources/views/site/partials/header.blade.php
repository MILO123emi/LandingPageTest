@php
    $wa = \App\Support\SiteLinks::whatsapp($site);
    $catalog = $page->catalogUrl() ?? ($page->has(\App\Enums\SectionType::FeaturedProducts) ? '#productos' : null);
@endphp
<header class="site-header">
    <div class="wrap">
        <x-site.brand :site="$site" :logo="$logo" />
        <nav class="nav" aria-label="Principal">
            @if ($catalog)
                <a href="{{ $catalog }}">Catálogo</a>
            @endif
            @if ($page->has(\App\Enums\SectionType::Location))
                <a href="#ubicacion">Ubicación</a>
            @endif
            @if ($wa)
                <a class="btn btn-primary" href="{{ $wa }}" target="_blank" rel="noopener"><x-site.icon name="chat" stroke="2" />WhatsApp</a>
            @elseif ($page->has(\App\Enums\SectionType::Contact))
                <a class="btn btn-primary" href="#contacto"><x-site.icon name="chat" stroke="2" />Contacto</a>
            @endif
        </nav>
    </div>
</header>
