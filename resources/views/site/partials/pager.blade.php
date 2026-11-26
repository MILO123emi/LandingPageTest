{{-- Numbered pagination for public pages. Rendered through $paginator->links('site.partials.pager'). --}}
@if ($paginator->hasPages())
    <nav class="pager" aria-label="Páginas del catálogo">
        @if ($paginator->onFirstPage())
            <span class="pager-step" aria-disabled="true">Anterior</span>
        @else
            <a class="pager-step" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
        @endif

        <ul class="pager-nums">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="pager-gap" aria-hidden="true">…</li>
                @else
                    @foreach ($element as $number => $url)
                        <li>
                            <a href="{{ $url }}" @if ($number === $paginator->currentPage()) aria-current="page" @endif aria-label="Página {{ $number }}">{{ $number }}</a>
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ul>

        @if ($paginator->hasMorePages())
            <a class="pager-step" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
        @else
            <span class="pager-step" aria-disabled="true">Siguiente</span>
        @endif
    </nav>
@endif
