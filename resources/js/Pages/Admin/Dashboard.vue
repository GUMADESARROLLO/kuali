<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Stats {
    abiertos: number;
    en_proceso: number;
    resueltos: number;
    vencidos: number;
}

interface RecentTicket {
    id: number;
    ticket_number: string;
    title: string;
    status: string;
    priority: string;
    created_at: string;
    user?: { name: string };
    department?: { name: string };
    category?: { name: string };
    subcategory?: { name: string };
    assigned_agent?: { name: string };
}

defineProps<{
    stats: Stats;
    recent: RecentTicket[];
}>();

const statusClass = (s: string): string => {
    const map: Record<string, string> = {
        abierto: 'bg-blue-100 text-blue-700',
        en_proceso: 'bg-yellow-100 text-yellow-700',
        en_espera: 'bg-gray-100 text-gray-700',
        resuelto: 'bg-green-100 text-green-700',
        cerrado: 'bg-gray-200 text-gray-700',
        cancelado: 'bg-red-100 text-red-700',
    };
    return map[s] ?? 'bg-gray-100 text-gray-700';
};

const priorityClass = (p: string): string => {
    const map: Record<string, string> = {
        baja: 'bg-gray-100 text-gray-700',
        media: 'bg-blue-100 text-blue-700',
        alta: 'bg-orange-100 text-orange-700',
        urgente: 'bg-red-100 text-red-700',
    };
    return map[p] ?? 'bg-gray-100 text-gray-700';
};

const statusLabel = (s: string): string => {
    const map: Record<string, string> = {
        abierto: 'Abierto',
        en_proceso: 'En proceso',
        en_espera: 'En espera',
        resuelto: 'Resuelto',
        cerrado: 'Cerrado',
        cancelado: 'Cancelado',
    };
    return map[s] ?? s;
};
</script>

<template>
    <Head title="Dashboard IT" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Dashboard IT
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Abiertos</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ stats.abiertos }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-sm text-gray-500 dark:text-gray-400">En proceso</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ stats.en_proceso }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Resueltos</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ stats.resueltos }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Vencidos</div>
                        <div class="mt-1 text-3xl font-semibold text-red-600">
                            {{ stats.vencidos }}
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">
                            Tickets recientes
                        </h3>
                    </div>
                    <div v-if="recent.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        Sin tickets aún.
                    </div>
                    <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                                <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Título</th>
                                <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Departamento</th>
                                <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Categoría</th>
                                <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Prioridad</th>
                                <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                                <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Asignado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="t in recent" :key="t.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-5 py-2 text-sm font-mono text-gray-900 dark:text-gray-100">
                                    {{ t.ticket_number }}
                                </td>
                                <td class="px-5 py-2 text-sm text-gray-900 dark:text-gray-100 truncate max-w-xs">
                                    {{ t.title }}
                                </td>
                                <td class="px-5 py-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ t.department?.name ?? '—' }}
                                </td>
                                <td class="px-5 py-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ t.category?.name ?? '—' }} / {{ t.subcategory?.name ?? '—' }}
                                </td>
                                <td class="px-5 py-2">
                                    <span :class="['px-2 py-0.5 text-xs rounded-full', priorityClass(t.priority)]">
                                        {{ t.priority }}
                                    </span>
                                </td>
                                <td class="px-5 py-2">
                                    <span :class="['px-2 py-0.5 text-xs rounded-full', statusClass(t.status)]">
                                        {{ statusLabel(t.status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ t.assigned_agent?.name ?? '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
