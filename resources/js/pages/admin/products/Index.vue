<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, Check, ImageOff, Package, Plus, Search } from '@lucide/vue';
import { ref, watch } from 'vue';
import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { create as productsCreate, index as productsIndex } from '@/routes/admin/products';
import { index as categoriesIndex } from '@/routes/admin/categories';
import type { AdminSite } from '@/types/site';

type ProductRow = {
    id: number;
    name: string;
    category: string;
    price: string | null;
    unit: string | null;
    photo: string | null;
    isVisible: boolean;
    isAvailable: boolean;
    isFeatured: boolean;
    editUrl: string;
};

type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    from: number | null;
    to: number | null;
};

type Flag = 'is_visible' | 'is_available' | 'is_featured';

defineOptions({
    layout: (props: { site: AdminSite }) => ({
        breadcrumbs: [
            { title: 'Tus tiendas', href: dashboard() },
            { title: props.site.name, href: props.site.adminUrl },
            { title: 'Productos', href: productsIndex(props.site.slug) },
        ],
    }),
});

const props = defineProps<{
    site: AdminSite;
    products: Paginated<ProductRow>;
    categories: { id: number; name: string }[];
    filters: { q: string; categoria: number | null };
    canReorder: boolean;
}>();

const q = ref(props.filters.q);
const categoria = ref<string>(props.filters.categoria ? String(props.filters.categoria) : '');

let searchTimer: ReturnType<typeof setTimeout> | undefined;

function applyFilters() {
    const query: Record<string, string> = {};

    if (q.value.trim()) {
        query.q = q.value.trim();
    }

    if (categoria.value) {
        query.categoria = categoria.value;
    }

    router.get(productsIndex(props.site.slug).url, query, { preserveState: true, preserveScroll: true, replace: true });
}

watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 350);
});

watch(categoria, applyFilters);

const flags: { field: Flag; key: 'isVisible' | 'isAvailable' | 'isFeatured'; on: string; off: string }[] = [
    { field: 'is_visible', key: 'isVisible', on: 'Visible', off: 'Oculto' },
    { field: 'is_available', key: 'isAvailable', on: 'Disponible', off: 'Agotado' },
    { field: 'is_featured', key: 'isFeatured', on: 'Destacado', off: 'Destacar' },
];

function toggle(product: ProductRow, field: Flag, current: boolean) {
    router.patch(
        ProductController.toggle({ site: props.site.slug, product: product.id }).url,
        { field, value: !current },
        { preserveScroll: true, preserveState: true },
    );
}

function move(product: ProductRow, direction: 'up' | 'down') {
    router.post(ProductController.move({ site: props.site.slug, product: product.id }).url, { direction }, { preserveScroll: true, preserveState: true });
}

function price(product: ProductRow): string | null {
    if (product.price === null) {
        return null;
    }

    return `L ${Number(product.price).toLocaleString('es-HN', { minimumFractionDigits: 2 })}${product.unit ? ` / ${product.unit}` : ''}`;
}

function pageLabel(label: string): string {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›').replace('Previous', 'Anterior').replace('Next', 'Siguiente');
}
</script>

<template>
    <Head title="Productos" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading title="Productos" description="Lo que ven tus clientes en el catálogo. Los cambios se ven al instante." />
            <Button v-if="categories.length" as-child class="h-11">
                <Link :href="productsCreate(site.slug)"><Plus aria-hidden="true" />Nuevo producto</Link>
            </Button>
        </div>

        <div v-if="!categories.length" class="rounded-xl border border-dashed p-8 text-center">
            <Package class="mx-auto mb-4 size-8 text-muted-foreground" aria-hidden="true" />
            <h3 class="font-medium">Primero creá una categoría</h3>
            <p class="mb-4 text-sm text-muted-foreground">Cada producto va dentro de una categoría.</p>
            <Button as-child class="h-11"><Link :href="categoriesIndex(site.slug)">Ir a categorías</Link></Button>
        </div>

        <template v-else>
            <div class="grid gap-3 sm:grid-cols-[1fr_16rem]">
                <div class="grid gap-1.5">
                    <Label for="product-search">Buscar</Label>
                    <div class="relative">
                        <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
                        <Input id="product-search" v-model="q" type="search" class="h-11 pl-9" placeholder="Nombre del producto" />
                    </div>
                </div>
                <div class="grid gap-1.5">
                    <Label for="product-category">Categoría</Label>
                    <select
                        id="product-category"
                        v-model="categoria"
                        class="h-11 w-full rounded-md border border-input bg-transparent px-3 text-base shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                    >
                        <option value="">Todas</option>
                        <option v-for="category in categories" :key="category.id" :value="String(category.id)">{{ category.name }}</option>
                    </select>
                </div>
            </div>

            <p v-if="!canReorder && products.data.length > 1" class="text-sm text-muted-foreground">
                Para cambiar el orden, elegí una categoría y dejá la búsqueda vacía.
            </p>

            <ul v-if="products.data.length" class="divide-y rounded-xl border">
                <li v-for="(product, index) in products.data" :key="product.id" class="flex flex-wrap items-center gap-3 p-3 md:flex-nowrap">
                    <div v-if="canReorder" class="flex flex-col">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="size-9"
                            :disabled="index === 0 && products.from === 1"
                            :aria-label="`Subir ${product.name}`"
                            @click="move(product, 'up')"
                        >
                            <ArrowUp aria-hidden="true" />
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="size-9"
                            :disabled="index === products.data.length - 1 && products.to === products.total"
                            :aria-label="`Bajar ${product.name}`"
                            @click="move(product, 'down')"
                        >
                            <ArrowDown aria-hidden="true" />
                        </Button>
                    </div>
                    <Link :href="product.editUrl" class="flex min-w-0 flex-1 items-center gap-3 rounded-md focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none">
                        <img v-if="product.photo" :src="product.photo" alt="" class="size-14 shrink-0 rounded-md object-cover" loading="lazy" />
                        <span v-else class="flex size-14 shrink-0 items-center justify-center rounded-md bg-muted">
                            <ImageOff class="size-5 text-muted-foreground" aria-hidden="true" />
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate font-medium" :class="{ 'text-muted-foreground': !product.isVisible }">{{ product.name }}</span>
                            <span class="block truncate text-sm text-muted-foreground">
                                {{ product.category }}<template v-if="price(product)"> · {{ price(product) }}</template>
                            </span>
                        </span>
                    </Link>
                    <div class="flex w-full flex-wrap gap-1.5 md:w-auto">
                        <button
                            v-for="flag in flags"
                            :key="flag.field"
                            type="button"
                            class="inline-flex h-9 items-center gap-1 rounded-full border px-3 text-sm transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                            :class="
                                product[flag.key]
                                    ? 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/70'
                                    : 'border-dashed text-muted-foreground hover:bg-accent'
                            "
                            :aria-pressed="product[flag.key]"
                            :aria-label="`${flag.on}: ${product.name}`"
                            @click="toggle(product, flag.field, product[flag.key])"
                        >
                            <Check v-if="product[flag.key]" class="size-3.5" aria-hidden="true" />
                            {{ product[flag.key] ? flag.on : flag.off }}
                        </button>
                    </div>
                </li>
            </ul>

            <div v-else class="rounded-xl border border-dashed p-8 text-center">
                <Package class="mx-auto mb-4 size-8 text-muted-foreground" aria-hidden="true" />
                <h3 class="font-medium">{{ filters.q || filters.categoria ? 'No hay productos con ese filtro' : 'Todavía no tenés productos' }}</h3>
                <p class="text-sm text-muted-foreground">
                    {{ filters.q || filters.categoria ? 'Probá con otra palabra o elegí "Todas".' : 'Cargá el primero con "Nuevo producto".' }}
                </p>
            </div>

            <nav v-if="products.links.length > 3" aria-label="Páginas" class="flex flex-wrap items-center justify-center gap-1">
                <template v-for="link in products.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        class="flex h-11 min-w-11 items-center justify-center rounded-md px-3 text-sm hover:bg-accent focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        :class="{ 'bg-primary text-primary-foreground hover:bg-primary': link.active }"
                        :aria-current="link.active ? 'page' : undefined"
                    >
                        {{ pageLabel(link.label) }}
                    </Link>
                    <span v-else class="flex h-11 min-w-11 items-center justify-center px-3 text-sm text-muted-foreground">{{ pageLabel(link.label) }}</span>
                </template>
            </nav>
            <p v-if="products.total" class="text-center text-sm text-muted-foreground">
                {{ products.from }}–{{ products.to }} de {{ products.total }}
            </p>
        </template>
    </div>
</template>
