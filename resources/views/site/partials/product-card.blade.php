{{-- Expects $page, $site, $product and $showPrices. Optional: $withCategory adds the category link above the name, $contactHref replaces the #contacto fallback when there is no WhatsApp. --}}
@php
    $url = $page->productUrl($product);
    $ask = \App\Support\SiteLinks::whatsapp($site, 'Hola, ¿tienen '.$product->name.'?');
    $media = $page->mediaFor($product->media_id);
    $withCategory = ($withCategory ?? false) && $product->relationLoaded('category') && $product->category;
@endphp
<article class="product product-card reveal">
    @if ($url)
        <a class="product-media" href="{{ $url }}" tabindex="-1" aria-hidden="true"><x-site.img :media="$media" :alt="$product->name" sizes="(min-width: 900px) 280px, 50vw" /></a>
    @else
        <div class="product-media"><x-site.img :media="$media" :alt="$product->name" sizes="(min-width: 900px) 280px, 50vw" /></div>
    @endif
    @if ($withCategory)
        <a class="product-cat" href="{{ $page->catalogUrl($product->category) }}">{{ $product->category->name }}</a>
    @endif
    @if ($url)
        <h3><a href="{{ $url }}">{{ $product->name }}</a></h3>
    @else
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
    <a href="{{ $ask ?? ($contactHref ?? '#contacto') }}" @if ($ask) target="_blank" rel="noopener" @endif>Preguntar por WhatsApp</a>
</article>
