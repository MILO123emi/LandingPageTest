<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ExternalLink, ImagePlus, Trash2 } from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';
import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { dashboard } from '@/routes';
import { index as productsIndex } from '@/routes/admin/products';
import type { AdminSite } from '@/types/site';

type Product = {
    id: number;
    name: string;
    categoryId: number;
    description: string | null;
    price: string | null;
    unit: string | null;
    photo: string | null;
    isVisible: boolean;
    isAvailable: boolean;
    isFeatured: boolean;
    publicUrl: string;
};

defineOptions({
    layout: (props: { site: AdminSite; product: Product | null }) => ({
        breadcrumbs: [
            { title: 'Tus tiendas', href: dashboard() },
            { title: props.site.name, href: props.site.adminUrl },
            { title: 'Productos', href: productsIndex(props.site.slug) },
            { title: props.product ? 'Editar' : 'Nuevo', href: '#' },
        ],
    }),
});

const props = defineProps<{
    site: AdminSite;
    product: Product | null;
    categories: { id: number; name: string }[];
    defaultUnit: string | null;
}>();

const form = useForm({
    name: props.product?.name ?? '',
    category_id: props.product?.categoryId ?? props.categories[0]?.id ?? null,
    description: props.product?.description ?? '',
    price: props.product?.price ?? '',
    unit: props.product?.unit ?? props.defaultUnit ?? '',
    is_visible: props.product?.isVisible ?? true,
    is_available: props.product?.isAvailable ?? true,
    is_featured: props.product?.isFeatured ?? false,
    photo: null as File | null,
    remove_photo: false,
});

const previewUrl = ref<string | null>(null);
const shownPhoto = computed(() => previewUrl.value ?? (form.remove_photo ? null : props.product?.photo ?? null));

function pickPhoto(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    form.photo = file;
    form.remove_photo = false;
    previewUrl.value = file ? URL.createObjectURL(file) : null;
}

function removePhoto() {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    previewUrl.value = null;
    form.photo = null;
    form.remove_photo = true;
}

onBeforeUnmount(() => previewUrl.value && URL.revokeObjectURL(previewUrl.value));

function submit() {
    if (props.product) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(ProductController.update({ site: props.site.slug, product: props.product.id }).url, {
            forceFormData: true,
            preserveScroll: true,
        });
    } else {
        form.post(ProductController.store(props.site.slug).url, { forceFormData: true, preserveScroll: true });
    }
}

const confirmDelete = ref(false);
const deleting = ref(false);

function destroy() {
    if (!props.product) {
        return;
    }

    router.delete(ProductController.destroy({ site: props.site.slug, product: props.product.id }).url, {
        onStart: () => (deleting.value = true),
        onFinish: () => (deleting.value = false),
    });
}

const selectClass =
    'h-11 w-full rounded-md border border-input bg-transparent px-3 text-base shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive md:text-sm dark:bg-input/30';
</script>

<template>
    <Head :title="product ? product.name : 'Nuevo producto'" />

    <div class="flex max-w-3xl flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading :title="product ? 'Editar producto' : 'Nuevo producto'" description="Los cambios se ven en tu catálogo al guardar." />
            <Button v-if="product" variant="outline" as-child class="h-11">
                <a :href="product.publicUrl" target="_blank" rel="noopener"><ExternalLink aria-hidden="true" />Ver en el sitio</a>
            </Button>
        </div>

        <form class="grid gap-6" @submit.prevent="submit">
            <div class="grid gap-6 md:grid-cols-[12rem_1fr]">
                <div class="grid w-full max-w-56 content-start gap-2 md:max-w-none">
                    <span class="text-sm font-medium">Foto</span>
                    <div class="relative aspect-square overflow-hidden rounded-xl border bg-muted">
                        <img v-if="shownPhoto" :src="shownPhoto" alt="Vista previa de la foto" class="size-full object-cover" />
                        <div v-else class="flex size-full flex-col items-center justify-center gap-2 p-4 text-center text-sm text-muted-foreground">
                            <ImagePlus class="size-8" aria-hidden="true" />
                            Sin foto
                        </div>
                    </div>
                    <Label
                        for="product-photo"
                        class="flex h-11 cursor-pointer items-center justify-center rounded-md border px-3 text-sm font-medium hover:bg-accent has-[:focus-visible]:ring-[3px] has-[:focus-visible]:ring-ring/50"
                    >
                        {{ shownPhoto ? 'Cambiar foto' : 'Elegir foto' }}
                        <input id="product-photo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="pickPhoto" />
                    </Label>
                    <Button v-if="shownPhoto" type="button" variant="ghost" class="h-11" @click="removePhoto">Quitar foto</Button>
                    <p class="text-xs text-muted-foreground">JPG, PNG o WebP, hasta 8 MB.</p>
                    <InputError :message="form.errors.photo" />
                    <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full" aria-label="Subiendo foto" />
                </div>

                <div class="grid content-start gap-4">
                    <div class="grid gap-2">
                        <Label for="product-name">Nombre</Label>
                        <Input id="product-name" v-model="form.name" class="h-11" maxlength="120" required :aria-invalid="!!form.errors.name" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="product-category">Categoría</Label>
                        <select id="product-category" v-model="form.category_id" :class="selectClass" required :aria-invalid="!!form.errors.category_id">
                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                        </select>
                        <InputError :message="form.errors.category_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="product-description">Descripción <span class="text-muted-foreground">(opcional)</span></Label>
                        <Textarea id="product-description" v-model="form.description" maxlength="2000" :aria-invalid="!!form.errors.description" />
                        <InputError :message="form.errors.description" />
                    </div>
                    <div class="grid items-start gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="product-price">Precio en lempiras <span class="text-muted-foreground">(opcional)</span></Label>
                            <Input
                                id="product-price"
                                v-model="form.price"
                                class="h-11"
                                inputmode="decimal"
                                placeholder="0.00"
                                :aria-invalid="!!form.errors.price"
                                aria-describedby="product-price-hint"
                            />
                            <p id="product-price-hint" class="text-xs text-muted-foreground">Solo se muestra si activás "Mostrar precios" en Ajustes.</p>
                            <InputError :message="form.errors.price" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="product-unit">Unidad <span class="text-muted-foreground">(opcional)</span></Label>
                            <Input id="product-unit" v-model="form.unit" class="h-11" maxlength="20" list="unit-options" placeholder="lb, unidad, docena…" />
                            <datalist id="unit-options">
                                <option value="unidad" />
                                <option value="lb" />
                                <option value="kg" />
                                <option value="docena" />
                                <option value="paquete" />
                                <option value="litro" />
                            </datalist>
                            <InputError :message="form.errors.unit" />
                        </div>
                    </div>
                    <fieldset class="grid gap-1">
                        <legend class="mb-1 text-sm font-medium">En el sitio</legend>
                        <Label class="flex min-h-11 items-center gap-3 font-normal"><Checkbox v-model="form.is_visible" />Mostrar en el catálogo</Label>
                        <Label class="flex min-h-11 items-center gap-3 font-normal"><Checkbox v-model="form.is_available" />Disponible (si no, sale "Agotado")</Label>
                        <Label class="flex min-h-11 items-center gap-3 font-normal"><Checkbox v-model="form.is_featured" />Destacado en la portada</Label>
                    </fieldset>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 border-t pt-6">
                <Button type="submit" class="h-11" :disabled="form.processing"><Spinner v-if="form.processing" />Guardar</Button>
                <Button variant="ghost" as-child class="h-11"><Link :href="productsIndex(site.slug)">Cancelar</Link></Button>
                <Button
                    v-if="product"
                    type="button"
                    variant="ghost"
                    class="ml-auto h-11 text-destructive hover:text-destructive"
                    @click="confirmDelete = true"
                >
                    <Trash2 aria-hidden="true" />Borrar producto
                </Button>
            </div>
        </form>
    </div>

    <Dialog v-model:open="confirmDelete">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>¿Borrar "{{ product?.name }}"?</DialogTitle>
                <DialogDescription>Deja de verse en tu catálogo y no se puede deshacer. Si solo querés esconderlo un tiempo, desmarcá "Mostrar en el catálogo".</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="ghost" class="h-11" @click="confirmDelete = false">Cancelar</Button>
                <Button variant="destructive" class="h-11" :disabled="deleting" @click="destroy"><Spinner v-if="deleting" />Borrar</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
