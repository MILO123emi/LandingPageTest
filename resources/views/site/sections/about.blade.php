<section class="section{{ $alt ? ' alt' : '' }}">
    <div class="wrap about">
        <div class="about-media reveal">
            <x-site.img :media="$page->mediaFor($content['media_id'] ?? null)" sizes="(min-width: 880px) 600px, 100vw" />
        </div>
        <div class="about-text reveal">
            <h2><x-site.text :value="$content['title'] ?? ''" /></h2>
            @if (! empty($content['body']))
                <p class="lead"><x-site.text :value="$content['body']" /></p>
            @endif
            @if (! empty($content['highlights']))
                <ul class="highlights">
                    @foreach (array_slice((array) $content['highlights'], 0, 3) as $highlight)
                        <li><x-site.icon :name="$highlight['icon'] ?? null" /><x-site.text :value="$highlight['text'] ?? ''" /></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</section>
