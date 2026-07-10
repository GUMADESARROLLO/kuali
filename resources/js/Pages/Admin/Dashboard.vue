<script setup lang="ts">
import { computed, ref } from 'vue';
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

const props = defineProps<{
    stats: Stats;
    recent: RecentTicket[];
}>();

const search = ref('');

const filteredRecent = computed(() => {
    if (!search.value) return props.recent;
    const q = search.value.toLowerCase();
    return props.recent.filter(t =>
        t.ticket_number.toLowerCase().includes(q) ||
        t.title.toLowerCase().includes(q) ||
        t.department?.name?.toLowerCase().includes(q) ||
        t.category?.name?.toLowerCase().includes(q) ||
        t.assigned_agent?.name?.toLowerCase().includes(q)
    );
});
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
            <div class="px-6 py-4 border-b border-border-subtle dark:border-gray-700 flex items-center justify-between gap-4">
                <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 shrink-0">Tickets recientes</h3>
                <div class="relative max-w-xs w-full">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[16px]">search</span>
                    <input
                        v-model="search"
                        class="w-full pl-9 pr-3 py-1.5 border border-border-subtle dark:border-gray-600 rounded-lg text-body-sm bg-surface-container-low dark:bg-gray-700 dark:text-gray-200 focus:ring-primary focus:border-primary outline-none"
                        placeholder="Search recent tickets..."
                    />
                </div>
            </div>
            <div v-if="filteredRecent.length === 0" class="p-8 text-center text-outline dark:text-gray-400 text-body-md">
                {{ search ? 'No tickets match your search.' : 'Sin tickets a&uacute;n.' }}
            </div>
            <div v-else class="overflow-x-auto">
            <table class="w-full text-left min-w-[640px]">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">#</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">T&iacute;tulo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider hidden md:table-cell">Depto</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider hidden lg:table-cell">Categor&iacute;a</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Prioridad</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider hidden sm:table-cell">Asignado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="t in filteredRecent" :key="t.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50 transition-colors cursor-pointer" @click="router.get(route('admin.tickets.show', { ticket: t.id }))">
                        <td class="px-6 py-3 text-body-sm font-mono text-on-surface dark:text-gray-100">{{ t.ticket_number }}</td>
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100 max-w-[200px] truncate">{{ t.title }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400 hidden md:table-cell">{{ t.department?.name ?? '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400 hidden lg:table-cell">{{ t.category?.name ?? '—' }} / {{ t.subcategory?.name ?? '—' }}</td>
                        <td class="px-6 py-3"><PriorityBadge :priority="t.priority" /></td>
                        <td class="px-6 py-3"><StatusPill :status="t.status" /></td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400 hidden sm:table-cell">{{ t.assigned_agent?.name ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </AppLayout>
</template>
