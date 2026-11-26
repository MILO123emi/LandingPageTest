@php
    $mapsUrl = \App\Support\SiteLinks::maps($content);
    $embed = \App\Support\SiteLinks::mapEmbed($content);
    $waLabel = \App\Support\SiteLinks::whatsappLabel($site);
    $wa = \App\Support\SiteLinks::whatsapp($site);
    $instagram = \App\Support\SiteLinks::social($site, 'instagram');
    $hours = (array) ($content['hours'] ?? []);
@endphp
<section class="section{{ $alt ? ' alt' : '' }}" id="ubicacion">
    <div class="wrap location">
        <div class="section-head reveal" style="margin:0">
            <h2><x-site.text :value="$content['title'] ?? 'Visítanos'" /></h2>
            @if (! empty($content['address']))
                <p class="lead"><x-site.text :value="$content['address']" /></p>
            @endif
            @if ($mapsUrl)
                <div><a class="btn btn-primary" href="{{ $mapsUrl }}" target="_blank" rel="noopener"><x-site.icon name="pin" stroke="2" />Cómo llegar</a></div>
            @endif
        </div>
        <dl class="reveal">
            <div>
                <dt>Horario</dt>
                <dd>
                    @if ($hours !== [])
                        <ul class="hours">
                            @foreach ($hours as $row)
                                <li><span>{{ $row['days'] ?? '' }}</span> · <x-site.text :value="$row['time'] ?? ''" /></li>
                            @endforeach
                        </ul>
                    @else
                        <span class="pendiente">[PENDIENTE: días y horas de atención]</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt>WhatsApp</dt>
                <dd>
                    @if ($wa)
                        <a href="{{ $wa }}" target="_blank" rel="noopener">{{ $waLabel }}</a>
                    @else
                        <span class="pendiente">[PENDIENTE: número de WhatsApp]</span>
                    @endif
                </dd>
            </div>
            @if ($instagram)
                <div><dt>Instagram</dt><dd><a href="{{ $instagram }}" target="_blank" rel="noopener">{{ \App\Support\SiteLinks::handle($instagram) }}</a></dd></div>
            @endif
        </dl>
        @if ($embed)
            <div class="location-map reveal">
                <iframe src="{{ $embed }}" title="Mapa de {{ $site->name }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        @endif
    </div>
</section>
