<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import PriorityBadge from '@/Components/PriorityBadge.vue';
import StatusPill from '@/Components/StatusPill.vue';
import PaginationBar from '@/Components/PaginationBar.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Stats {
    abiertos: number;
    en_proceso: number;
    resueltos: number;
    vencidos: number;
}

interface TicketRow {
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

interface PaginationMeta {
    data: TicketRow[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number;
    to: number;
    total: number;
    current_page: number;
    last_page: number;
}

const props = defineProps<{
    stats: Stats;
    tickets: PaginationMeta;
}>();

const search = ref('');
const statusFilter = ref('');
const priorityFilter = ref('');
const today = new Date().toISOString().split('T')[0];
const dateFrom = ref(today);
const dateTo = ref(today);
const perPage = ref(10);
let searchTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (statusFilter.value) params.status = statusFilter.value;
    if (priorityFilter.value) params.priority = priorityFilter.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;
    if (perPage.value !== 10) params.per_page = String(perPage.value);
    router.get(route('user.dashboard'), params, { preserveState: true, preserveScroll: true, replace: true });
};

const onSearchInput = () => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
};

const statuses = [
    { value: 'abierto', label: 'Abierto' },
    { value: 'en_proceso', label: 'En Proceso' },
    { value: 'en_espera', label: 'En Espera' },
    { value: 'resuelto', label: 'Resuelto' },
    { value: 'cerrado', label: 'Cerrado' },
    { value: 'cancelado', label: 'Cancelado' },
];

const priorities = [
    { value: 'baja', label: 'Baja' },
    { value: 'media', label: 'Media' },
    { value: 'alta', label: 'Alta' },
    { value: 'urgente', label: 'Urgente' },
];
</script>

<template>
    <AppLayout active-nav="dashboard">
        <Head title="Mis Tickets" />

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Mis Tickets</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Tickets de tu departamento.</p>
            </div>
            <Link
                :href="route('user.tickets.create')"
                class="bg-deep-navy hover:bg-primary text-white px-6 py-2.5 rounded-lg text-label-md flex items-center gap-2 transition-all shadow-sm hover:shadow-md active:scale-95"
            >
                <span class="material-symbols-outlined text-[18px]">add</span>
                Nuevo Ticket
            </Link>
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

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-subtle dark:border-gray-700">
                <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100">Tickets</h3>
            </div>

            <!-- Filters -->
            <div class="p-4 flex flex-wrap items-center gap-4 bg-surface-container-lowest dark:bg-gray-800/50 border-b border-border-subtle dark:border-gray-700">
                <div class="relative w-full sm:flex-1 sm:min-w-[200px] shrink-0">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input
                        v-model="search"
                        @input="onSearchInput"
                        class="w-full pl-10 pr-4 py-2 border border-border-subtle dark:border-gray-600 rounded-lg text-body-sm bg-white dark:bg-gray-700 dark:text-gray-200 focus:ring-primary focus:border-primary outline-none"
                        placeholder="Buscar por # o título..."
                    />
                </div>

                <select v-model="statusFilter" @change="applyFilters"
                    class="w-full sm:w-auto shrink-0 border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3 focus:ring-primary focus:border-primary outline-none">
                    <option value="">Todos los estados</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>

                <select v-model="priorityFilter" @change="applyFilters"
                    class="w-full sm:w-auto shrink-0 border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3 focus:ring-primary focus:border-primary outline-none">
                    <option value="">Todas las prioridades</option>
                    <option v-for="p in priorities" :key="p.value" :value="p.value">{{ p.label }}</option>
                </select>

                <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                    <div class="w-full sm:w-36">
                        <DatePicker v-model="dateFrom" placeholder="Inicio" />
                    </div>
                    <span class="text-outline text-label-sm shrink-0">—</span>
                    <div class="w-full sm:w-36">
                        <DatePicker v-model="dateTo" placeholder="Termina" />
                    </div>
                </div>

                <select v-model="perPage" @change="applyFilters"
                    class="w-full sm:w-auto shrink-0 border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3 focus:ring-primary focus:border-primary outline-none">
                    <option :value="10">10 / pág</option>
                    <option :value="25">25 / pág</option>
                    <option :value="50">50 / pág</option>
                </select>
            </div>

            <!-- Table -->
            <div v-if="tickets.data.length === 0" class="p-8 text-center text-outline dark:text-gray-400 text-body-md">
                Sin tickets.
            </div>
            <table v-else class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">#</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">T&iacute;tulo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Categor&iacute;a</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Prioridad</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Asignado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="t in tickets.data" :key="t.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50 transition-colors cursor-pointer" @click="router.get(route('admin.tickets.show', { ticket: t.id }))">
                        <td class="px-6 py-3 text-body-sm font-mono text-on-surface dark:text-gray-100">{{ t.ticket_number }}</td>
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100 truncate max-w-xs">{{ t.title }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ t.category?.name ?? '—' }} / {{ t.subcategory?.name ?? '—' }}</td>
                        <td class="px-6 py-3"><PriorityBadge :priority="t.priority" /></td>
                        <td class="px-6 py-3"><StatusPill :status="t.status" /></td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ t.assigned_agent?.name ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>

            <PaginationBar
                :links="tickets.links"
                :from="tickets.from"
                :to="tickets.to"
                :total="tickets.total"
            />
        </div>
    </AppLayout>
</template>
