@php
    $site = $page->site;
    $logo = $page->logo();
    $band = 0;
@endphp
<x-layouts.site :title="$site->settings['seo_title'] ?? $site->name" :description="$page->description()" :tokens="$page->tokens">
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    @include('site.partials.header')

    <main id="inicio">
        <span id="contenido"></span>
        @foreach ($page->body() as $section)
            @php
                $content = $section->content;
                $banded = ! in_array($section->type, [\App\Enums\SectionType::Hero, \App\Enums\SectionType::Slogan], true);
                $alt = $banded && $band++ % 2 === 1;
            @endphp
            @include('site.sections.'.$section->type->value)
        @endforeach
    </main>

    @include('site.sections.footer', ['content' => $page->section(\App\Enums\SectionType::Footer)?->content ?? []])
</x-layouts.site>
