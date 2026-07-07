<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PriorityBadge from '@/Components/PriorityBadge.vue';
import StatusPill from '@/Components/StatusPill.vue';

interface TicketUser {
    id: number;
    name: string;
    email: string;
}

interface Ticket {
    id: number;
    ticket_number: string;
    title: string;
    status: string;
    priority: string;
    created_at: string;
    resolved_at: string | null;
    closed_at: string | null;
    user: TicketUser;
    department: { name: string } | null;
    category: { name: string } | null;
    subcategory: { name: string } | null;
    assigned_agent: TicketUser | null;
}

defineProps<{
    ticket: Ticket;
}>();

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
            <!-- Status & Priority -->
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <p class="text-[10px] uppercase font-bold text-outline dark:text-gray-400 tracking-widest">Status</p>
                    <StatusPill :status="ticket.status" />
                </div>
                <div class="space-y-2">
                    <p class="text-[10px] uppercase font-bold text-outline dark:text-gray-400 tracking-widest">Priority</p>
                    <PriorityBadge :priority="ticket.priority" />
                </div>
            </div>

            <div class="h-px bg-border-subtle dark:bg-gray-700" />

            <!-- Assignee -->
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
            </div>

            <!-- Requester -->
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

            <!-- Category -->
            <div v-if="ticket.category" class="space-y-2">
                <p class="text-[10px] uppercase font-bold text-outline dark:text-gray-400 tracking-widest">Category</p>
                <p class="text-label-md text-on-surface dark:text-gray-200">{{ ticket.category.name }} / {{ ticket.subcategory?.name }}</p>
            </div>

            <div class="h-px bg-border-subtle dark:bg-gray-700" />

            <!-- Timestamps -->
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

            <!-- Back link -->
            <div class="pt-4">
                <Link
                    :href="route('admin.tickets.index')"
                    class="flex items-center gap-2 text-label-sm text-primary dark:text-blue-300 hover:underline"
                >
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    Back to Tickets
                </Link>
            </div>
        </div>
    </aside>
</template>
