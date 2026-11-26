<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { MapPin } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import SiteSettingsController from '@/actions/App/Http/Controllers/Admin/SiteSettingsController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import { edit as settingsEdit } from '@/routes/admin/settings';
import { publish } from '@/routes/admin/sites';
import type { AdminSite } from '@/types/site';

type Settings = {
    whatsapp: string;
    instagram: string;
    facebook: string;
    tiktok: string;
    show_prices: boolean;
    unit: string;
    maps_link: string;
    lat: number | null;
    lng: number | null;
};

defineOptions({
    layout: (props: { site: AdminSite }) => ({
        breadcrumbs: [
            { title: 'Tus tiendas', href: dashboard() },
            { title: props.site.name, href: props.site.adminUrl },
            { title: 'Ajustes', href: settingsEdit(props.site.slug) },
        ],
    }),
});

const props = defineProps<{ site: AdminSite; settings: Settings; hasLocationSection: boolean; hasChanges: boolean }>();

const settingsForm = useForm({
    ...props.settings,
    lat: props.settings.lat === null ? '' : String(props.settings.lat),
    lng: props.settings.lng === null ? '' : String(props.settings.lng),
});

function submit() {
    settingsForm.submit(SiteSettingsController.update(props.site.slug), { preserveScroll: true });
}

// Map preview: a pasted link is read on the server (short links need a redirect).
const mapError = ref<string | null>(null);
const mapLoading = ref(false);
const showCoordinates = ref(false);

const embed = computed(() => {
    const lat = Number(settingsForm.lat);
    const lng = Number(settingsForm.lng);

    if (settingsForm.lat.trim() === '' || settingsForm.lng.trim() === '' || Number.isNaN(lat) || Number.isNaN(lng)) {
        return null;
    }

    return `https://www.google.com/maps?q=${lat},${lng}&z=17&output=embed`;
});

const mapMessage = computed(() => mapError.value ?? settingsForm.errors.maps_link);

watch(
    () => settingsForm.maps_link,
    () => {
        mapError.value = null;
        settingsForm.lat = '';
        settingsForm.lng = '';
    },
);

function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function previewMap() {
    mapError.value = null;

    if (!settingsForm.maps_link.trim()) {
        mapError.value = 'Pegá primero el enlace de Google Maps.';

        return;
    }

    mapLoading.value = true;

    try {
        const response = await fetch(SiteSettingsController.previewLocation(props.site.slug).url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': xsrfToken() },
            body: JSON.stringify({ maps_link: settingsForm.maps_link }),
        });
        const data = await response.json();

        if (!response.ok) {
            mapError.value = data.message ?? 'No pudimos leer ese enlace.';

            return;
        }

        settingsForm.lat = String(data.lat);
        settingsForm.lng = String(data.lng);
    } catch {
        mapError.value = 'No hay conexión. Probá de nuevo en un momento.';
    } finally {
        mapLoading.value = false;
    }
}

function clearLocation() {
    settingsForm.maps_link = '';
    settingsForm.lat = '';
    settingsForm.lng = '';
}

const publishing = ref(false);

function publishChanges() {
    router.post(publish(props.site.slug).url, {}, {
        preserveScroll: true,
        onStart: () => (publishing.value = true),
        onFinish: () => (publishing.value = false),
    });
}
</script>

<template>
    <Head title="Ajustes" />

    <div class="flex max-w-3xl flex-col gap-6 p-4 md:p-6">
        <Heading title="Ajustes del sitio" description="Contacto, redes, ubicación y precios. Se ven en tu sitio cuando publicás." />

        <div v-if="hasChanges" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-500/40 bg-amber-50 p-4 dark:bg-amber-950/30">
            <p class="text-sm">Tenés cambios guardados que tus clientes todavía no ven.</p>
            <Button class="h-11" :disabled="publishing" @click="publishChanges"><Spinner v-if="publishing" />Publicar cambios</Button>
        </div>

        <form class="grid gap-6" @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Contacto y redes</CardTitle>
                    <CardDescription>Escribilos como quieras; nosotros los dejamos en el formato correcto.</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="whatsapp">WhatsApp</Label>
                        <Input id="whatsapp" v-model="settingsForm.whatsapp" class="h-11" inputmode="tel" autocomplete="tel" placeholder="9499-7224" :aria-invalid="!!settingsForm.errors.whatsapp" />
                        <InputError :message="settingsForm.errors.whatsapp" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="instagram">Instagram</Label>
                        <Input id="instagram" v-model="settingsForm.instagram" class="h-11" placeholder="@mitienda o el enlace del perfil" :aria-invalid="!!settingsForm.errors.instagram" />
                        <InputError :message="settingsForm.errors.instagram" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="facebook">Facebook <span class="text-muted-foreground">(opcional)</span></Label>
                            <Input id="facebook" v-model="settingsForm.facebook" class="h-11" placeholder="Enlace de la página" :aria-invalid="!!settingsForm.errors.facebook" />
                            <InputError :message="settingsForm.errors.facebook" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="tiktok">TikTok <span class="text-muted-foreground">(opcional)</span></Label>
                            <Input id="tiktok" v-model="settingsForm.tiktok" class="h-11" placeholder="@mitienda" :aria-invalid="!!settingsForm.errors.tiktok" />
                            <InputError :message="settingsForm.errors.tiktok" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Ubicación</CardTitle>
                    <CardDescription>Abrí tu tienda en Google Maps, tocá «Compartir», copiá el enlace y pegalo aquí.</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <p v-if="!hasLocationSection" class="text-sm text-muted-foreground">Tu sitio no tiene sección de ubicación, así que el mapa no se muestra.</p>
                    <div class="grid gap-2">
                        <Label for="maps-link">Enlace de Google Maps</Label>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <Input
                                id="maps-link"
                                v-model="settingsForm.maps_link"
                                class="h-11"
                                inputmode="url"
                                placeholder="maps.app.goo.gl/…"
                                :aria-invalid="!!(settingsForm.errors.maps_link || mapError)"
                                aria-describedby="maps-link-error"
                            />
                            <Button type="button" variant="outline" class="h-11 shrink-0" :disabled="mapLoading" @click="previewMap">
                                <Spinner v-if="mapLoading" /><MapPin v-else aria-hidden="true" />Ver en el mapa
                            </Button>
                        </div>
                        <InputError id="maps-link-error" :message="mapMessage" />
                    </div>

                    <div v-if="embed" class="overflow-hidden rounded-xl border">
                        <iframe :src="embed" title="Vista previa del mapa" class="aspect-[4/3] w-full sm:aspect-[16/9]" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <p v-else class="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground">
                        Sin ubicación todavía. Pegá el enlace y tocá «Ver en el mapa».
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <Button type="button" variant="ghost" class="h-11" :aria-expanded="showCoordinates" @click="showCoordinates = !showCoordinates">
                            {{ showCoordinates ? 'Ocultar coordenadas' : 'Escribir coordenadas a mano' }}
                        </Button>
                        <Button v-if="embed || settingsForm.maps_link" type="button" variant="ghost" class="h-11" @click="clearLocation">Quitar ubicación</Button>
                    </div>
                    <div v-if="showCoordinates" class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="lat">Latitud</Label>
                            <Input id="lat" v-model="settingsForm.lat" class="h-11" inputmode="decimal" placeholder="15.7793" :aria-invalid="!!settingsForm.errors.lat" />
                            <InputError :message="settingsForm.errors.lat" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="lng">Longitud</Label>
                            <Input id="lng" v-model="settingsForm.lng" class="h-11" inputmode="decimal" placeholder="-86.7901" :aria-invalid="!!settingsForm.errors.lng" />
                            <InputError :message="settingsForm.errors.lng" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Precios</CardTitle>
                    <CardDescription>Si los apagás, el precio no aparece en ninguna parte del sitio.</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <Label class="flex min-h-11 items-center gap-3 font-normal"><Checkbox v-model="settingsForm.show_prices" />Mostrar precios en el catálogo</Label>
                    <div class="grid gap-2 sm:max-w-xs">
                        <Label for="unit">Unidad para productos nuevos <span class="text-muted-foreground">(opcional)</span></Label>
                        <Input id="unit" v-model="settingsForm.unit" class="h-11" maxlength="20" placeholder="unidad, lb…" :aria-invalid="!!settingsForm.errors.unit" />
                        <InputError :message="settingsForm.errors.unit" />
                    </div>
                </CardContent>
                <CardFooter class="border-t pt-6">
                    <Button type="submit" class="h-11" :disabled="settingsForm.processing"><Spinner v-if="settingsForm.processing" />Guardar ajustes</Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
