<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import PriorityBadge from '@/Components/PriorityBadge.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
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
                <h2 class="text-display-lg text-deep-navy mb-1">Dashboard IT</h2>
                <p class="text-body-md text-outline">Metricas generales del sistema.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl border border-border-subtle p-5">
                <div class="text-sm text-outline">Abiertos</div>
                <div class="mt-1 text-3xl font-bold text-deep-navy">
                    {{ stats.abiertos }}
                </div>
            </div>
            <div class="bg-white rounded-xl border border-border-subtle p-5">
                <div class="text-sm text-outline">En proceso</div>
                <div class="mt-1 text-3xl font-bold text-deep-navy">
                    {{ stats.en_proceso }}
                </div>
            </div>
            <div class="bg-white rounded-xl border border-border-subtle p-5">
                <div class="text-sm text-outline">Resueltos</div>
                <div class="mt-1 text-3xl font-bold text-success-green">
                    {{ stats.resueltos }}
                </div>
            </div>
            <div class="bg-white rounded-xl border border-border-subtle p-5">
                <div class="text-sm text-outline">Vencidos</div>
                <div class="mt-1 text-3xl font-bold text-error">
                    {{ stats.vencidos }}
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-border-subtle shadow-sm">
            <div class="px-6 py-4 border-b border-border-subtle">
                <h3 class="text-headline-sm font-semibold text-on-surface">
                    Tickets recientes
                </h3>
            </div>
            <div v-if="recent.length === 0" class="p-8 text-center text-outline text-body-md">
                Sin tickets a&uacute;n.
            </div>
            <table v-else class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low border-b border-border-subtle">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">#</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">T&iacute;tulo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Depto</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Categor&iacute;a</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Prioridad</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Asignado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle">
                    <tr v-for="t in recent" :key="t.id" class="hover:bg-surface-container-lowest transition-colors">
                        <td class="px-6 py-3 text-body-sm font-mono text-on-surface">{{ t.ticket_number }}</td>
                        <td class="px-6 py-3 text-body-sm text-on-surface truncate max-w-xs">{{ t.title }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline">{{ t.department?.name ?? '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline">{{ t.category?.name ?? '—' }} / {{ t.subcategory?.name ?? '—' }}</td>
                        <td class="px-6 py-3">
                            <PriorityBadge :priority="t.priority" />
                        </td>
                        <td class="px-6 py-3">
                            <StatusBadge :status="t.status" />
                        </td>
                        <td class="px-6 py-3 text-body-sm text-outline">{{ t.assigned_agent?.name ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
