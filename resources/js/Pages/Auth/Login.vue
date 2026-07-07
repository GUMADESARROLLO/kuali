<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import LoginLayout from '@/Layouts/LoginLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import KualiButton from '@/Components/KualiButton.vue';
import ClockWidget from '@/Components/ClockWidget.vue';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <LoginLayout>
        <Head title="Iniciar Sesión" />

        <template #header>
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Iniciar Sesión</h1>
                <p class="text-sm text-gray-600 mb-4">Ingresa tus credenciales para gestionar tus requerimientos técnicos.</p>
                <p class="text-sm text-gray-500">
                    ¿Necesitas una cuenta de Kuali?
                </p>
            </div>
        </template>

        <template #form>
            <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <FormInput
                    v-model="form.email"
                    label="Correo Corporativo"
                    type="email"
                    placeholder="Ingresa tu correo electrónico"
                    badge="Acceso Restringido a Colaboradores"
                    autocomplete="username"
                    autofocus
                    :error="form.errors.email"
                />

                <FormInput
                    v-model="form.password"
                    label="Contraseña"
                    type="password"
                    placeholder="Ingresa tu contraseña"
                    autocomplete="current-password"
                    :error="form.errors.password"
                />

                <KualiButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                    Acceder al Portal
                </KualiButton>
            </form>
        </template>

        <template #footer>
            <div class="mt-12 pt-6 border-t border-gray-100">
                <ClockWidget />
            </div>
        </template>
    </LoginLayout>
</template>
