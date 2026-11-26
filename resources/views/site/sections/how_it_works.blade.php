<section class="section{{ $alt ? ' alt' : '' }}">
    <div class="wrap">
        <div class="section-head reveal"><h2><x-site.text :value="$content['title'] ?? 'Cómo comprar'" /></h2></div>
        <ol class="steps">
            @foreach (array_slice((array) ($content['steps'] ?? []), 0, 4) as $step)
                <li class="reveal">
                    <h3><x-site.text :value="$step['title'] ?? ''" /></h3>
                    <p><x-site.text :value="$step['body'] ?? ''" /></p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
