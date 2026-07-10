<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import TicketTable from '@/Components/TicketTable.vue';
import PaginationBar from '@/Components/PaginationBar.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Dept { id: number; name: string }

interface Ticket {
    id: number;
    ticket_number: string;
    title: string;
    priority: string;
    status: string;
    created_at: string;
    department?: Dept | null;
    assigned_agent?: { name: string } | null;
}

interface PaginationMeta {
    data: Ticket[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number;
    to: number;
    total: number;
    current_page: number;
    last_page: number;
}

interface Filters { search: string; date_from: string; date_to: string; department_id: string; escalated: string }

const props = defineProps<{
    tickets: PaginationMeta;
    activePerPage?: number;
    departments?: Dept[];
    filters?: Filters;
}>();

const currentPerPage = ref(props.activePerPage || 10);
const searchQuery = ref(props.filters?.search ?? '');
const dateFrom = ref(props.filters?.date_from ?? new Date().toISOString().split('T')[0]);
const dateTo = ref(props.filters?.date_to ?? new Date().toISOString().split('T')[0]);
const departmentId = ref(props.filters?.department_id ?? '');
const escalatedFilter = ref(props.filters?.escalated ?? '');

const doSearch = () => {
    const params: Record<string, string> = {};
    if (searchQuery.value) params.search = searchQuery.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;
    if (departmentId.value) params.department_id = departmentId.value;
    if (escalatedFilter.value) params.escalated = escalatedFilter.value;
    if (currentPerPage.value !== 10) params.per_page = String(currentPerPage.value);
    router.get(route('admin.tickets.index'), params, { preserveState: true, preserveScroll: true, replace: true });
};

const changePerPage = (e: Event) => {
    currentPerPage.value = Number((e.target as HTMLSelectElement).value);
    doSearch();
};
</script>

<template>
    <AppLayout active-nav="tickets">
        <Head title="All Tickets - Kuali" />

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">All Tickets</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Manage and respond to customer support requests.</p>
            </div>
            <Link
                :href="route('admin.tickets.create')"
                class="bg-deep-navy hover:bg-primary text-white px-6 py-2.5 rounded-lg text-label-md flex items-center gap-2 transition-all shadow-sm hover:shadow-md active:scale-95"
            >
                <span class="material-symbols-outlined text-[18px]">add</span>
                Create Ticket
            </Link>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-visible flex flex-col">
<div class="p-4 grid grid-cols-1 sm:flex sm:flex-wrap items-center gap-3 bg-surface-container-lowest dark:bg-gray-800/50">
                <div class="relative w-full sm:flex-1 sm:min-w-[200px] shrink-0">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input
                        v-model="searchQuery"
                        @keydown.enter="doSearch"
                        class="w-full pl-10 pr-4 py-2 border border-border-subtle dark:border-gray-600 rounded-lg text-body-sm bg-white dark:bg-gray-700 dark:text-gray-200 focus:ring-primary focus:border-primary outline-none"
                        placeholder="Search by ticket #, title, or description..."
                    />
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                    <div class="w-full sm:w-36">
                        <DatePicker v-model="dateFrom" placeholder="Inicio" />
                    </div>
                    <span class="text-outline text-label-sm shrink-0">—</span>
                    <div class="w-full sm:w-36">
                        <DatePicker v-model="dateTo" placeholder="Termina" />
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <select v-model="departmentId"
                        class="flex-1 sm:w-auto shrink-0 border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3 focus:ring-primary focus:border-primary outline-none">
                        <option value="">Todos los deptos</option>
                        <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                    </select>

                    <select v-model="escalatedFilter"
                        class="flex-1 sm:w-auto shrink-0 border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3 focus:ring-primary focus:border-primary outline-none">
                        <option value="">Todos</option>
                        <option value="yes">Vencidos (SLA)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button @click="doSearch"
                        class="flex-1 sm:flex-initial px-4 py-2 bg-deep-navy text-white rounded-lg text-label-sm hover:bg-primary transition-all shrink-0">
                        Filtrar
                    </button>

                    <select
                        :value="currentPerPage"
                        @change="changePerPage"
                        class="w-24 sm:w-auto border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3 focus:ring-primary focus:border-primary outline-none"
                    >
                    <option :value="10">10 / page</option>
                    <option :value="25">25 / page</option>
                    <option :value="50">50 / page</option>
                </select>
            </div>
            </div>

            <!-- Table -->
            <TicketTable :tickets="tickets.data" />

            <!-- Pagination -->
            <PaginationBar
                :links="tickets.links"
                :from="tickets.from"
                :to="tickets.to"
                :total="tickets.total"
            />
        </div>
    </AppLayout>
</template>
