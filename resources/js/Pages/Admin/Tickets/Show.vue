<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import TicketMetaSidebar from '@/Components/TicketMetaSidebar.vue';
import { Head, Link } from '@inertiajs/vue3';

interface TicketUser {
    id: number;
    name: string;
    email: string;
}

interface Comment {
    id: number;
    comment: string;
    is_internal: boolean;
    created_at: string;
    author: TicketUser;
}

interface Ticket {
    id: number;
    ticket_number: string;
    title: string;
    description: string;
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
    comments: Comment[];
    attachments: { id: number; file_name: string; file_path: string; uploaded_by: number }[];
}

defineProps<{
    ticket: Ticket;
}>();

const formatDate = (d: string): string => {
    const dt = new Date(d);
    return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AppLayout active-nav="tickets">
        <Head :title="'#' + ticket.ticket_number + ' - ' + ticket.title" />

        <main class="flex-1 flex overflow-hidden" style="height: calc(100vh - 4rem);">
            <!-- Conversation -->
            <section class="flex-1 flex flex-col bg-surface-gray dark:bg-gray-800/50 overflow-hidden">
                <!-- Header -->
                <div class="px-8 py-6 bg-white dark:bg-gray-800 border-b border-border-subtle dark:border-gray-700 flex justify-between items-center shrink-0">
                    <div>
                        <nav class="flex items-center gap-2 text-outline dark:text-gray-400 mb-2">
                            <Link :href="route('admin.tickets.index')" class="text-label-sm hover:text-primary dark:hover:text-blue-300">Tickets</Link>
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                            <span class="text-label-sm text-primary">#{{ ticket.ticket_number }}</span>
                        </nav>
                        <h2 class="text-headline-md text-deep-navy dark:text-blue-300">{{ ticket.title }}</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <button class="px-4 py-2 border border-border-subtle dark:border-gray-600 rounded-lg text-label-md text-on-surface dark:text-gray-200 hover:bg-surface-container-low dark:hover:bg-gray-700 transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">print</span> Print
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto px-8 py-8 space-y-8">
                    <!-- System: created -->
                    <div class="flex justify-center">
                        <span class="px-4 py-1 bg-surface-container-high dark:bg-gray-700 rounded-full text-outline dark:text-gray-400 text-[11px] font-medium">
                            Ticket created on {{ formatDate(ticket.created_at) }}
                        </span>
                    </div>

                    <!-- First message: description from user -->
                    <div v-if="ticket.description" class="flex gap-4 max-w-3xl ml-auto flex-row-reverse">
                        <div class="w-10 h-10 rounded-full bg-surface-container dark:bg-gray-700 flex items-center justify-center text-outline dark:text-gray-300 shrink-0 border border-border-subtle dark:border-gray-600">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                        </div>
                        <div class="space-y-1 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <span class="text-label-sm text-outline dark:text-gray-400">{{ formatDate(ticket.created_at) }}</span>
                                <span class="text-label-md text-deep-navy dark:text-blue-300">{{ ticket.user?.name }}</span>
                            </div>
                            <div class="bg-primary-fixed dark:bg-blue-900 p-4 rounded-xl rounded-tr-none text-on-primary-fixed-variant dark:text-blue-200 shadow-sm text-body-md text-left">
                                {{ ticket.description }}
                            </div>
                        </div>
                    </div>

                    <!-- Comments -->
                    <div
                        v-for="c in ticket.comments"
                        :key="c.id"
                        :class="[
                            'flex gap-4 max-w-3xl',
                            c.is_internal ? 'justify-center' : c.author?.id === ticket.user?.id ? 'ml-auto flex-row-reverse' : 'mr-auto'
                        ]"
                    >
                        <!-- Internal note -->
                        <div v-if="c.is_internal" class="bg-tertiary-fixed dark:bg-amber-900 text-on-tertiary-fixed dark:text-amber-200 px-6 py-3 rounded-lg border border-tertiary-fixed-dim/30 dark:border-amber-700/50 flex items-start gap-3 max-w-2xl">
                            <span class="material-symbols-outlined text-tertiary dark:text-amber-300 shrink-0">lock</span>
                            <div>
                                <p class="text-label-md">Internal Note by {{ c.author?.name }}</p>
                                <p class="text-body-sm mt-1 opacity-90">{{ c.comment }}</p>
                            </div>
                        </div>

                        <!-- Regular comment from agent (left) -->
                        <template v-else-if="c.author?.id !== ticket.user?.id">
                            <div class="w-10 h-10 rounded-full bg-surface-container dark:bg-gray-700 flex items-center justify-center text-outline dark:text-gray-300 shrink-0 border border-border-subtle dark:border-gray-600">
                                <span class="material-symbols-outlined text-[20px]">support_agent</span>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-label-md text-deep-navy dark:text-blue-300">{{ c.author?.name }}</span>
                                    <span class="bg-primary-container text-on-primary-container dark:text-blue-200 px-2 py-0.5 rounded text-[10px] font-bold">AGENT</span>
                                    <span class="text-label-sm text-outline dark:text-gray-400">{{ formatDate(c.created_at) }}</span>
                                </div>
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl rounded-tl-none border border-border-subtle dark:border-gray-600 shadow-sm text-on-surface dark:text-gray-200 text-body-md">
                                    {{ c.comment }}
                                </div>
                            </div>
                        </template>

                        <!-- Regular comment from user (right) -->
                        <template v-else>
                            <div class="w-10 h-10 rounded-full bg-surface-container dark:bg-gray-700 flex items-center justify-center text-outline dark:text-gray-300 shrink-0 border border-border-subtle dark:border-gray-600">
                                <span class="material-symbols-outlined text-[20px]">person</span>
                            </div>
                            <div class="space-y-1 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-label-sm text-outline dark:text-gray-400">{{ formatDate(c.created_at) }}</span>
                                    <span class="text-label-md text-deep-navy dark:text-blue-300">{{ c.author?.name }}</span>
                                </div>
                                <div class="bg-primary-fixed dark:bg-blue-900 p-4 rounded-xl rounded-tr-none text-on-primary-fixed-variant dark:text-blue-200 shadow-sm text-body-md text-left">
                                    {{ c.comment }}
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Reply Editor -->
                <div class="px-8 py-6 bg-white dark:bg-gray-800 border-t border-border-subtle dark:border-gray-700 shrink-0">
                    <div class="bg-surface-container-low dark:bg-gray-700 border border-border-subtle dark:border-gray-600 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-primary-container dark:focus-within:ring-blue-500 transition-all">
                        <div class="flex items-center justify-between px-4 py-2 border-b border-border-subtle dark:border-gray-600">
                            <div class="flex items-center gap-1">
                                <button class="p-1.5 hover:bg-surface-container-high dark:hover:bg-gray-600 rounded text-on-surface-variant dark:text-gray-300"><span class="material-symbols-outlined text-[18px]">format_bold</span></button>
                                <button class="p-1.5 hover:bg-surface-container-high dark:hover:bg-gray-600 rounded text-on-surface-variant dark:text-gray-300"><span class="material-symbols-outlined text-[18px]">format_italic</span></button>
                                <button class="p-1.5 hover:bg-surface-container-high dark:hover:bg-gray-600 rounded text-on-surface-variant dark:text-gray-300"><span class="material-symbols-outlined text-[18px]">attachment</span></button>
                            </div>
                        </div>
                        <textarea
                            class="w-full h-28 px-4 py-4 bg-transparent border-none focus:ring-0 text-body-md resize-none dark:text-gray-200 dark:placeholder-gray-400"
                            placeholder="Type your reply here..."
                        />
                        <div class="px-4 py-3 bg-white/50 dark:bg-gray-800/50 border-t border-border-subtle dark:border-gray-600 flex justify-between items-center">
                            <div class="flex items-center gap-2 text-outline dark:text-gray-400">
                                <span class="material-symbols-outlined text-sm">info</span>
                                <span class="text-label-sm">Replying as Support</span>
                            </div>
                            <button class="px-5 py-2.5 bg-deep-navy dark:bg-blue-700 text-white rounded-lg text-label-md hover:shadow-lg transition-all flex items-center gap-2">
                                Send Reply <span class="material-symbols-outlined text-sm">send</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Metadata Sidebar -->
            <TicketMetaSidebar :ticket="ticket" />
        </main>
    </AppLayout>
</template>
