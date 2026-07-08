<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import PriorityBadge from '@/Components/PriorityBadge.vue';
import StatusPill from '@/Components/StatusPill.vue';

interface TicketUser { id: number; name: string; email: string }
interface Agent { id: number; name: string }
interface Ticket {
    id: number; ticket_number: string; title: string; status: string; priority: string;
    created_at: string; resolved_at: string | null; closed_at: string | null;
    user: TicketUser; department: { name: string } | null;
    category: { name: string } | null; subcategory: { name: string } | null;
    assigned_agent: TicketUser | null;
}

const props = defineProps<{ ticket: Ticket; agents: Agent[] }>();

const auth = usePage().props.auth as any;
const userRoles = (auth?.roles ?? []) as string[];
const isAgent = userRoles.includes('agente_it') || userRoles.includes('admin_it');

const showAssign = ref(false);
const selectedAgent = ref<number | null>(null);

const changeStatus = (e: Event) => {
    const status = (e.target as HTMLSelectElement).value;
    router.post(route('admin.tickets.status', { ticket: props.ticket.id }), { status }, {
        preserveScroll: true, preserveState: false,
    });
};

const assign = () => {
    if (!selectedAgent.value) return;
    router.post(route('admin.tickets.assign', { ticket: props.ticket.id }), {
        assigned_to: selectedAgent.value,
    }, { preserveScroll: true, onSuccess: () => { showAssign.value = false; selectedAgent.value = null; } });
};

const formatDate = (d: string): string => {
    const dt = new Date(d);
    return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const timeAgo = (d: string): string => {
    const diff = Date.now() - new Date(d).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 60) return mins + ' mins ago';
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return hrs + ' hours ago';
    return Math.floor(hrs / 24) + ' days ago';
};
</script>

<template>
    <aside class="w-80 h-full bg-white dark:bg-gray-800 border-l border-border-subtle dark:border-gray-700 overflow-y-auto p-6 hidden xl:block shrink-0">
        <h3 class="text-headline-sm text-deep-navy dark:text-blue-300 mb-6">Ticket Details</h3>
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <p class="text-[10px] uppercase font-bold text-outline dark:text-gray-400 tracking-widest">Status</p>
                    <StatusPill :status="ticket.status" />
                    <select v-if="isAgent"
                        :value="ticket.status"
                        @change="changeStatus"
                        class="w-full mt-2 px-3 py-2 border border-border-subtle dark:border-gray-600 rounded-lg text-label-sm bg-white dark:bg-gray-700 text-on-surface dark:text-gray-200 focus:ring-primary outline-none cursor-pointer"
                    >
                        <option value="abierto">Abierto</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="en_espera">En Espera</option>
                        <option value="resuelto">Resuelto</option>
                        <option value="cerrado">Cerrado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <p class="text-[10px] uppercase font-bold text-outline dark:text-gray-400 tracking-widest">Priority</p>
                    <PriorityBadge :priority="ticket.priority" />
                </div>
            </div>
            <div class="h-px bg-border-subtle dark:bg-gray-700" />

            <div class="space-y-3">
                <p class="text-[10px] uppercase font-bold text-outline dark:text-gray-400 tracking-widest">Assigned to</p>
                <div v-if="ticket.assigned_agent" class="flex items-center gap-3 p-2 rounded-lg bg-surface-container-low dark:bg-gray-700 border border-border-subtle dark:border-gray-600">
                    <div class="w-9 h-9 rounded-full bg-surface-container dark:bg-gray-600 flex items-center justify-center text-outline dark:text-gray-300 shrink-0 border border-border-subtle dark:border-gray-500">
                        <span class="material-symbols-outlined text-[18px]">support_agent</span>
                    </div>
                    <div>
                        <p class="text-label-md text-deep-navy dark:text-blue-300">{{ ticket.assigned_agent.name }}</p>
                        <p class="text-label-sm text-outline dark:text-gray-400">IT Support</p>
                    </div>
                </div>
                <div v-else class="text-label-sm text-outline dark:text-gray-400 italic p-2">Unassigned</div>
                <div v-if="!showAssign">
                    <button @click="showAssign = true" class="w-full py-2 border border-dashed border-primary/30 rounded-lg text-label-sm text-primary hover:bg-primary-fixed/30 dark:hover:bg-blue-900/40 transition-colors">{{ ticket.assigned_agent ? 'Reassign' : 'Assign' }} Ticket</button>
                </div>
                <div v-else class="space-y-2">
                    <select v-model="selectedAgent" class="w-full px-3 py-2 border border-border-subtle dark:border-gray-600 rounded-lg text-label-sm bg-white dark:bg-gray-700 text-on-surface dark:text-gray-200">
                        <option :value="null" disabled>Select agent...</option>
                        <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                    </select>
                    <div class="flex gap-2">
                        <button @click="assign" :disabled="!selectedAgent" class="flex-1 py-1.5 bg-deep-navy text-white rounded-lg text-label-sm disabled:opacity-50">Assign</button>
                        <button @click="showAssign = false; selectedAgent = null" class="py-1.5 px-3 border border-border-subtle rounded-lg text-label-sm text-outline">Cancel</button>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <p class="text-[10px] uppercase font-bold text-outline dark:text-gray-400 tracking-widest">Requester</p>
                <div class="flex items-center gap-3 p-2 rounded-lg bg-surface-container-low dark:bg-gray-700 border border-border-subtle dark:border-gray-600">
                    <div class="w-9 h-9 rounded-full bg-surface-container dark:bg-gray-600 flex items-center justify-center text-outline dark:text-gray-300 shrink-0 border border-border-subtle dark:border-gray-500">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                    </div>
                    <div>
                        <p class="text-label-md text-deep-navy dark:text-blue-300">{{ ticket.user?.name }}</p>
                        <p class="text-label-sm text-outline dark:text-gray-400">{{ ticket.department?.name ?? '' }}</p>
                    </div>
                </div>
            </div>

            <div v-if="ticket.category" class="space-y-2">
                <p class="text-[10px] uppercase font-bold text-outline dark:text-gray-400 tracking-widest">Category</p>
                <p class="text-label-md text-on-surface dark:text-gray-200">{{ ticket.category.name }} / {{ ticket.subcategory?.name }}</p>
            </div>
            <div class="h-px bg-border-subtle dark:bg-gray-700" />

            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-label-sm text-outline dark:text-gray-400">Created</span>
                    <span class="text-label-sm text-on-surface dark:text-gray-200">{{ formatDate(ticket.created_at) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-label-sm text-outline dark:text-gray-400">Last Activity</span>
                    <span class="text-label-sm text-on-surface dark:text-gray-200">{{ timeAgo(ticket.created_at) }}</span>
                </div>
                <div v-if="ticket.resolved_at" class="flex justify-between items-center">
                    <span class="text-label-sm text-outline dark:text-gray-400">Resolved</span>
                    <span class="text-label-sm text-on-surface dark:text-gray-200">{{ formatDate(ticket.resolved_at) }}</span>
                </div>
                <div v-if="ticket.closed_at" class="flex justify-between items-center">
                    <span class="text-label-sm text-outline dark:text-gray-400">Closed</span>
                    <span class="text-label-sm text-on-surface dark:text-gray-200">{{ formatDate(ticket.closed_at) }}</span>
                </div>
            </div>

            <div class="pt-2">
                <Link :href="route('admin.tickets.index')" class="flex items-center gap-2 text-label-sm text-primary dark:text-blue-300 hover:underline">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    Back to Tickets
                </Link>
            </div>
        </div>
    </aside>
</template>
