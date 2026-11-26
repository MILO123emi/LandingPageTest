<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Sites\PublishSite;
use App\Data\AdminSiteData;
use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingsRequest;
use App\Models\Section;
use App\Models\Site;
use App\Support\MapsLink;
use App\Support\SiteLinks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingsController extends Controller
{
    public function edit(Site $site): Response
    {
        $settings = $site->draft_settings ?? $site->settings ?? [];
        $location = $this->location();
        $content = $location === null ? [] : ($location->draft_content ?? $location->content);

        return Inertia::render('admin/SiteSettings', [
            'site' => AdminSiteData::fromSite($site),
            'settings' => [
                'whatsapp' => isset($settings['whatsapp']) ? substr((string) $settings['whatsapp'], 3, 4).'-'.substr((string) $settings['whatsapp'], 7) : '',
                'instagram' => $settings['instagram'] ?? '',
                'facebook' => $settings['facebook'] ?? '',
                'tiktok' => $settings['tiktok'] ?? '',
                'show_prices' => (bool) ($settings['show_prices'] ?? false),
                'unit' => $settings['unit'] ?? '',
                'maps_link' => $content['maps_url'] ?? '',
                'lat' => $content['lat'] ?? null,
                'lng' => $content['lng'] ?? null,
            ],
            'hasLocationSection' => $location !== null,
            'hasChanges' => PublishSite::hasChanges($site),
        ]);
    }

    public function update(SiteSettingsRequest $request, Site $site): RedirectResponse
    {
        $data = $request->normalized();
        $location = $data['location'] ?? null;
        unset($data['location']);

        $site->draft_settings = [...($site->draft_settings ?? $site->settings ?? []), ...$data];
        $site->save();

        $section = $this->location();

        if ($section !== null) {
            $content = $section->draft_content ?? $section->content;
            $section->draft_content = [
                ...$content,
                'lat' => $location['lat'] ?? null,
                'lng' => $location['lng'] ?? null,
                'maps_url' => $location['maps_url'] ?? null,
            ];
            $section->save();
        }

        $this->toast('Ajustes guardados. Se verán en tu sitio cuando publiques.');

        return back();
    }

    /** Map preview for a pasted link, before saving. */
    public function previewLocation(Request $request, Site $site): JsonResponse
    {
        $request->validate(['maps_link' => ['required', 'string', 'max:2000']]);

        $coordinates = MapsLink::resolve($request->string('maps_link')->trim()->value());

        if ($coordinates === null) {
            return response()->json(['message' => 'No pudimos leer la ubicación de ese enlace.'], 422);
        }

        return response()->json([...$coordinates, 'embed' => SiteLinks::mapEmbed($coordinates)]);
    }

    public function publish(Site $site): RedirectResponse
    {
        PublishSite::handle($site);
        $this->toast('Listo: tus cambios ya se ven en el sitio.');

        return back();
    }

    private function location(): ?Section
    {
        return Section::query()->where('type', SectionType::Location)->first();
    }
}
