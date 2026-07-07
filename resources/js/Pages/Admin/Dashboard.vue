<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import PriorityBadge from '@/Components/PriorityBadge.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { Head, router } from '@inertiajs/vue3';

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
    department?: { name: string };
    category?: { name: string };
    subcategory?: { name: string };
    assigned_agent?: { name: string };
}

defineProps<{
    stats: Stats;
    recent: RecentTicket[];
}>();
</script>

<template>
    <AppLayout active-nav="dashboard">
        <Head title="Dashboard IT" />

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Dashboard IT</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Metricas generales del sistema.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 p-5">
                <div class="text-sm text-outline dark:text-gray-400">Abiertos</div>
                <div class="mt-1 text-3xl font-bold text-deep-navy dark:text-blue-300">{{ stats.abiertos }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 p-5">
                <div class="text-sm text-outline dark:text-gray-400">En proceso</div>
                <div class="mt-1 text-3xl font-bold text-deep-navy dark:text-blue-300">{{ stats.en_proceso }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 p-5">
                <div class="text-sm text-outline dark:text-gray-400">Resueltos</div>
                <div class="mt-1 text-3xl font-bold text-success-green">{{ stats.resueltos }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 p-5">
                <div class="text-sm text-outline dark:text-gray-400">Vencidos</div>
                <div class="mt-1 text-3xl font-bold text-error">{{ stats.vencidos }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm">
            <div class="px-6 py-4 border-b border-border-subtle dark:border-gray-700">
                <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100">Tickets recientes</h3>
            </div>
            <div v-if="recent.length === 0" class="p-8 text-center text-outline dark:text-gray-400 text-body-md">
                Sin tickets a&uacute;n.
            </div>
            <table v-else class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">#</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">T&iacute;tulo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Depto</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Categor&iacute;a</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Prioridad</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Asignado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="t in recent" :key="t.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50 transition-colors cursor-pointer" @click="router.get(route('admin.tickets.show', { ticket: t.id }))">
                        <td class="px-6 py-3 text-body-sm font-mono text-on-surface dark:text-gray-100">{{ t.ticket_number }}</td>
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100 truncate max-w-xs">{{ t.title }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ t.department?.name ?? '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ t.category?.name ?? '—' }} / {{ t.subcategory?.name ?? '—' }}</td>
                        <td class="px-6 py-3"><PriorityBadge :priority="t.priority" /></td>
                        <td class="px-6 py-3"><StatusPill :status="t.status" /></td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ t.assigned_agent?.name ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
