<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import EmailController from '@/actions/App/Http/Controllers/Settings/EmailController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { InputOTP, InputOTPGroup, InputOTPSlot } from '@/components/ui/input-otp';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { edit } from '@/routes/email';

const props = defineProps<{ pendingEmail: string | null }>();
const page = usePage();
const useAnotherEmail = ref(false);
watch(() => props.pendingEmail, () => { useAnotherEmail.value = false; });

defineOptions({ layout: { breadcrumbs: [{ title: 'Correo', href: edit() }] } });
</script>

<template>
    <Head title="Correo" />
    <div class="space-y-6">
        <Heading variant="small" title="Correo" :description="`Tu correo actual: ${page.props.auth.user.email}`" />
        <template v-if="pendingEmail && !useAnotherEmail">
            <p>Escribí el código de 6 dígitos que mandamos a {{ pendingEmail }}. Vence en 10 minutos.</p>
            <Form v-bind="EmailController.update.form()" class="space-y-6" v-slot="{ errors, processing }">
                <div class="grid gap-2">
                    <Label for="code">Código</Label>
                    <InputOTP id="code" name="code" :maxlength="6" inputmode="numeric" autocomplete="one-time-code" v-focus>
                        <InputOTPGroup><InputOTPSlot v-for="index in 6" :key="index" :index="index - 1" /></InputOTPGroup>
                    </InputOTP>
                    <InputError :message="errors.code" />
                </div>
                <Button :disabled="processing"><Spinner v-if="processing" />Confirmar</Button>
            </Form>
            <Button variant="link" @click="useAnotherEmail = true">Usar otro correo</Button>
        </template>
        <Form v-else v-bind="EmailController.store.form()" class="space-y-6" reset-on-success v-slot="{ errors, processing }" @success="useAnotherEmail = false">
            <div class="grid gap-2">
                <Label for="email">Correo nuevo</Label>
                <Input id="email" name="email" type="email" autocomplete="email" required v-focus />
                <InputError :message="errors.email" />
            </div>
            <div class="grid gap-2">
                <Label for="current_password">Tu contraseña actual</Label>
                <PasswordInput id="current_password" name="current_password" autocomplete="current-password" required />
                <InputError :message="errors.current_password" />
            </div>
            <Button :disabled="processing"><Spinner v-if="processing" />Enviar código</Button>
        </Form>
    </div>
</template>
