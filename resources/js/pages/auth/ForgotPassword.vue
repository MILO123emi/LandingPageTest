<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/password/recovery';

defineOptions({ layout: {
    title: '¿Olvidaste tu contraseña?',
    description: 'Escribí tu correo y te mandamos un código de 6 dígitos.',
} });
</script>

<template>
    <Head title="¿Olvidaste tu contraseña?" />
    <Form v-bind="store.form()" class="flex flex-col gap-6" v-slot="{ errors, processing }">
        <div class="grid gap-2">
            <Label for="email">Correo</Label>
            <Input id="email" name="email" type="email" autocomplete="email" required v-focus />
            <InputError :message="errors.email" />
        </div>
        <Button class="mt-4 w-full" :disabled="processing"><Spinner v-if="processing" />Enviar código</Button>
        <TextLink :href="login()" class="text-center">Volver a entrar</TextLink>
    </Form>
</template>
