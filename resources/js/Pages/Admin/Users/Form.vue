<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Dept { id: number; name: string; company?: { id: number; name: string } | null }

const props = defineProps<{
    user?: { id: number; name: string; email: string; department_id: number | null; phone: string | null; is_active: boolean; role: string | null };
    departments: Dept[];
    roles: string[];
}>();

const isEditing = !!props.user;
const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    department_id: props.user?.department_id ?? null,
    phone: props.user?.phone ?? '',
    is_active: props.user?.is_active ?? true,
    role: props.user?.role ?? 'usuario',
});

const submit = () => {
    if (isEditing) {
        form.put(route('admin.users.update', props.user!.id));
    } else {
        form.post(route('admin.users.store'));
    }
};
</script>

<template>
    <AppLayout active-nav="users">
        <Head :title="isEditing ? 'Editar Usuario' : 'Nuevo Usuario'" />

        <div class="max-w-2xl">
            <div class="mb-8">
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">{{ isEditing ? 'Editar' : 'Nuevo' }} Usuario</h2>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-8 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nombre</label>
                        <input v-model="form.name" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        <p v-if="form.errors.name" class="text-error text-label-sm">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Email</label>
                        <input v-model="form.email" type="email" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        <p v-if="form.errors.email" class="text-error text-label-sm">{{ form.errors.email }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Contraseña <span v-if="isEditing" class="text-outline font-normal">(dejar vacío para mantener)</span></label>
                    <input v-model="form.password" type="password" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    <p v-if="form.errors.password" class="text-error text-label-sm">{{ form.errors.password }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Departamento</label>
                        <select v-model="form.department_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option :value="null">Sin departamento</option>
                            <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.company?.name ?? '?' }} - {{ d.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Teléfono</label>
                        <input v-model="form.phone" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Rol</label>
                        <select v-model="form.role" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                        </select>
                        <p v-if="form.errors.role" class="text-error text-label-sm">{{ form.errors.role }}</p>
                    </div>
                    <div class="flex items-end pb-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-primary focus:ring-primary" />
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Activo</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-4 border-t border-border-subtle dark:border-gray-700">
                    <a :href="route('admin.users.index')" class="px-4 py-2 text-label-md text-outline hover:text-on-surface transition-colors">Cancelar</a>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-deep-navy text-white rounded-lg text-label-md disabled:opacity-50">
                        {{ isEditing ? 'Actualizar' : 'Crear' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
