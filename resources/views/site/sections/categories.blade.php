<section class="section{{ $alt ? ' alt' : '' }}">
    <div class="wrap">
        <div class="section-head reveal">
            <h2><x-site.text :value="$content['title'] ?? 'Categorías'" /></h2>
            @if (! empty($content['subtitle']))
                <p class="lead"><x-site.text :value="$content['subtitle']" /></p>
            @endif
        </div>
        <div class="grid-cats">
            @foreach ($page->categories as $category)
                <a class="cat reveal" href="{{ $page->catalogUrl($category) ?? '#productos' }}">
                    <x-site.img :media="$page->mediaFor($category->media_id)" alt="" sizes="(min-width: 900px) 280px, 50vw" />
                    <span>{{ $category->name }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
