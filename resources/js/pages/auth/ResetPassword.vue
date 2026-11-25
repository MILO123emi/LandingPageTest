<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { REGEXP_ONLY_DIGITS } from 'vue-input-otp';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { InputOTP, InputOTPGroup, InputOTPSlot } from '@/components/ui/input-otp';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { create, update } from '@/routes/password/recovery';

defineProps<{ email: string }>();
defineOptions({ layout: { title: 'Escribí el código' } });
</script>

<template>
    <Head title="Escribí el código" />
    <p class="mb-6 text-center text-sm text-muted-foreground">Si {{ email }} está registrado, te llegó un código. Vence en 10 minutos.</p>
    <Form v-bind="update.form()" class="flex flex-col gap-6" :reset-on-success="['password', 'password_confirmation', 'code']" v-slot="{ errors, processing }">
        <div class="grid gap-2">
            <Label for="code">Código</Label>
            <InputOTP id="code" name="code" :maxlength="6" inputmode="numeric" autocomplete="one-time-code" autofocus :pattern="REGEXP_ONLY_DIGITS">
                <InputOTPGroup><InputOTPSlot v-for="index in 6" :key="index" :index="index - 1" /></InputOTPGroup>
            </InputOTP>
            <InputError :message="errors.code" />
        </div>
        <div class="grid gap-2">
            <Label for="password">Contraseña nueva</Label>
            <PasswordInput id="password" name="password" autocomplete="new-password" required />
            <InputError :message="errors.password" />
        </div>
        <div class="grid gap-2">
            <Label for="password_confirmation">Repetí la contraseña</Label>
            <PasswordInput id="password_confirmation" name="password_confirmation" autocomplete="new-password" required />
            <InputError :message="errors.password_confirmation" />
        </div>
        <Button class="mt-4 w-full" :disabled="processing"><Spinner v-if="processing" />Cambiar contraseña</Button>
        <TextLink :href="create()" class="text-center">Pedir otro código</TextLink>
    </Form>
</template>
