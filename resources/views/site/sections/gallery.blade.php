<section class="section{{ $alt ? ' alt' : '' }}">
    <div class="wrap">
        <div class="section-head reveal"><h2><x-site.text :value="$content['title'] ?? 'Así se ve la tienda'" /></h2></div>
        <div class="gallery reveal">
            @foreach (array_slice((array) ($content['items'] ?? []), 0, 9) as $item)
                <figure>
                    <x-site.img :media="$page->mediaFor($item['media_id'] ?? null)" sizes="(min-width: 760px) 400px, 50vw" />
                    @if (! empty($item['caption']))
                        <figcaption>{{ $item['caption'] }}</figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    </div>
</section>
