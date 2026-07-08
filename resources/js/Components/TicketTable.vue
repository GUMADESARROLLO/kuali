<script setup lang="ts">
import StatusPill from '@/Components/StatusPill.vue';
import PriorityBadge from '@/Components/PriorityBadge.vue';
import { router } from '@inertiajs/vue3';

interface Ticket {
    id: number;
    ticket_number: string;
    title: string;
    priority: string;
    status: string;
    created_at: string;
    assigned_agent?: { name: string } | null;
}

defineProps<{
    tickets: Ticket[];
}>();

const viewTicket = (ticket: Ticket) => {
    router.get(route('admin.tickets.show', { ticket: ticket.id }));
};

const formatDate = (dateStr: string): string => {
    const d = new Date(dateStr);
    const now = new Date();
    const isToday = d.toDateString() === now.toDateString();
    if (isToday) {
        return `Today, ${d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}`;
    }
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>
    <div class="overflow-x-auto table-scrollbar">
        <table class="w-full text-left border-collapse min-w-[750px]">
            <thead>
                <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                    <th class="py-4 px-6 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider w-12">
                        <input class="rounded border-outline-variant dark:border-gray-500 text-primary focus:ring-primary" type="checkbox"/>
                    </th>
                    <th class="py-4 px-6 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">
                        <div class="flex items-center gap-1">Subject <span class="material-symbols-outlined text-[16px]">swap_vert</span></div>
                    </th>
                    <th class="py-4 px-6 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider w-24">
                        <div class="flex items-center gap-1">Priority <span class="material-symbols-outlined text-[16px]">swap_vert</span></div>
                    </th>
                    <th class="py-4 px-6 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider w-36">
                        <div class="flex items-center gap-1">Assignee <span class="material-symbols-outlined text-[16px]">swap_vert</span></div>
                    </th>
                    <th class="py-4 px-6 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider w-36">
                        <div class="flex items-center gap-1">Created Date <span class="material-symbols-outlined text-[16px]">swap_vert</span></div>
                    </th>
                    <th class="py-4 px-6 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider w-28">
                        <div class="flex items-center gap-1">Status <span class="material-symbols-outlined text-[16px]">swap_vert</span></div>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                <tr
                    v-for="t in tickets"
                    :key="t.id"
                    class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50 transition-colors group"
                >
                    <td class="py-4 px-6"><input class="rounded border-outline-variant dark:border-gray-500 text-primary focus:ring-primary" type="checkbox"/></td>
                    <td class="py-4 px-6 cursor-pointer" @click="viewTicket(t)">
                        <div class="text-body-md text-on-surface dark:text-gray-100 group-hover:text-primary transition-colors">{{ t.title }}</div>
                        <p class="text-[12px] text-outline dark:text-gray-400">Ticket ID: #{{ t.ticket_number }}</p>
                    </td>
                    <td class="py-4 px-6"><PriorityBadge :priority="t.priority" /></td>
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-2">
                            <div v-if="t.assigned_agent" class="w-7 h-7 rounded-full bg-surface-container dark:bg-gray-700 flex items-center justify-center text-outline dark:text-gray-300 text-xs border border-border-subtle dark:border-gray-600">
                                <span class="material-symbols-outlined text-[16px]">person</span>
                            </div>
                            <div v-else class="w-7 h-7 rounded-full bg-surface-container dark:bg-gray-700 flex items-center justify-center text-outline dark:text-gray-400 text-[14px]">-</div>
                            <span v-if="t.assigned_agent" class="text-body-sm text-on-surface dark:text-gray-100">{{ t.assigned_agent.name }}</span>
                            <span v-else class="text-body-sm text-outline dark:text-gray-400 italic">Unassigned</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-body-sm text-on-surface-variant dark:text-gray-400">{{ formatDate(t.created_at) }}</td>
                    <td class="py-4 px-6"><StatusPill :status="t.status" /></td>

                </tr>
                <tr v-if="tickets.length === 0">
                    <td colspan="6" class="py-12 text-center text-outline dark:text-gray-400 text-body-md">No tickets found.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
