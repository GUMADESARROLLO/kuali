<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{ department?: { id: number; name: string; description: string | null; is_active: boolean } }>();

const isEditing = !!props.department;
const form = useForm({
    name: props.department?.name ?? '',
    description: props.department?.description ?? '',
    is_active: props.department?.is_active ?? true,
});

const submit = () => {
    if (isEditing) {
        form.put(route('admin.departments.update', props.department!.id));
    } else {
        form.post(route('admin.departments.store'));
    }
};
</script>

<template>
    <AppLayout active-nav="departments">
        <Head :title="isEditing ? 'Editar Departamento' : 'Nuevo Departamento'" />

        <div class="max-w-2xl">
            <div class="mb-8">
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">{{ isEditing ? 'Editar' : 'Nuevo' }} Departamento</h2>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-8 space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nombre</label>
                    <input v-model="form.name" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    <p v-if="form.errors.name" class="text-error text-label-sm">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Descripción</label>
                    <textarea v-model="form.description" rows="3" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none" />
                    <p v-if="form.errors.description" class="text-error text-label-sm">{{ form.errors.description }}</p>
                </div>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-primary focus:ring-primary" />
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Activo</span>
                </label>

                <div class="flex items-center justify-end gap-4 pt-4 border-t border-border-subtle dark:border-gray-700">
                    <a :href="route('admin.departments.index')" class="px-4 py-2 text-label-md text-outline hover:text-on-surface transition-colors">Cancelar</a>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-deep-navy text-white rounded-lg text-label-md disabled:opacity-50">
                        {{ isEditing ? 'Actualizar' : 'Crear' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
