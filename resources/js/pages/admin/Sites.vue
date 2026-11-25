<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Store } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { AdminSite } from '@/types/site';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Tus tiendas', href: dashboard() }],
    },
});

defineProps<{ sites: AdminSite[] }>();
</script>

<template>
    <Head title="Tus tiendas" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <Heading title="Tus tiendas" description="Elegí la tienda que querés administrar." />
        <div v-if="sites.length" class="grid gap-4 sm:grid-cols-2">
            <Card v-for="site in sites" :key="site.slug">
                <CardHeader>
                    <CardTitle>{{ site.name }}</CardTitle>
                    <CardDescription>/{{ site.path }}</CardDescription>
                    <Badge :variant="site.status === 'live' ? 'secondary' : 'outline'">
                        {{ site.status === 'live' ? 'Publicada' : 'Borrador' }}
                    </Badge>
                </CardHeader>
                <CardFooter class="flex gap-2">
                    <Button as-child class="h-11">
                        <Link :href="site.adminUrl">Administrar</Link>
                    </Button>
                    <Button variant="ghost" as-child class="h-11">
                        <a :href="site.publicUrl" target="_blank" rel="noopener">Ver sitio</a>
                    </Button>
                </CardFooter>
            </Card>
        </div>
        <div v-else class="rounded-xl border border-dashed p-8 text-center">
            <Store class="mx-auto mb-4 size-8 text-muted-foreground" aria-hidden="true" />
            <h3 class="font-medium">Todavía no tenés tiendas asignadas</h3>
            <p class="text-sm text-muted-foreground">Pedí acceso a quien administra Vitrina.</p>
        </div>
    </div>
</template>
