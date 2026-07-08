<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TicketMetaSidebar from '@/Components/TicketMetaSidebar.vue';
import FileUploader from '@/Components/FileUploader.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';

interface TicketUser { id: number; name: string; email: string }
interface Attachment { id: number; file_name: string; file_path: string; url: string; icon: string; comment_id: number | null }
interface Comment { id: number; comment: string; is_internal: boolean; created_at: string; author: TicketUser; attachments: Attachment[] }
interface Ticket {
    id: number; ticket_number: string; title: string; description: string;
    status: string; priority: string; created_at: string;
    resolved_at: string | null; closed_at: string | null;
    user: TicketUser; department: { name: string } | null;
    category: { id: number; name: string } | null; subcategory: { id: number; name: string } | null;
    assigned_agent: TicketUser | null;
    comments: Comment[]; attachments: Attachment[];
}

const props = defineProps<{ ticket: Ticket; agents: { id: number; name: string }[]; categories?: { id: number; name: string }[] }>();

const auth = usePage().props.auth as any;
const userRoles = (auth?.roles ?? []) as string[];
const isAgent = computed(() => userRoles.includes('agente_it') || userRoles.includes('admin_it'));

const commentForm = useForm({ comment: '', attachments: [] as File[] });
const uploaderRef = ref<InstanceType<typeof FileUploader> | null>(null);
const dragOver = ref(false);
const showResolveModal = ref(false);
const resolveNote = ref('');
const resolveAttachments = ref<File[]>([]);
const resolving = ref(false);
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

const onGlobalDragEnter = (e: DragEvent) => { e.preventDefault(); dragCounter++; dragOver.value = true; };
const onGlobalDragLeave = (e: DragEvent) => { e.preventDefault(); dragCounter--; if (dragCounter <= 0) dragOver.value = false; };
const onGlobalDrop = (e: DragEvent) => {
    e.preventDefault(); dragCounter = 0; dragOver.value = false;
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

const resolveTicket = () => {
    resolving.value = true;
    router.post(route('admin.tickets.resolve', { ticket: props.ticket.id }), {
        comment: resolveNote.value || 'Ticket resuelto.',
        attachments: resolveAttachments.value,
    }, {
        forceFormData: true, preserveScroll: true, preserveState: false,
        onSuccess: () => {
            showResolveModal.value = false;
            resolveNote.value = '';
            resolveAttachments.value = [];
        },
        onFinish: () => { resolving.value = false; },
    });
};

const formatDate = (d: string): string => {
    const dt = new Date(d);
    return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const initAvatar = (name: string): string => {
    return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
};
</script>

<template>
    <AppLayout active-nav="tickets">
        <Head :title="'#' + ticket.ticket_number + ' - ' + ticket.title" />
        <main class="flex-1 flex overflow-hidden -mx-4 md:-mx-8 -mt-4 md:-mt-8" style="height: calc(100vh - 4rem);">
            <!-- Conversation -->
            <section class="flex-1 flex flex-col min-w-0 bg-white dark:bg-gray-900">
                <!-- Header -->
                <header class="flex items-start gap-3 px-6 py-4 border-b border-border-subtle dark:border-gray-700 bg-white dark:bg-gray-800">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3">
                            <Link :href="route('admin.tickets.index')" class="text-outline dark:text-gray-400 hover:text-on-surface dark:hover:text-gray-100 transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </Link>
                            <h2 class="text-base font-semibold text-on-surface dark:text-gray-100 shrink-0">#{{ ticket.ticket_number }}</h2>
                            <span class="text-outline dark:text-gray-500">&middot;</span>
                            <span class="text-sm text-outline dark:text-gray-400 truncate">{{ ticket.title }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-1.5 ml-8">
                            <div class="w-5 h-5 rounded-full bg-emerald-500 text-white text-[9px] flex items-center justify-center shrink-0">{{ initAvatar(ticket.user?.name) }}</div>
                            <span class="text-xs text-outline dark:text-gray-400">{{ ticket.user?.name }} <span class="text-outline/60">— {{ ticket.department?.name ?? '' }}</span></span>
                        </div>
                    </div>

                    <!-- Agente/Admin: boton resolver si no esta resuelto -->
                    <div v-if="isAgent && ticket.status !== 'resuelto' && ticket.status !== 'cerrado' && ticket.status !== 'cancelado'" class="flex items-center gap-2 shrink-0">
                        <button @click="showResolveModal = true"
                            class="bg-success-green text-white text-sm font-medium rounded-lg px-4 py-1.5 hover:opacity-90 transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Resolver
                        </button>
                    </div>
                    <!-- Ticket resuelto: badge para todos -->
                    <div v-if="ticket.status === 'resuelto' || ticket.status === 'cerrado'" class="flex items-center gap-2 shrink-0">
                        <span class="px-3 py-1.5 bg-success-green/10 text-success-green rounded-lg text-label-sm font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Resuelto
                        </span>
                    </div>
                </header>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-5 bg-surface-gray/30 dark:bg-gray-800">
                    <!-- System: created -->
                    <div class="flex items-center gap-2 text-xs text-outline dark:text-gray-400 pl-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Ticket created on {{ formatDate(ticket.created_at) }}</span>
                    </div>

                    <!-- First message: description from user -->
                    <div v-if="ticket.description" class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-500 text-white text-xs flex items-center justify-center shrink-0">{{ initAvatar(ticket.user?.name) }}</div>
                        <div>
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-2xl rounded-tl-sm px-4 py-3 max-w-md text-sm text-on-surface dark:text-gray-200">
                                {{ ticket.description }}
                            </div>
                            <div v-if="ticket.attachments.filter(a => !a.comment_id).length" class="flex flex-wrap gap-2 mt-2">
                                <a v-for="att in ticket.attachments.filter(a => !a.comment_id)" :key="att.id" :href="att.url || undefined" target="_blank"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-white dark:bg-gray-800 rounded-lg text-label-sm text-on-surface dark:text-gray-200 hover:bg-surface-container-low dark:hover:bg-gray-700 transition-colors border border-border-subtle dark:border-gray-600"
                                    :class="{ 'opacity-50': !att.url }">
                                    <span>{{ att.icon }}</span>
                                    <span class="truncate max-w-[100px]">{{ att.file_name }}</span>
                                </a>
                            </div>
                            <p class="text-xs text-outline dark:text-gray-400 mt-1">{{ formatDate(ticket.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Comments -->
                    <div v-for="c in ticket.comments" :key="c.id">
                        <!-- Internal note -->
                        <div v-if="c.is_internal" class="flex items-center gap-2 text-xs text-outline dark:text-gray-400 pl-2 my-4">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span class="font-semibold text-on-surface dark:text-gray-100">{{ c.author?.name }}</span>
                            <span>{{ formatDate(c.created_at) }}</span>
                            <span class="text-on-surface/50 dark:text-gray-400/70">added a private note</span>
                        </div>

                        <!-- Agent comment (right-aligned like design) -->
                        <div v-else-if="c.author?.id !== ticket.user?.id" class="flex justify-end">
                            <div class="max-w-lg">
                                <p class="text-sm font-semibold text-on-surface dark:text-gray-100 mb-1 text-right">{{ c.author?.name }}</p>
                                <div class="bg-blue-50 dark:bg-blue-900/40 rounded-2xl rounded-tr-sm px-4 py-3">
                                    <p class="text-sm text-on-surface dark:text-gray-200">{{ c.comment }}</p>
                                </div>
                                <div v-if="c.attachments?.length" class="flex flex-wrap gap-2 mt-2 justify-end">
                                    <a v-for="att in c.attachments" :key="att.id" :href="att.url || undefined" target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-white dark:bg-gray-800 rounded-lg text-label-sm text-on-surface dark:text-gray-200 hover:bg-surface-container-low dark:hover:bg-gray-700 transition-colors border border-border-subtle dark:border-gray-600"
                                        :class="{ 'opacity-50': !att.url }">
                                        <span>{{ att.icon }}</span>
                                        <span class="truncate max-w-[100px]">{{ att.file_name }}</span>
                                    </a>
                                </div>
                                <p class="text-xs text-outline dark:text-gray-400 mt-1 text-right">{{ formatDate(c.created_at) }}</p>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-deep-navy dark:bg-blue-700 text-white text-[10px] font-semibold flex items-center justify-center ml-2 shrink-0">{{ initAvatar(c.author?.name) }}</div>
                        </div>

                        <!-- User comment (left-aligned) -->
                        <div v-else class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500 text-white text-xs flex items-center justify-center shrink-0">{{ initAvatar(c.author?.name) }}</div>
                            <div>
                                <p class="text-sm font-semibold text-on-surface dark:text-gray-100 mb-1">{{ c.author?.name }}</p>
                                <div class="bg-gray-100 dark:bg-gray-700 rounded-2xl rounded-tl-sm px-4 py-3 max-w-md text-sm text-on-surface dark:text-gray-200">
                                    {{ c.comment }}
                                </div>
                                <div v-if="c.attachments?.length" class="flex flex-wrap gap-2 mt-2">
                                    <a v-for="att in c.attachments" :key="att.id" :href="att.url || undefined" target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-white dark:bg-gray-800 rounded-lg text-label-sm text-on-surface dark:text-gray-200 hover:bg-surface-container-low dark:hover:bg-gray-700 transition-colors border border-border-subtle dark:border-gray-600"
                                        :class="{ 'opacity-50': !att.url }">
                                        <span>{{ att.icon }}</span>
                                        <span class="truncate max-w-[100px]">{{ att.file_name }}</span>
                                    </a>
                                </div>
                                <p class="text-xs text-outline dark:text-gray-400 mt-1">{{ formatDate(c.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resolve Modal -->
                <Teleport to="body">
                    <div v-if="showResolveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showResolveModal = false">
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-lg mx-4">
                            <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-2">Resolver Ticket</h3>
                            <p class="text-body-sm text-outline dark:text-gray-400 mb-4">Describe la resoluci&oacute;n final del ticket.</p>
                            <textarea v-model="resolveNote" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none text-body-sm"
                                placeholder="Describe la soluci&oacute;n aplicada..."
                            />
                            <div class="mt-3">
                                <FileUploader v-model="resolveAttachments" />
                            </div>
                            <div class="flex justify-end gap-3 mt-4">
                                <button @click="showResolveModal = false; resolveNote = ''; resolveAttachments = []"
                                    class="px-4 py-2 border border-border-subtle rounded-lg text-label-sm text-outline hover:bg-surface-container-low transition-all">Cancelar</button>
                                <button @click="resolveTicket" :disabled="resolving"
                                    class="px-4 py-2 bg-success-green text-white rounded-lg text-label-sm hover:opacity-90 transition-all disabled:opacity-50">
                                    {{ resolving ? 'Resolviendo...' : 'Confirmar resoluci&oacute;n' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </Teleport>

                <!-- Composer -->
                <div v-if="ticket.status !== 'resuelto' && ticket.status !== 'cerrado' && ticket.status !== 'cancelado'" class="border-t border-border-subtle dark:border-gray-700 px-6 py-4 bg-white dark:bg-gray-800">
                    <form @submit.prevent="submitComment">
                        <div class="border border-border-subtle dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800">
                            <div class="px-4 pt-3">
                                <textarea
                                    v-model="commentForm.comment"
                                    @input="autoGrow"
                                    class="w-full text-sm placeholder-outline dark:placeholder-gray-400 focus:outline-none resize-none bg-transparent dark:text-gray-200"
                                    style="min-height:90px;max-height:160px"
                                    placeholder="Escribe tu respuesta aquí..."
                                    rows="1"
                                />
                            </div>
                            <div class="px-4">
                                <FileUploader ref="uploaderRef" v-model="commentForm.attachments" />
                            </div>
                            <p v-if="commentForm.errors.comment" class="px-4 text-error text-label-sm pb-1">{{ commentForm.errors.comment }}</p>
                            <div class="flex items-center justify-between px-4 pb-3 pt-2 border-t border-border-subtle dark:border-gray-600">
                                <div class="flex items-center gap-1 text-outline dark:text-gray-400">
                                    <button type="button" @click="uploaderRef?.openFilePicker()" class="flex items-center gap-1.5 px-3 py-1.5 border border-border-subtle dark:border-gray-500 rounded-lg text-label-sm text-outline dark:text-gray-300 hover:text-deep-navy dark:hover:text-white hover:border-deep-navy dark:hover:border-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-all" title="Adjuntar archivos">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7.586a2 2 0 00-2.828 0l-5.657 5.657a4 4 0 105.657 5.657l5.657-5.657A2 2 0 0015.172 7.586z"/></svg>
                                        Adjuntar
                                    </button>
                                </div>
                                <button type="submit" :disabled="commentForm.processing || !commentForm.comment.trim()"
                                    class="w-9 h-9 rounded-xl bg-deep-navy dark:bg-blue-700 text-white flex items-center justify-center hover:bg-primary dark:hover:bg-blue-600 transition-colors disabled:opacity-50">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M3 12l18-8-8 18-2-8-8-2z"/></svg>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Details Panel -->
            <TicketMetaSidebar :ticket="ticket" :agents="agents" :categories="categories ?? []" />

            <!-- Drag overlay -->
            <div v-if="dragOver" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(59,91,219,0.08)">
                <div class="border-2 border-dashed border-primary rounded-xl p-10 bg-white/80 dark:bg-gray-800/80 text-center">
                    <div class="text-3xl mb-2">⬆</div>
                    <p class="text-body-md">Suelta el archivo aquí para adjuntarlo</p>
                </div>
            </div>
        </main>
    </AppLayout>
</template>
