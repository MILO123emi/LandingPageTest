<?php

namespace App\Http\Requests\Admin;

use App\Support\ContactNormalizer;
use App\Support\MapsLink;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SiteSettingsRequest extends FormRequest
{
    private const NETWORKS = ['instagram', 'facebook', 'tiktok'];

    /** @var array<string, mixed> */
    private array $normalized = [];

    /** Access is checked by the site.manage middleware. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'instagram' => ['nullable', 'string', 'max:200'],
            'facebook' => ['nullable', 'string', 'max:200'],
            'tiktok' => ['nullable', 'string', 'max:200'],
            'show_prices' => ['boolean'],
            'unit' => ['nullable', 'string', 'max:20'],
            'maps_link' => ['nullable', 'string', 'max:2000'],
            'lat' => ['nullable', 'required_with:lng', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'required_with:lat', 'numeric', 'between:-180,180'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'whatsapp' => 'WhatsApp',
            'maps_link' => 'enlace de Google Maps',
            'lat' => 'latitud',
            'lng' => 'longitud',
            'unit' => 'unidad',
        ];
    }

    /** @return list<Closure(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $whatsapp = $this->string('whatsapp')->trim()->value();
            $this->normalized['whatsapp'] = $whatsapp === '' ? null : ContactNormalizer::whatsapp($whatsapp);

            if ($whatsapp !== '' && $this->normalized['whatsapp'] === null) {
                $validator->errors()->add('whatsapp', 'Escribí un número de Honduras de 8 dígitos, por ejemplo 9499-7224.');
            }

            foreach (self::NETWORKS as $network) {
                $value = $this->string($network)->trim()->value();
                $this->normalized[$network] = $value === '' ? null : ContactNormalizer::social($network, $value);

                if ($value !== '' && $this->normalized[$network] === null) {
                    $validator->errors()->add($network, 'Pegá el enlace de tu perfil o escribí tu usuario (por ejemplo @mitienda).');
                }
            }

            $this->normalizeLocation($validator);
        }];
    }

    /**
     * Normalized values: settings keys plus a `location` array (lat, lng, maps_url),
     * or null in `location` when the owner left the location empty.
     *
     * @return array<string, mixed>
     */
    public function normalized(): array
    {
        return [
            ...$this->normalized,
            'show_prices' => $this->boolean('show_prices'),
            'unit' => $this->string('unit')->trim()->value() ?: null,
        ];
    }

    private function normalizeLocation(Validator $validator): void
    {
        $link = $this->string('maps_link')->trim()->value();
        $mapsUrl = $link !== '' && MapsLink::isGoogle($link) ? $link : null;

        if ($this->filled(['lat', 'lng'])) {
            $this->normalized['location'] = ['lat' => (float) $this->input('lat'), 'lng' => (float) $this->input('lng'), 'maps_url' => $mapsUrl];

            return;
        }

        if ($link === '') {
            $this->normalized['location'] = null;

            return;
        }

        $coordinates = $mapsUrl !== null ? MapsLink::resolve($link) : null;

        if ($coordinates === null) {
            $validator->errors()->add('maps_link', 'No pudimos leer la ubicación de ese enlace. Abrí la tienda en Google Maps en la computadora y copiá el enlace largo, o escribí la latitud y la longitud.');

            return;
        }

        $this->normalized['location'] = [...$coordinates, 'maps_url' => $mapsUrl];
    }
}
