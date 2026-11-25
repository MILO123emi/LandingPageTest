<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { AdminSite } from '@/types/site';

defineOptions({
    layout: (props: { site: AdminSite }) => ({
        breadcrumbs: [
            { title: 'Tus tiendas', href: dashboard() },
            { title: props.site.name, href: props.site.adminUrl },
        ],
    }),
});

defineProps<{ site: AdminSite }>();
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
