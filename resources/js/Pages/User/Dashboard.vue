<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Stats {
    abiertos: number;
    en_proceso: number;
    resueltos: number;
}

interface TicketItem {
    id: number;
    ticket_number: string;
    title: string;
    status: string;
    priority: string;
    created_at: string;
}

defineProps<{
    stats: Stats;
    tickets: TicketItem[];
}>();

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

const priorityClass = (p: string): string => {
    const map: Record<string, string> = {
        baja: 'bg-gray-100 text-gray-700',
        media: 'bg-blue-100 text-blue-700',
        alta: 'bg-orange-100 text-orange-700',
        urgente: 'bg-red-100 text-red-700',
    };
    return map[p] ?? 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head title="Mis tickets" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Mis tickets
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                        <div class="text-sm text-gray-500 dark:text-gray-400">Resueltos / Cerrados</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ stats.resueltos }}
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">
                            Tickets recientes
                        </h3>
                        <a
                            href="/mis-tickets/nuevo"
                            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700"
                        >
                            Nuevo ticket
                        </a>
                    </div>
                    <div v-if="tickets.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        Aún no has creado tickets.
                    </div>
                    <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li
                            v-for="t in tickets"
                            :key="t.id"
                            class="px-5 py-3 flex items-center justify-between"
                        >
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ t.ticket_number }} · {{ t.title }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ new Date(t.created_at).toLocaleDateString() }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span :class="['px-2 py-0.5 text-xs rounded-full', priorityClass(t.priority)]">
                                    {{ t.priority }}
                                </span>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                    {{ statusLabel(t.status) }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
