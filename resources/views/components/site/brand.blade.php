@props(['site', 'logo' => null, 'href' => '#inicio'])
<a class="brand" href="{{ $href }}">
    @if ($logo)
        <img src="{{ $logo->url() }}" alt="" width="44" height="44">
    @else
        <span class="brand-mark" aria-hidden="true">{{ mb_substr($site->name, 0, 1) }}</span>
    @endif
    {{ $site->name }}
</a>
