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
                @php
                    $url = $page->productUrl($product);
                    $ask = \App\Support\SiteLinks::whatsapp($site, 'Hola, ¿tienen '.$product->name.'?');
                @endphp
                <article class="product reveal">
                    @if ($url)
                        <a class="product-media" href="{{ $url }}" tabindex="-1" aria-hidden="true"><x-site.img :media="$page->mediaFor($product->media_id)" :alt="$product->name" sizes="(min-width: 900px) 280px, 50vw" /></a>
                        <h3><a href="{{ $url }}">{{ $product->name }}</a></h3>
                    @else
                        <div class="product-media"><x-site.img :media="$page->mediaFor($product->media_id)" :alt="$product->name" sizes="(min-width: 900px) 280px, 50vw" /></div>
                        <h3>{{ $product->name }}</h3>
                    @endif
                    @if ($product->description)
                        <p><x-site.text :value="$product->description" /></p>
                    @endif
                    @if ($showPrices && $product->price !== null)
                        <span class="price">L {{ number_format((float) $product->price, 2) }}{{ $product->unit ? ' / '.$product->unit : '' }}</span>
                    @endif
                    @unless ($product->is_available)
                        <span class="badge">Agotado</span>
                    @endunless
                    <a href="{{ $ask ?? '#contacto' }}" @if ($ask) target="_blank" rel="noopener" @endif>Preguntar por WhatsApp</a>
                </article>
            @endforeach
        </div>
        @if ($page->catalogUrl())
            <div class="section-foot reveal"><a class="btn btn-primary" href="{{ $page->catalogUrl() }}">Ver todo el catálogo</a></div>
        @endif
    </div>
</section>
