<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, Eye, EyeOff, Pencil, Plus, Tags, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { dashboard } from '@/routes';
import { index as categoriesIndex } from '@/routes/admin/categories';
import type { AdminSite } from '@/types/site';

type Category = {
    id: number;
    name: string;
    description: string | null;
    isVisible: boolean;
    productsCount: number;
    publicUrl: string;
};

defineOptions({
    layout: (props: { site: AdminSite }) => ({
        breadcrumbs: [
            { title: 'Tus tiendas', href: dashboard() },
            { title: props.site.name, href: props.site.adminUrl },
            { title: 'Categorías', href: categoriesIndex(props.site.slug) },
        ],
    }),
});

const props = defineProps<{ site: AdminSite; categories: Category[] }>();

const page = usePage();
const deleteError = computed(() => page.props.errors.category);

const editing = ref<Category | null>(null);
const formOpen = ref(false);
const form = useForm({ name: '', description: '' as string | null, is_visible: true });

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    formOpen.value = true;
}

function openEdit(category: Category) {
    editing.value = category;
    form.clearErrors();
    form.name = category.name;
    form.description = category.description ?? '';
    form.is_visible = category.isVisible;
    formOpen.value = true;
}

function submit() {
    const options = { preserveScroll: true, onSuccess: () => (formOpen.value = false) };

    if (editing.value) {
        form.submit(CategoryController.update({ site: props.site.slug, category: editing.value.id }), options);
    } else {
        form.submit(CategoryController.store(props.site.slug), options);
    }
}

function move(category: Category, direction: 'up' | 'down') {
    router.post(CategoryController.move({ site: props.site.slug, category: category.id }).url, { direction }, { preserveScroll: true });
}

function toggleVisible(category: Category) {
    router.put(
        CategoryController.update({ site: props.site.slug, category: category.id }).url,
        { name: category.name, description: category.description, is_visible: !category.isVisible },
        { preserveScroll: true },
    );
}

const deleting = ref<Category | null>(null);
const deleteProcessing = ref(false);

function confirmDelete() {
    if (!deleting.value) {
        return;
    }

    router.delete(CategoryController.destroy({ site: props.site.slug, category: deleting.value.id }).url, {
        preserveScroll: true,
        onStart: () => (deleteProcessing.value = true),
        onFinish: () => {
            deleteProcessing.value = false;
            deleting.value = null;
        },
    });
}
</script>

<template>
    <Head title="Categorías" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading title="Categorías" description="Agrupan tus productos en el catálogo. El orden de aquí es el orden en tu sitio." />
            <Button class="h-11" @click="openCreate"><Plus aria-hidden="true" />Nueva categoría</Button>
        </div>

        <Alert v-if="deleteError" variant="destructive">
            <AlertDescription>{{ deleteError }}</AlertDescription>
        </Alert>

        <ul v-if="categories.length" class="divide-y rounded-xl border">
            <li v-for="(category, index) in categories" :key="category.id" class="flex flex-wrap items-center gap-3 p-3 sm:flex-nowrap">
                <div class="flex flex-col">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="size-9"
                        :disabled="index === 0"
                        :aria-label="`Subir ${category.name}`"
                        @click="move(category, 'up')"
                    >
                        <ArrowUp aria-hidden="true" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="size-9"
                        :disabled="index === categories.length - 1"
                        :aria-label="`Bajar ${category.name}`"
                        @click="move(category, 'down')"
                    >
                        <ArrowDown aria-hidden="true" />
                    </Button>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="line-clamp-2 font-medium break-words" :class="{ 'text-muted-foreground': !category.isVisible }">{{ category.name }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ category.productsCount === 1 ? '1 producto' : `${category.productsCount} productos` }}
                        <span v-if="!category.isVisible"> · Oculta</span>
                    </p>
                </div>
                <div class="flex gap-1">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="size-11"
                        :aria-label="category.isVisible ? `Ocultar ${category.name}` : `Mostrar ${category.name}`"
                        :aria-pressed="!category.isVisible"
                        @click="toggleVisible(category)"
                    >
                        <Eye v-if="category.isVisible" aria-hidden="true" />
                        <EyeOff v-else aria-hidden="true" />
                    </Button>
                    <Button variant="ghost" size="icon" class="size-11" :aria-label="`Editar ${category.name}`" @click="openEdit(category)">
                        <Pencil aria-hidden="true" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="size-11 text-destructive hover:text-destructive"
                        :aria-label="`Borrar ${category.name}`"
                        @click="deleting = category"
                    >
                        <Trash2 aria-hidden="true" />
                    </Button>
                </div>
            </li>
        </ul>

        <div v-else class="rounded-xl border border-dashed p-8 text-center">
            <Tags class="mx-auto mb-4 size-8 text-muted-foreground" aria-hidden="true" />
            <h3 class="font-medium">Todavía no tenés categorías</h3>
            <p class="mb-4 text-sm text-muted-foreground">Creá la primera (por ejemplo "Frutas" o "Limpieza") para empezar a cargar productos.</p>
            <Button class="h-11" @click="openCreate"><Plus aria-hidden="true" />Nueva categoría</Button>
        </div>
    </div>

    <Dialog v-model:open="formOpen">
        <DialogContent>
            <form class="grid gap-4" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>{{ editing ? 'Editar categoría' : 'Nueva categoría' }}</DialogTitle>
                    <DialogDescription>El nombre aparece en el catálogo y en los filtros.</DialogDescription>
                </DialogHeader>
                <div class="grid gap-2">
                    <Label for="category-name">Nombre</Label>
                    <Input id="category-name" v-model="form.name" class="h-11" maxlength="80" required :aria-invalid="!!form.errors.name" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="category-description">Descripción <span class="text-muted-foreground">(opcional)</span></Label>
                    <Textarea id="category-description" v-model="form.description" maxlength="300" :aria-invalid="!!form.errors.description" />
                    <InputError :message="form.errors.description" />
                </div>
                <Label class="flex min-h-11 items-center gap-3">
                    <Checkbox v-model="form.is_visible" />
                    Mostrar en el sitio
                </Label>
                <DialogFooter>
                    <Button type="button" variant="ghost" class="h-11" @click="formOpen = false">Cancelar</Button>
                    <Button type="submit" class="h-11" :disabled="form.processing"><Spinner v-if="form.processing" />Guardar</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <Dialog :open="deleting !== null" @update:open="(open) => !open && (deleting = null)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>¿Borrar "{{ deleting?.name }}"?</DialogTitle>
                <DialogDescription>
                    <template v-if="deleting?.productsCount">
                        Tiene {{ deleting.productsCount }} producto(s). Movelos a otra categoría antes de borrarla.
                    </template>
                    <template v-else>Esto no se puede deshacer.</template>
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="ghost" class="h-11" @click="deleting = null">Cancelar</Button>
                <Button
                    variant="destructive"
                    class="h-11"
                    :disabled="deleteProcessing || !!deleting?.productsCount"
                    @click="confirmDelete"
                >
                    <Spinner v-if="deleteProcessing" />Borrar
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
