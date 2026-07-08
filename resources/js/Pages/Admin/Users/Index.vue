<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface UserRow {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    is_active: boolean;
    department: string | null;
    roles: string;
}

defineProps<{ users: UserRow[] }>();

const destroy = (id: number) => {
    if (!confirm('¿Eliminar este usuario?')) return;
    router.delete(route('admin.users.destroy', id));
};
</script>

<template>
    <AppLayout active-nav="users">
        <Head title="Usuarios" />

        <div class="flex items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Usuarios</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Gestiona los usuarios y sus roles.</p>
            </div>
            <Link :href="route('admin.users.create')" class="bg-deep-navy hover:bg-primary text-white px-5 py-2.5 rounded-lg text-label-md flex items-center gap-2">+ Nuevo</Link>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Email</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Depto</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Roles</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Activo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="u in users" :key="u.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100">{{ u.name }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ u.email }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ u.department || '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ u.roles || '—' }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold" :class="u.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">{{ u.is_active ? 'Sí' : 'No' }}</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <Link :href="route('admin.users.edit', u.id)" class="text-label-sm text-primary hover:underline">Editar</Link>
                                <button @click="destroy(u.id)" class="text-label-sm text-error hover:underline">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
