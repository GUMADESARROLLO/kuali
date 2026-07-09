<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface Department {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    created_at: string;
}

defineProps<{ departments: Department[] }>();

const destroy = (id: number) => {
    if (!confirm('¿Eliminar este departamento?')) return;
    router.delete(route('admin.departments.destroy', id));
};

const toggleActive = (d: Department) => {
    router.put(route('admin.departments.update', d.id), {
        name: d.name, description: d.description, is_active: !d.is_active,
    }, { preserveScroll: true });
};
</script>

<template>
    <AppLayout active-nav="departments">
        <Head title="Departamentos" />

        <div class="flex items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Departamentos</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Gestiona los departamentos de la empresa.</p>
            </div>
            <Link :href="route('admin.departments.create')" class="bg-deep-navy hover:bg-primary text-white px-5 py-2.5 rounded-lg text-label-md flex items-center gap-2">+ Nuevo</Link>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Descripción</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Activo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="d in departments" :key="d.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100">{{ d.name }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400 font-mono">{{ d.slug }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ d.description || '—' }}</td>
                        <td class="px-6 py-3">
                            <button @click="toggleActive(d)" class="px-2 py-0.5 rounded text-[11px] font-bold" :class="d.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ d.is_active ? 'Sí' : 'No' }}
                            </button>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <Link :href="route('admin.departments.edit', d.id)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-deep-navy dark:hover:text-blue-300 hover:bg-surface-container dark:hover:bg-gray-700 rounded transition-all" title="Editar">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </Link>
                                <button @click="destroy(d.id)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-error hover:bg-error-container/20 dark:hover:bg-red-900/30 rounded transition-all" title="Eliminar">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
