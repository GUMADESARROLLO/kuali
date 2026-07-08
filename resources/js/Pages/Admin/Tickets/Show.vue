<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TicketMetaSidebar from '@/Components/TicketMetaSidebar.vue';
import FileUploader from '@/Components/FileUploader.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

interface TicketUser { id: number; name: string; email: string }
interface Attachment { id: number; file_name: string; file_path: string; url: string; icon: string; comment_id: number | null }
interface Comment { id: number; comment: string; is_internal: boolean; created_at: string; author: TicketUser; attachments: Attachment[] }
interface Agent { id: number; name: string }
interface Ticket {
    id: number; ticket_number: string; title: string; description: string;
    status: string; priority: string; created_at: string;
    resolved_at: string | null; closed_at: string | null;
    user: TicketUser; department: { name: string } | null;
    category: { name: string } | null; subcategory: { name: string } | null;
    assigned_agent: TicketUser | null;
    comments: Comment[];
    attachments: Attachment[];
}

const props = defineProps<{ ticket: Ticket; agents: Agent[] }>();

const commentForm = useForm({ comment: '', attachments: [] as File[] });
const uploaderRef = ref<InstanceType<typeof FileUploader> | null>(null);
const dragOver = ref(false);
let dragCounter = 0;

onMounted(() => {
    window.addEventListener('dragenter', onGlobalDragEnter);
    window.addEventListener('dragleave', onGlobalDragLeave);
    window.addEventListener('dragover', (e) => e.preventDefault());
    window.addEventListener('drop', onGlobalDrop);
});
onUnmounted(() => {
    window.removeEventListener('dragenter', onGlobalDragEnter);
    window.removeEventListener('dragleave', onGlobalDragLeave);
    window.removeEventListener('dragover', (e) => e.preventDefault());
    window.removeEventListener('drop', onGlobalDrop);
});

const onGlobalDragEnter = (e: DragEvent) => {
    e.preventDefault();
    dragCounter++;
    dragOver.value = true;
};
const onGlobalDragLeave = (e: DragEvent) => {
    e.preventDefault();
    dragCounter--;
    if (dragCounter <= 0) dragOver.value = false;
};
const onGlobalDrop = (e: DragEvent) => {
    e.preventDefault();
    dragCounter = 0;
    dragOver.value = false;
    uploaderRef.value?.addFiles(e.dataTransfer?.files ?? null);
};

const autoGrow = (e: Event) => {
    const el = e.target as HTMLTextAreaElement;
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 160) + 'px';
};

const submitComment = () => {
    commentForm.post(route('admin.tickets.comment', { ticket: props.ticket.id }), {
        forceFormData: true, preserveScroll: true, preserveState: false,
        onSuccess: () => commentForm.reset('comment', 'attachments'),
    });
};

const formatDate = (d: string): string => {
    const dt = new Date(d);
    return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AppLayout active-nav="tickets">
        <Head :title="'#' + ticket.ticket_number + ' - ' + ticket.title" />
        <main class="flex-1 flex overflow-hidden" style="height: calc(100vh - 4rem);">
            <section class="flex-1 flex flex-col bg-surface-gray dark:bg-gray-800/50 overflow-hidden">
                <div class="px-8 py-6 bg-white dark:bg-gray-800 border-b border-border-subtle dark:border-gray-700 flex justify-between items-center shrink-0">
                    <div>
                        <nav class="flex items-center gap-2 text-outline dark:text-gray-400 mb-2">
                            <Link :href="route('admin.tickets.index')" class="text-label-sm hover:text-primary dark:hover:text-blue-300">Tickets</Link>
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                            <span class="text-label-sm text-primary">#{{ ticket.ticket_number }}</span>
                        </nav>
                        <h2 class="text-headline-md text-deep-navy dark:text-blue-300">{{ ticket.title }}</h2>
                    </div>
                    <button class="px-4 py-2 border border-border-subtle dark:border-gray-600 rounded-lg text-label-md text-on-surface dark:text-gray-200 hover:bg-surface-container-low dark:hover:bg-gray-700 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">print</span> Print
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-8 py-8 space-y-8">
                    <div class="flex justify-center">
                        <span class="px-4 py-1 bg-surface-container-high dark:bg-gray-700 rounded-full text-outline dark:text-gray-400 text-[11px] font-medium">Ticket created on {{ formatDate(ticket.created_at) }}</span>
                    </div>

                    <div v-if="ticket.description" class="flex gap-4 max-w-3xl ml-auto flex-row-reverse">
                        <div class="w-10 h-10 rounded-full bg-surface-container dark:bg-gray-700 flex items-center justify-center text-outline dark:text-gray-300 shrink-0 border border-border-subtle dark:border-gray-600">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                        </div>
                        <div class="space-y-1 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <span class="text-label-sm text-outline dark:text-gray-400">{{ formatDate(ticket.created_at) }}</span>
                                <span class="text-label-md text-deep-navy dark:text-blue-300">{{ ticket.user?.name }}</span>
                            </div>
                            <div class="bg-primary-fixed dark:bg-blue-900 p-4 rounded-xl rounded-tr-none text-on-primary-fixed-variant dark:text-blue-200 shadow-sm text-body-md text-left">{{ ticket.description }}</div>
                            <div v-if="ticket.attachments.filter(a => !a.comment_id).length" class="flex flex-wrap gap-2 mt-3 justify-end">
                                <a v-for="att in ticket.attachments.filter(a => !a.comment_id)" :key="att.id" :href="att.url || undefined" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/50 dark:bg-gray-700/50 rounded-lg text-label-sm text-on-surface dark:text-gray-200 hover:bg-white dark:hover:bg-gray-700 transition-colors border border-border-subtle dark:border-gray-600" :class="{ 'opacity-50 cursor-not-allowed': !att.url }">
                                    <span>{{ att.icon }}</span>
                                    <span class="truncate max-w-[120px]">{{ att.file_name }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-for="c in ticket.comments" :key="c.id" :class="['flex gap-4 max-w-3xl', c.is_internal ? 'justify-center' : c.author?.id === ticket.user?.id ? 'ml-auto flex-row-reverse' : 'mr-auto']">
                        <div v-if="c.is_internal" class="bg-tertiary-fixed dark:bg-amber-900 text-on-tertiary-fixed dark:text-amber-200 px-6 py-3 rounded-lg border border-tertiary-fixed-dim/30 dark:border-amber-700/50 flex items-start gap-3 max-w-2xl">
                            <span class="material-symbols-outlined text-tertiary dark:text-amber-300 shrink-0">lock</span>
                            <div><p class="text-label-md">Internal Note by {{ c.author?.name }}</p><p class="text-body-sm mt-1 opacity-90">{{ c.comment }}</p></div>
                        </div>
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
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl rounded-tl-none border border-border-subtle dark:border-gray-600 shadow-sm text-on-surface dark:text-gray-200 text-body-md">{{ c.comment }}</div>
                                <div v-if="c.attachments?.length" class="flex flex-wrap gap-2 mt-2">
                                    <a v-for="att in c.attachments" :key="att.id" :href="att.url || undefined" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white dark:bg-gray-700 rounded-lg text-label-sm text-on-surface dark:text-gray-200 hover:bg-surface-container-low dark:hover:bg-gray-600 transition-colors border border-border-subtle dark:border-gray-600" :class="{ 'opacity-50 cursor-not-allowed': !att.url }">
                                        <span>{{ att.icon }}</span>
                                        <span class="truncate max-w-[120px]">{{ att.file_name }}</span>
                                    </a>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="w-10 h-10 rounded-full bg-surface-container dark:bg-gray-700 flex items-center justify-center text-outline dark:text-gray-300 shrink-0 border border-border-subtle dark:border-gray-600">
                                <span class="material-symbols-outlined text-[20px]">person</span>
                            </div>
                            <div class="space-y-1 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-label-sm text-outline dark:text-gray-400">{{ formatDate(c.created_at) }}</span>
                                    <span class="text-label-md text-deep-navy dark:text-blue-300">{{ c.author?.name }}</span>
                                </div>
                                <div class="bg-primary-fixed dark:bg-blue-900 p-4 rounded-xl rounded-tr-none text-on-primary-fixed-variant dark:text-blue-200 shadow-sm text-body-md text-left">{{ c.comment }}</div>
                                <div v-if="c.attachments?.length" class="flex flex-wrap gap-2 mt-2 justify-end">
                                    <a v-for="att in c.attachments" :key="att.id" :href="att.url || undefined" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/50 dark:bg-gray-700/50 rounded-lg text-label-sm text-on-surface dark:text-gray-200 hover:bg-white dark:hover:bg-gray-700 transition-colors border border-border-subtle dark:border-gray-600" :class="{ 'opacity-50 cursor-not-allowed': !att.url }">
                                        <span>{{ att.icon }}</span>
                                        <span class="truncate max-w-[120px]">{{ att.file_name }}</span>
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Composer minimalista -->
                <form @submit.prevent="submitComment" class="px-8 py-5 bg-white dark:bg-gray-800 border-t border-border-subtle dark:border-gray-700 shrink-0">
                    <div class="bg-surface-container-low dark:bg-gray-700 border border-border-subtle dark:border-gray-600 rounded-xl">
                        <textarea
                            v-model="commentForm.comment"
                            @input="autoGrow"
                            class="w-full bg-transparent border-none resize-none text-body-md dark:text-gray-200 dark:placeholder-gray-400 px-4 pt-3 pb-1 outline-none"
                            style="min-height:90px;max-height:160px"
                            placeholder="Escribe tu respuesta aquí..."
                            rows="1"
                        />
                        <div class="px-4">
                            <FileUploader ref="uploaderRef" v-model="commentForm.attachments" />
                        </div>
                        <p v-if="commentForm.errors.comment" class="px-4 text-error text-label-sm pb-1">{{ commentForm.errors.comment }}</p>
                        <div class="flex items-center justify-between px-3 py-2.5 border-t border-border-subtle dark:border-gray-600">
                            <button
                                type="button"
                                @click="uploaderRef?.openFilePicker()"
                                class="inline-flex items-center gap-2 px-3 py-1.5 border border-border-subtle dark:border-gray-600 rounded-lg text-label-sm text-on-surface-variant dark:text-gray-300 hover:border-outline hover:text-on-surface dark:hover:text-gray-100 transition-colors bg-transparent"
                            >
                                📎 Adjuntar
                            </button>
                            <div class="flex items-center gap-4">
                                <span class="text-label-sm text-outline dark:text-gray-500 hidden sm:inline">PDF, PNG, JPG, DOCX, XLSX, ZIP · máx 10MB</span>
                                <button
                                    type="submit"
                                    :disabled="commentForm.processing || !commentForm.comment.trim()"
                                    class="inline-flex items-center gap-2 bg-deep-navy dark:bg-blue-700 text-white rounded-lg px-4 py-2 text-label-sm font-medium hover:shadow-lg transition-all disabled:opacity-50"
                                >
                                    <span v-if="commentForm.processing" class="animate-spin">⟳</span>
                                    Enviar ➤
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Drag & drop overlay -->
                <div
                    v-if="dragOver"
                    class="fixed inset-0 z-50 flex items-center justify-center"
                    style="background:rgba(59,91,219,0.08)"
                >
                    <div class="border-2 border-dashed border-primary rounded-xl p-10 bg-white/80 dark:bg-gray-800/80 text-center">
                        <div class="text-3xl mb-2">⬆</div>
                        <p class="text-body-md text-on-surface dark:text-gray-200">Suelta el archivo aquí para adjuntarlo</p>
                    </div>
                </div>
            </section>

            <TicketMetaSidebar :ticket="ticket" :agents="agents" />
        </main>
    </AppLayout>
</template>
