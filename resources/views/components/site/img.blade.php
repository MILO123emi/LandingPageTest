@props(['media' => null, 'sizes' => '100vw', 'eager' => false, 'alt' => null, 'icon' => 'basket'])
@if ($media)
    @php
    $srcset = $media->srcset();
@endphp
    <img src="{{ $media->url() }}" @if ($srcset !== '') srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif alt="{{ $alt ?? $media->alt }}" width="{{ $media->width }}" height="{{ $media->height }}" style="object-position: {{ $media->objectPosition() }}" @if ($eager) fetchpriority="high" @else loading="lazy" decoding="async" @endif {{ $attributes }}>
@else
    <div class="ph" aria-hidden="true"><x-site.icon :name="$icon" /></div>
@endif
