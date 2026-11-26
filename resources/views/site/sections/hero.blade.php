@php
    $target = $content['cta_target'] ?? 'catalog';
    $ctaHref = match ($target) {
        'whatsapp' => \App\Support\SiteLinks::whatsapp($site) ?? '#contacto',
        'location' => '#ubicacion',
        default => $page->has(\App\Enums\SectionType::FeaturedProducts) ? '#productos' : ($page->catalogUrl() ?? '#contacto'),
    };
@endphp
<section class="hero">
    <div class="hero-media">
        <x-site.img :media="$page->mediaFor($content['background_media_id'] ?? null)" :eager="true" sizes="100vw" />
    </div>
    <div class="wrap">
        @if (! empty($content['eyebrow']))
            <p class="eyebrow"><x-site.text :value="$content['eyebrow']" /></p>
        @endif
        <h1><x-site.text :value="$content['title'] ?? $site->name" /></h1>
        @if (! empty($content['subtitle']))
            <p><x-site.text :value="$content['subtitle']" /></p>
        @endif
        <div class="hero-actions">
            <a class="btn btn-primary" href="{{ $ctaHref }}" @if (str_starts_with($ctaHref, 'https://')) target="_blank" rel="noopener" @endif>{{ $content['cta_label'] ?? 'Ver productos' }}</a>
            @if ($target !== 'location' && $page->has(\App\Enums\SectionType::Location))
                <a class="btn btn-light" href="#ubicacion">Cómo llegar</a>
            @endif
        </div>
    </div>
</section>
