<section class="slogan" aria-label="Lema">
    <div class="wrap reveal">
        <strong><x-site.text :value="$content['text'] ?? ''" /></strong>
        @if (! empty($content['note']))
            <span><x-site.text :value="$content['note']" /></span>
        @endif
    </div>
</section>
