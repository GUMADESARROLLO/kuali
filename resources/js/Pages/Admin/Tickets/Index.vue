<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import TicketTable from '@/Components/TicketTable.vue';
import PaginationBar from '@/Components/PaginationBar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Ticket {
    id: number;
    ticket_number: string;
    title: string;
    priority: string;
    status: string;
    created_at: string;
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

const props = defineProps<{
    tickets: PaginationMeta;
    activeTab: string;
}>();

const currentTab = ref(props.activeTab || 'all');

const tabs = [
    { key: 'all', label: 'ALL' },
    { key: 'abierto', label: 'OPEN' },
    { key: 'en_proceso', label: 'IN PROGRESS' },
    { key: 'en_espera', label: 'ON-HOLD' },
    { key: 'resuelto', label: 'RESOLVED' },
    { key: 'cerrado', label: 'CLOSED' },
];

const switchTab = (tab: string) => {
    currentTab.value = tab;
    router.get(
        route('admin.tickets.index'),
        { status: tab === 'all' ? undefined : tab },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const getTabCount = (key: string): number => {
    if (key === 'all') return props.tickets.total;
    return key === props.activeTab ? props.tickets.data.length : 0;
};
</script>

<template>
    <AppLayout active-nav="tickets">
        <Head title="All Tickets - Kuali" />

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy mb-1">All Tickets</h2>
                <p class="text-body-md text-outline">Manage and respond to customer support requests.</p>
            </div>
            <Link
                :href="route('admin.tickets.create')"
                class="bg-deep-navy hover:bg-primary text-white px-6 py-2.5 rounded-lg text-label-md flex items-center gap-2 transition-all shadow-sm hover:shadow-md active:scale-95"
            >
                <span class="material-symbols-outlined text-[18px]">add</span>
                Create Ticket
            </Link>
        </div>

        <!-- Content Card -->
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden flex flex-col">
            <!-- Status Filter Tabs -->
            <div class="flex items-center gap-6 px-6 pt-4 border-b border-border-subtle overflow-x-auto no-scrollbar">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="switchTab(tab.key)"
                    :class="[
                        'text-label-md pb-4 flex items-center gap-2 whitespace-nowrap transition-colors relative',
                        currentTab === tab.key ? 'text-primary active-tab' : 'text-outline hover:text-on-surface-variant'
                    ]"
                >
                    {{ tab.label }}
                    <span
                        :class="[
                            'px-1.5 py-0.5 rounded text-[10px]',
                            currentTab === tab.key
                                ? 'bg-primary-container text-on-primary-container'
                                : 'bg-surface-container-highest text-outline'
                        ]"
                    >
                        {{ getTabCount(tab.key) }}
                    </span>
                </button>
            </div>

            <!-- Filter Bar -->
            <div class="p-4 flex flex-wrap items-center justify-between gap-4 bg-surface-container-lowest">
                <div class="flex flex-wrap items-center gap-3">
                    <button class="flex items-center gap-2 px-3 py-1.5 border border-border-subtle rounded-lg text-body-sm hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined text-[18px]">filter_list</span>
                        Add Filter
                    </button>
                    <div class="flex items-center gap-2 px-3 py-1.5 border border-border-subtle rounded-lg text-body-sm bg-white">
                        <span class="text-outline">Priority:</span>
                        <span class="font-medium">All</span>
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 border border-border-subtle rounded-lg text-body-sm bg-white">
                        <span class="text-outline">Status:</span>
                        <span class="font-medium">All</span>
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                    <button class="text-error text-label-sm hover:underline ml-2">Reset</button>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-label-sm text-outline">Show</span>
                    <select class="border-border-subtle rounded-lg text-label-sm py-1 focus:ring-primary focus:border-primary">
                        <option>10 / page</option>
                        <option>25 / page</option>
                        <option>50 / page</option>
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
