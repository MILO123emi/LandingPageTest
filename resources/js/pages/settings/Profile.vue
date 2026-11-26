<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { edit as editEmail } from '@/routes/email';
import { Spinner } from '@/components/ui/spinner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Perfil',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Perfil" />

    <h1 class="sr-only">Perfil</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Perfil"
            description="Actualizá tu nombre"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Nombre</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Nombre"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="space-y-2">
                <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                <TextLink :href="editEmail()">Cambiar correo</TextLink>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button"
                    ><Spinner v-if="processing" />Guardar</Button
                >
            </div>
        </Form>
    </div>

</template>
