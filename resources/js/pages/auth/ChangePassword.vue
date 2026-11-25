<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import ForcedPasswordController from '@/actions/App/Http/Controllers/Settings/ForcedPasswordController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';

defineOptions({
    layout: {
        title: 'Cambiá tu contraseña',
        description: 'Es tu primera vez en el panel. Elegí una contraseña nueva de al menos 10 caracteres.',
    },
});

defineProps<{ passwordRules: string }>();
</script>

<template>
    <Head title="Cambiá tu contraseña" />
    <Form v-bind="ForcedPasswordController.update.form()" class="flex flex-col gap-6" v-slot="{ errors, processing }">
        <div class="grid gap-2">
            <Label for="password">Contraseña nueva</Label>
            <PasswordInput id="password" name="password" autocomplete="new-password" :passwordrules="passwordRules" required v-focus />
            <InputError :message="errors.password" />
        </div>
        <div class="grid gap-2">
            <Label for="password_confirmation">Repetí la contraseña</Label>
            <PasswordInput id="password_confirmation" name="password_confirmation" autocomplete="new-password" :passwordrules="passwordRules" required />
            <InputError :message="errors.password_confirmation" />
        </div>
        <Button class="mt-4 w-full" :disabled="processing"><Spinner v-if="processing" />Guardar y entrar</Button>
    </Form>
    <TextLink :href="logout()" method="post" as="button" class="mt-6">Salir</TextLink>
</template>
