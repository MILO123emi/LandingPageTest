@php
    $site = $page->site;
    $logo = $page->logo();
    $home = route('site.show', $site->path);
    $showPrices = (bool) ($site->settings['show_prices'] ?? false);
    $contactHref = $home.'#contacto';
    $baseUrl = $page->catalogUrl($category);
    $query = $term !== '' ? ['q' => $term] : [];
    $heading = $category?->name ?? 'Catálogo';
    $total = $products->total();
    $wa = \App\Support\SiteLinks::whatsapp($site, $term !== '' ? 'Hola, ¿tienen '.$term.'?' : null);
@endphp
<x-layouts.site :title="$heading.' · '.$site->name" :description="$category?->description ?? $page->description()" :tokens="$page->tokens" body-class="page-solid">
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    @include('site.partials.header-inner', ['navLink' => ['label' => 'Inicio', 'href' => $home]])

    <main class="catalog wrap" id="contenido">
        <nav class="crumbs" aria-label="Ruta">
            <a href="{{ $home }}">Inicio</a>
            <span aria-hidden="true">/</span>
            @if ($category)
                <a href="{{ $page->catalogUrl() }}">Catálogo</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ $category->name }}</span>
            @else
                <span aria-current="page">Catálogo</span>
            @endif
        </nav>

        <div class="catalog-head">
            <h1>{{ $heading }}</h1>
            <p class="lead" aria-live="polite">
                @if ($term !== '')
                    {{ $total === 1 ? '1 resultado' : $total.' resultados' }} para “{{ $term }}”
                @else
                    {{ $total === 1 ? '1 producto' : $total.' productos' }}
                @endif
            </p>
        </div>

        <form class="search" role="search" method="get" action="{{ $baseUrl }}">
            <label class="visually-hidden" for="q">Buscar productos</label>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input id="q" name="q" type="search" value="{{ $term }}" maxlength="80" placeholder="Buscar por nombre" autocomplete="off" enterkeyhint="search">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </form>

        @if ($categories->count() > 1)
            <nav class="chips" aria-label="Categorías">
                <a class="chip" href="{{ $page->catalogUrl().($query ? '?'.http_build_query($query) : '') }}" @if (! $category) aria-current="page" @endif>Todos</a>
                @foreach ($categories as $chip)
                    <a class="chip" href="{{ $page->catalogUrl($chip).($query ? '?'.http_build_query($query) : '') }}" @if ($category?->is($chip)) aria-current="page" @endif>
                        {{ $chip->name }} <span class="chip-n">{{ $chip->products_count }}</span>
                    </a>
                @endforeach
            </nav>
        @endif

        @if ($products->isNotEmpty())
            <div class="grid-products">
                @foreach ($products as $product)
                    @include('site.partials.product-card', ['withCategory' => $category === null])
                @endforeach
            </div>
            {{ $products->onEachSide(1)->links('site.partials.pager') }}
        @else
            <div class="empty">
                @if ($term !== '')
                    <h2>No encontramos “{{ $term }}”</h2>
                    <p>Prueba con otra palabra o pregúntanos por WhatsApp: puede que lo tengamos y aún no esté en el catálogo.</p>
                @elseif ($products->currentPage() > 1)
                    <h2>Esta página no existe</h2>
                    <p>El catálogo tiene menos páginas.</p>
                @else
                    <h2>Todavía no hay productos aquí</h2>
                    <p>Estamos preparando el catálogo. Mientras tanto, pregúntanos por WhatsApp.</p>
                @endif
                <div class="empty-actions">
                    @if ($term !== '' || $category || $products->currentPage() > 1)
                        <a class="btn btn-light-ink" href="{{ $page->catalogUrl() }}">Ver todo el catálogo</a>
                    @else
                        <a class="btn btn-light-ink" href="{{ $home }}">Volver al inicio</a>
                    @endif
                    @if ($wa)
                        <a class="btn btn-primary" href="{{ $wa }}" target="_blank" rel="noopener">Preguntar por WhatsApp</a>
                    @endif
                </div>
            </div>
        @endif
    </main>

    @include('site.sections.footer', ['content' => $page->section(\App\Enums\SectionType::Footer)?->content ?? [], 'brandHref' => $home])
</x-layouts.site>
