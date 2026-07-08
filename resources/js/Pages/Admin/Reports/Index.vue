<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { Head, router } from '@inertiajs/vue3';

interface Metrics {
    totalTickets: number;
    resolved: number;
    open: number;
    inProgress: number;
    onHold: number;
    avgResolutionHours: number;
    resolutionRate: number;
}

interface DeptRow {
    name: string;
    total: number;
}

interface AgentRow {
    name: string;
    resolved: number;
    total: number;
}

const props = defineProps<{
    metrics: Metrics;
    departments: DeptRow[];
    maxDeptTotal: number;
    agents: AgentRow[];
    from: string | null;
    to: string | null;
}>();

const fromDate = ref(props.from ?? '');
const toDate = ref(props.to ?? '');

const applyFilters = () => {
    router.get(route('admin.reports.index'), {
        from: fromDate.value || null,
        to: toDate.value || null,
    }, { preserveState: true, replace: true });
};

const exportExcel = () => {
    window.open('/admin/reportes/exportar', '_blank');
};

const deptPercent = (total: number) => props.maxDeptTotal > 0 ? Math.round(total / props.maxDeptTotal * 100) : 0;
const deptBarWidth = (total: number) => props.maxDeptTotal > 0 ? Math.round(total / props.maxDeptTotal * 85) : 0;

const totalAll = computed(() => props.metrics.totalTickets);
const totalResolved = computed(() => props.metrics.resolved);
const totalOpen = computed(() => props.metrics.open + props.metrics.inProgress);
const totalOnHold = computed(() => props.metrics.onHold);

const resolvedPct = computed(() => totalAll.value > 0 ? Math.round(totalResolved.value / totalAll.value * 100) : 0);
const openPct = computed(() => totalAll.value > 0 ? Math.round(totalOpen.value / totalAll.value * 100) : 0);
const onHoldPct = computed(() => totalAll.value > 0 ? Math.round(totalOnHold.value / totalAll.value * 100) : 0);

const donutCircumference = 2 * Math.PI * 80;
const resolvedOffset = computed(() => donutCircumference - (donutCircumference * resolvedPct.value / 100));
const openOffset = computed(() => donutCircumference - (donutCircumference * openPct.value / 100));
const onHoldOffset = computed(() => donutCircumference - (donutCircumference * onHoldPct.value / 100));
</script>

<template>
    <AppLayout active-nav="reports">
        <Head title="Analytics &amp; Reports" />

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
                <nav class="flex items-center gap-2 text-label-sm text-outline mb-2">
                    <span class="text-primary font-semibold">Analytics</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="text-on-surface-variant">Overview</span>
                </nav>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Analytics &amp; Reports</h2>
                <p class="text-body-md text-outline dark:text-gray-400">M&eacute;tricas generales del sistema de tickets.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <DatePicker v-model="fromDate" />
                    <span class="text-outline">—</span>
                    <DatePicker v-model="toDate" />
                </div>
                <button @click="applyFilters" class="px-4 py-2 bg-deep-navy text-white rounded-lg text-label-sm hover:shadow-md transition-all">Filtrar</button>
                <button @click="exportExcel" class="px-4 py-2 border border-border-subtle rounded-lg text-label-sm text-on-surface hover:bg-surface-container-low transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">download</span>
                    Exportar
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-full -mr-12 -mt-12" />
                <div class="flex items-start gap-4 mb-4 relative">
                    <div class="p-2.5 bg-primary/10 rounded-lg text-deep-navy">
                        <span class="material-symbols-outlined">confirmation_number</span>
                    </div>
                    <div class="px-2 py-1 bg-success-green/10 text-success-green rounded-full text-[11px] font-bold">{{ metrics.resolutionRate }}%</div>
                </div>
                <p class="text-label-sm text-outline uppercase tracking-wider mb-1">Total Tickets</p>
                <p class="text-[32px] font-bold text-deep-navy dark:text-blue-300">{{ metrics.totalTickets }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-success-green/10 rounded-full -mr-12 -mt-12" />
                <div class="flex items-start gap-4 mb-4 relative">
                    <div class="p-2.5 bg-success-green/10 rounded-lg text-success-green">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                </div>
                <p class="text-label-sm text-outline uppercase tracking-wider mb-1">Resueltos</p>
                <p class="text-[32px] font-bold text-deep-navy dark:text-blue-300">{{ metrics.resolved }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-amber-100/30 rounded-full -mr-12 -mt-12" />
                <div class="flex items-start gap-4 mb-4 relative">
                    <div class="p-2.5 bg-amber-100/50 rounded-lg text-amber-800">
                        <span class="material-symbols-outlined">pending</span>
                    </div>
                </div>
                <p class="text-label-sm text-outline uppercase tracking-wider mb-1">Abiertos / En Proceso</p>
                <p class="text-[32px] font-bold text-deep-navy dark:text-blue-300">{{ totalOpen }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-surface-container-high rounded-full -mr-12 -mt-12" />
                <div class="flex items-start gap-4 mb-4 relative">
                    <div class="p-2.5 bg-surface-container rounded-lg text-on-surface-variant">
                        <span class="material-symbols-outlined">schedule</span>
                    </div>
                </div>
                <p class="text-label-sm text-outline uppercase tracking-wider mb-1">Tiempo Prom. Resoluci&oacute;n</p>
                <p class="text-[32px] font-bold text-deep-navy dark:text-blue-300">{{ metrics.avgResolutionHours }}h</p>
            </div>
        </div>

        <!-- Middle Row -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
            <!-- Department Performance -->
            <div class="lg:col-span-8 bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-headline-sm text-deep-navy dark:text-blue-300 mb-1">Tickets por Departamento</h3>
                <p class="text-label-sm text-outline mb-6">Distribuci&oacute;n de tickets por &aacute;rea.</p>
                <div class="space-y-5">
                    <div v-for="d in departments" :key="d.name">
                        <div class="flex justify-between text-label-sm mb-1.5">
                            <span class="font-semibold text-on-surface dark:text-gray-100">{{ d.name }}</span>
                            <span class="text-outline">{{ d.total }} tickets <span class="text-[11px] opacity-60">({{ deptPercent(d.total) }}%)</span></span>
                        </div>
                        <div class="w-full bg-surface-container-low dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-deep-navy dark:bg-blue-500 h-full rounded-full transition-all duration-1000" :style="{ width: deptBarWidth(d.total) + '%' }" />
                        </div>
                    </div>
                    <div v-if="!departments.length" class="text-center text-outline text-body-sm py-6">Sin datos para el per&iacute;odo.</div>
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-headline-sm text-deep-navy dark:text-blue-300 mb-1">Estado de Tickets</h3>
                <p class="text-label-sm text-outline mb-6">Distribuci&oacute;n actual.</p>
                <div class="flex flex-col items-center">
                    <svg class="w-44 h-44 -rotate-90" viewBox="0 0 192 192">
                        <circle cx="96" cy="96" r="80" fill="transparent" stroke="currentColor" stroke-width="14" class="text-surface-container-low dark:text-gray-700" />
                        <circle v-if="resolvedPct > 0" cx="96" cy="96" r="80" fill="transparent" stroke="currentColor" stroke-width="14" class="text-success-green"
                            :stroke-dasharray="donutCircumference" :stroke-dashoffset="resolvedOffset" stroke-linecap="round" />
                        <circle v-if="openPct > 0" cx="96" cy="96" r="80" fill="transparent" stroke="currentColor" stroke-width="14" class="text-deep-navy dark:text-blue-500"
                            :stroke-dasharray="donutCircumference" :stroke-dashoffset="openOffset" stroke-linecap="round"
                            style="transform-origin: center; transform: rotate(234deg);" />
                    </svg>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-3 mt-6 w-full">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-success-green shrink-0" />
                            <span class="text-label-sm text-outline">Resueltos ({{ resolvedPct }}%)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-deep-navy dark:bg-blue-500 shrink-0" />
                            <span class="text-label-sm text-outline">Abiertos ({{ openPct }}%)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-400 shrink-0" />
                            <span class="text-label-sm text-outline">En espera ({{ onHoldPct }}%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agents Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-border-subtle dark:border-gray-700">
                <h3 class="text-headline-sm text-deep-navy dark:text-blue-300">Rendimiento de Agentes</h3>
                <p class="text-label-sm text-outline mt-0.5">Tickets resueltos por agente.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                            <th class="px-6 py-4 text-label-sm uppercase text-outline font-bold tracking-wider">Agente</th>
                            <th class="px-6 py-4 text-label-sm uppercase text-outline font-bold tracking-wider text-center">Resueltos</th>
                            <th class="px-6 py-4 text-label-sm uppercase text-outline font-bold tracking-wider text-center">Total</th>
                            <th class="px-6 py-4 text-label-sm uppercase text-outline font-bold tracking-wider">Rendimiento</th>
                            <th class="px-6 py-4 text-label-sm uppercase text-outline font-bold tracking-wider text-right">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                        <tr v-for="a in agents" :key="a.name" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-deep-navy dark:bg-blue-700 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                        {{ a.name.charAt(0) }}{{ a.name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() }}
                                    </div>
                                    <span class="font-semibold text-body-sm text-on-surface dark:text-gray-100">{{ a.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-body-sm text-on-surface dark:text-gray-100">{{ a.resolved }}</td>
                            <td class="px-6 py-4 text-center text-body-sm text-outline">{{ a.total }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3 max-w-[200px]">
                                    <div class="flex-1 bg-surface-container-low dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                        <div class="bg-success-green h-full rounded-full" :style="{ width: (a.total > 0 ? Math.round(a.resolved / a.total * 100) : 0) + '%' }" />
                                    </div>
                                    <span class="text-label-sm font-bold text-success-green shrink-0">{{ a.total > 0 ? Math.round(a.resolved / a.total * 100) : 0 }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-success-green/10 text-success-green">Activo</span>
                            </td>
                        </tr>
                        <tr v-if="!agents.length">
                            <td colspan="5" class="px-6 py-8 text-center text-outline text-body-sm">Sin datos de agentes.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
