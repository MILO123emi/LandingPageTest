@php
    $site = $page->site;
    $logo = $page->logo();
    $home = route('site.show', $site->path);
    $showPrices = (bool) ($site->settings['show_prices'] ?? false);
    $contactHref = $home.'#contacto';
    $catalog = $page->catalogUrl();
    $category = $product->category;
    $ask = \App\Support\SiteLinks::whatsapp($site, 'Hola, ¿tienen '.$product->name.'?');
    $description = $product->description && ! \App\Support\PendingText::isPending($product->description) ? $product->description : $page->description();
@endphp
<x-layouts.site :title="$product->name.' · '.$site->name" :description="$description" :tokens="$page->tokens" body-class="page-solid">
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    @include('site.partials.header-inner', ['navLink' => ['label' => 'Catálogo', 'href' => $catalog]])

    <main class="catalog wrap" id="contenido">
        <nav class="crumbs" aria-label="Ruta">
            <a href="{{ $home }}">Inicio</a>
            <span aria-hidden="true">/</span>
            <a href="{{ $catalog }}">Catálogo</a>
            <span aria-hidden="true">/</span>
            <a href="{{ $page->catalogUrl($category) }}">{{ $category->name }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">{{ $product->name }}</span>
        </nav>

        <article class="pdp">
            <div class="pdp-media">
                <x-site.img :media="$page->mediaFor($product->media_id)" :alt="$product->name" sizes="(min-width: 880px) 55vw, 100vw" :eager="true" />
            </div>
            <div class="pdp-info">
                <a class="product-cat" href="{{ $page->catalogUrl($category) }}">{{ $category->name }}</a>
                <h1>{{ $product->name }}</h1>
                @if ($product->description)
                    <p class="lead"><x-site.text :value="$product->description" /></p>
                @endif
                @if ($showPrices && $product->price !== null)
                    <p class="pdp-price">L {{ number_format((float) $product->price, 2) }}{{ $product->unit ? ' / '.$product->unit : '' }}</p>
                @endif
                @unless ($product->is_available)
                    <span class="badge">Agotado por ahora</span>
                @endunless
                <p class="pdp-note">Escríbenos para confirmar disponibilidad{{ $showPrices ? ' y precio' : '' }} antes de pasar a la tienda.</p>
                <div class="pdp-actions">
                    @if ($ask)
                        <a class="btn btn-primary btn-lg" href="{{ $ask }}" target="_blank" rel="noopener"><x-site.icon name="chat" stroke="2" />Preguntar por WhatsApp</a>
                    @endif
                    <a class="btn btn-light-ink btn-lg" href="{{ $page->catalogUrl($category) }}">Ver más de {{ $category->name }}</a>
                </div>
            </div>
        </article>

        @if ($related->isNotEmpty())
            <section class="related" aria-labelledby="related-title">
                <h2 id="related-title">También en esta categoría</h2>
                <div class="grid-products">
                    @foreach ($related as $product)
                        @include('site.partials.product-card')
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    @include('site.sections.footer', ['content' => $page->section(\App\Enums\SectionType::Footer)?->content ?? [], 'brandHref' => $home])
</x-layouts.site>
