<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronRight, Package, Settings2, Tags } from '@lucide/vue';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import { index as categoriesIndex } from '@/routes/admin/categories';
import { index as productsIndex } from '@/routes/admin/products';
import { edit as settingsEdit } from '@/routes/admin/settings';
import { publish } from '@/routes/admin/sites';
import type { AdminSite } from '@/types/site';

defineOptions({
    layout: (props: { site: AdminSite }) => ({
        breadcrumbs: [
            { title: 'Tus tiendas', href: dashboard() },
            { title: props.site.name, href: props.site.adminUrl },
        ],
    }),
});

const props = defineProps<{
    site: AdminSite;
    hasChanges: boolean;
    counts: { products: number; categories: number };
}>();

const publishing = ref(false);

function publishChanges() {
    router.post(publish(props.site.slug).url, {}, {
        preserveScroll: true,
        onStart: () => (publishing.value = true),
        onFinish: () => (publishing.value = false),
    });
}

const shortcuts = [
    { title: 'Productos', description: () => `${props.counts.products} en tu catálogo`, href: productsIndex(props.site.slug), icon: Package },
    { title: 'Categorías', description: () => `${props.counts.categories} para ordenar el catálogo`, href: categoriesIndex(props.site.slug), icon: Tags },
    { title: 'Ajustes', description: () => 'WhatsApp, redes, ubicación y precios', href: settingsEdit(props.site.slug), icon: Settings2 },
];
</script>

<template>
    <Head :title="site.name" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div>
            <Heading :title="site.name" description="Panel de tu tienda." />
            <Badge :variant="site.status === 'live' ? 'secondary' : 'outline'">
                {{ site.status === 'live' ? 'Publicada' : 'Borrador' }}
            </Badge>
        </div>

        <Card v-if="hasChanges" class="border-amber-500/40 bg-amber-50 dark:bg-amber-950/30">
            <CardHeader>
                <CardTitle>Tenés cambios sin publicar</CardTitle>
                <CardDescription>Tus clientes siguen viendo la versión anterior hasta que publiques.</CardDescription>
            </CardHeader>
            <CardFooter>
                <Button class="h-11" :disabled="publishing" @click="publishChanges">
                    <Spinner v-if="publishing" />Publicar cambios
                </Button>
            </CardFooter>
        </Card>

        <nav aria-label="Secciones de la tienda" class="grid gap-3 sm:grid-cols-3">
            <Link
                v-for="item in shortcuts"
                :key="item.title"
                :href="item.href"
                class="group flex min-h-20 items-center gap-4 rounded-xl border bg-card p-4 transition-colors hover:bg-accent focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
            >
                <component :is="item.icon" class="size-6 shrink-0 text-muted-foreground" aria-hidden="true" />
                <span class="flex-1">
                    <span class="block font-medium">{{ item.title }}</span>
                    <span class="block text-sm text-muted-foreground">{{ item.description() }}</span>
                </span>
                <ChevronRight class="size-4 text-muted-foreground transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
            </Link>
        </nav>

        <Card>
            <CardHeader>
                <CardTitle>Sitio público</CardTitle>
            </CardHeader>
            <CardContent>
                <p class="font-mono text-sm break-all">{{ site.publicUrl }}</p>
            </CardContent>
            <CardFooter>
                <Button variant="outline" as-child class="h-11">
                    <a :href="site.publicUrl" target="_blank" rel="noopener">Ver sitio</a>
                </Button>
            </CardFooter>
        </Card>
    </div>
</template>
