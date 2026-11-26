@php
    $showPrices = (bool) ($site->settings['show_prices'] ?? false);
@endphp
<section class="section{{ $alt ? ' alt' : '' }}" id="productos">
    <div class="wrap">
        <div class="section-head reveal">
            <h2><x-site.text :value="$content['title'] ?? 'Productos destacados'" /></h2>
            @if (! empty($content['subtitle']))
                <p class="lead"><x-site.text :value="$content['subtitle']" /></p>
            @endif
        </div>
        <div class="grid-products">
            @foreach ($page->featured as $product)
                @include('site.partials.product-card')
            @endforeach
        </div>
        @if ($page->catalogUrl())
            <div class="section-foot reveal"><a class="btn btn-primary" href="{{ $page->catalogUrl() }}">Ver todo el catálogo</a></div>
        @endif
    </div>
</section>
