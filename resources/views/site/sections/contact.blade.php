@php
    $wa = \App\Support\SiteLinks::whatsapp($site, 'Hola, vengo de su página web.');
@endphp
<section class="section" id="contacto">
    <div class="wrap">
        <div class="contact reveal">
            <h2><x-site.text :value="$content['title'] ?? '¿Buscas algo en especial?'" /></h2>
            @if (! empty($content['body']))
                <p><x-site.text :value="$content['body']" /></p>
            @endif
            @if ($wa)
                <a class="btn btn-light" href="{{ $wa }}" target="_blank" rel="noopener"><x-site.icon name="chat" stroke="2" />Escríbenos por WhatsApp</a>
            @else
                <span class="pendiente">[PENDIENTE: número de WhatsApp]</span>
            @endif
        </div>
    </div>
</section>
