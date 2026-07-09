<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import FormSelect from '@/Components/FormSelect.vue';
import axios from 'axios';

interface CatItem { id: number; name: string }
interface TicketUser { id: number; name: string; email: string }
interface Agent { id: number; name: string }
interface SlaRuleRef { id: number; name: string }

interface Ticket {
    id: number; ticket_number: string; title: string; status: string; priority: string;
    created_at: string; resolved_at: string | null; closed_at: string | null;
    user: TicketUser; department: { name: string } | null;
    category: CatItem | null; subcategory: CatItem | null;
    assigned_agent: TicketUser | null;
    sla_rule: SlaRuleRef | null;
    first_response_due_at: string | null;
    update_due_at: string | null;
    solution_due_at: string | null;
    is_escalated: boolean;
}

const props = defineProps<{ ticket: Ticket; agents: Agent[]; categories?: CatItem[] }>();

const selCategory = ref<string | number>(props.ticket.category?.id ?? '');
const selSubcategory = ref<string | number>(props.ticket.subcategory?.id ?? '');
const subcatOptions = ref<CatItem[]>([]);
const loadingSub = ref(false);

if (props.ticket.category?.id) {
    axios.get(`/api/subcategorias/${props.ticket.category.id}`).then(({ data }) => {
        subcatOptions.value = data;
    }).catch(() => {});
}

watch(selCategory, async (catId) => {
    selSubcategory.value = '';
    subcatOptions.value = [];
    if (!catId) return;
    loadingSub.value = true;
    try {
        const { data } = await axios.get(`/api/subcategorias/${catId}`);
        subcatOptions.value = data;
    } catch { subcatOptions.value = []; }
    loadingSub.value = false;
});

const categoryOptions = computed(() => (props.categories ?? []).map(c => ({ value: c.id, label: c.name })));
const subcategoryOptions = computed(() => subcatOptions.value.map(s => ({ value: s.id, label: s.name })));

let reclassifyTimer: ReturnType<typeof setTimeout> | null = null;
watch([selCategory, selSubcategory], ([cat, sub]) => {
    if (!cat || !sub) return;
    if (cat == props.ticket.category?.id && sub == props.ticket.subcategory?.id) return;
    if (reclassifyTimer) clearTimeout(reclassifyTimer);
    reclassifyTimer = setTimeout(() => {
        router.post(route('admin.tickets.reclassify', { ticket: props.ticket.id }), {
            category_id: cat, subcategory_id: sub,
        }, { preserveScroll: true, preserveState: false });
    }, 600);
});

const auth = usePage().props.auth as any;
const userRoles = (auth?.roles ?? []) as string[];
const isAgent = userRoles.includes('agente_it') || userRoles.includes('admin_it');

const timeLeft = (dateStr: string): string => {
    const diff = new Date(dateStr).getTime() - Date.now();
    if (diff <= 0) return 'Vencido';
    const h = Math.floor(diff / (1000 * 60 * 60));
    const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    if (h >= 24) return `${Math.floor(h / 24)}d ${h % 24}h`;
    return `${h}h ${m}m`;
};
const isPast = (dateStr: string): boolean => new Date(dateStr).getTime() < Date.now();

const assignAgent = ref(props.ticket.assigned_agent?.id ?? '');

watch(assignAgent, (newVal) => {
    if (newVal && newVal !== props.ticket.assigned_agent?.id) {
        router.post(route('admin.tickets.assign', { ticket: props.ticket.id }), {
            assigned_to: newVal,
        }, { preserveScroll: true, preserveState: false });
    }
});

const agentOptions = computed(() => props.agents.map(a => ({
    value: a.id,
    label: a.name,
    prefix: a.name.charAt(0).toUpperCase(),
})));

const statusOptions = [
    { value: 'abierto', label: 'Abierto' },
    { value: 'en_proceso', label: 'En Proceso' },
    { value: 'en_espera', label: 'En Espera' },
    { value: 'resuelto', label: 'Resuelto' },
    { value: 'cerrado', label: 'Cerrado' },
    { value: 'cancelado', label: 'Cancelado' },
];

const changeStatus = (val: string | number) => {
    router.post(route('admin.tickets.status', { ticket: props.ticket.id }), {
        status: val,
    }, { preserveScroll: true, preserveState: false });
};

const changePriority = (p: string) => {
    router.post(route('admin.tickets.priority', { ticket: props.ticket.id }), {
        priority: p,
    }, { preserveScroll: true, preserveState: false });
};

const formatDate = (d: string): string => {
    const dt = new Date(d);
    return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const timeAgo = (d: string): string => {
    const diff = Date.now() - new Date(d).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 60) return mins + ' mins ago';
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return hrs + ' hours ago';
    return Math.floor(hrs / 24) + ' days ago';
};

const initAvatar = (name: string): string => {
    return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
};
</script>

<template>
    <aside class="w-[320px] h-full bg-white dark:bg-gray-800 border-l border-border-subtle dark:border-gray-700 overflow-y-auto hidden xl:block shrink-0">
        <div class="px-5 py-5">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-semibold text-on-surface dark:text-gray-100">Ticket details</h3>
            </div>

            <!-- Assign -->
            <div class="mb-5">
                <label class="text-xs font-medium text-outline dark:text-gray-400 mb-1.5 block">Assigne</label>
                <div v-if="isAgent">
                    <FormSelect v-model="assignAgent" :options="agentOptions" placeholder="Sin asignar">
                        <template #prefix>
                            <div v-if="ticket.assigned_agent" class="w-6 h-6 rounded-full bg-deep-navy text-white text-[9px] font-bold flex items-center justify-center shrink-0">
                                {{ initAvatar(ticket.assigned_agent.name) }}
                            </div>
                            <div v-else class="w-6 h-6 rounded-full bg-surface-container text-outline flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                        </template>
                    </FormSelect>
                </div>
                <div v-else class="flex items-center gap-2 border border-border-subtle dark:border-gray-600 rounded-xl px-3 py-2.5">
                    <div v-if="ticket.assigned_agent" class="w-6 h-6 rounded-full bg-deep-navy dark:bg-blue-700 text-white text-[9px] font-bold flex items-center justify-center shrink-0">
                        {{ initAvatar(ticket.assigned_agent.name) }}
                    </div>
                    <span class="text-sm text-on-surface dark:text-gray-100">{{ ticket.assigned_agent?.name ?? 'Sin asignar' }}</span>
                </div>
            </div>

            <!-- SLA Timer -->
            <div v-if="ticket.sla_rule" class="mb-5 p-3 rounded-xl border" :class="ticket.is_escalated ? 'border-error/40 bg-error/5' : 'border-border-subtle dark:border-gray-600 bg-surface-container-lowest dark:bg-gray-800/50'">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-[16px]" :class="ticket.is_escalated ? 'text-error' : 'text-outline'">timer</span>
                    <span class="text-xs font-semibold text-outline dark:text-gray-400 uppercase tracking-wider">SLA: {{ ticket.sla_rule.name }}</span>
                    <span v-if="ticket.is_escalated" class="ml-auto px-1.5 py-0.5 rounded text-[10px] font-bold bg-error text-white">VENCIDO</span>
                </div>
                <div class="space-y-1 text-[12px]">
                    <div v-if="ticket.first_response_due_at" class="flex justify-between">
                        <span class="text-outline dark:text-gray-400">1ra Respuesta</span>
                        <span :class="isPast(ticket.first_response_due_at) ? 'text-error font-bold' : 'text-on-surface dark:text-gray-200'">{{ timeLeft(ticket.first_response_due_at) }}</span>
                    </div>
                    <div v-if="ticket.update_due_at" class="flex justify-between">
                        <span class="text-outline dark:text-gray-400">Actualización</span>
                        <span :class="isPast(ticket.update_due_at) ? 'text-error font-bold' : 'text-on-surface dark:text-gray-200'">{{ timeLeft(ticket.update_due_at) }}</span>
                    </div>
                    <div v-if="ticket.solution_due_at" class="flex justify-between">
                        <span class="text-outline dark:text-gray-400">Solución</span>
                        <span :class="isPast(ticket.solution_due_at) ? 'text-error font-bold' : 'text-on-surface dark:text-gray-200'">{{ timeLeft(ticket.solution_due_at) }}</span>
                    </div>
                </div>
            </div>

            <!-- Team / Department -->
            <div class="mb-5">
                <label class="text-xs font-medium text-outline dark:text-gray-400 mb-1.5 block">Departamento</label>
                <div class="flex items-center border border-border-subtle dark:border-gray-600 rounded-xl px-3 py-2.5">
                    <span class="text-sm text-on-surface dark:text-gray-100 flex-1">{{ ticket.department?.name ?? '—' }}</span>
                </div>
            </div>

            <!-- Set status -->
            <div class="mb-5">
                <label class="text-xs font-medium text-outline dark:text-gray-400 mb-1.5 block">Set status</label>
                <div v-if="isAgent">
                    <FormSelect :model-value="ticket.status" :options="statusOptions" @update:model-value="changeStatus">
                        <template #prefix>
                            <svg class="w-4 h-4 text-primary shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3v18M5 4h11l-2 4 2 4H5"/></svg>
                        </template>
                    </FormSelect>
                </div>
                <div v-else class="flex items-center border border-border-subtle dark:border-gray-600 rounded-xl px-3 py-2.5">
                    <span class="text-sm text-on-surface dark:text-gray-100 flex-1">{{ ticket.status }}</span>
                </div>
            </div>

            <!-- Set priority -->
            <div class="mb-5">
                <label class="text-xs font-medium text-outline dark:text-gray-400 mb-1.5 block">Set priority</label>
                <div class="flex gap-2">
                    <button @click="changePriority('baja')"
                        class="flex-1 flex items-center justify-center gap-1.5 text-sm border rounded-xl py-2 transition-all"
                        :class="ticket.priority === 'baja' ? 'border-deep-navy dark:border-blue-500 bg-deep-navy/5 dark:bg-blue-500/10 font-medium text-on-surface dark:text-gray-100' : 'border-border-subtle dark:border-gray-600 text-outline dark:text-gray-300 hover:bg-surface-container-low dark:hover:bg-gray-700'">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>Low
                    </button>
                    <button @click="changePriority('media')"
                        class="flex-1 flex items-center justify-center gap-1.5 text-sm border rounded-xl py-2 transition-all"
                        :class="ticket.priority === 'media' ? 'border-deep-navy dark:border-blue-500 bg-deep-navy/5 dark:bg-blue-500/10 font-medium text-on-surface dark:text-gray-100' : 'border-border-subtle dark:border-gray-600 text-outline dark:text-gray-300 hover:bg-surface-container-low dark:hover:bg-gray-700'">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>Medium
                    </button>
                    <button @click="changePriority('alta')"
                        class="flex-1 flex items-center justify-center gap-1.5 text-sm border rounded-xl py-2 transition-all"
                        :class="ticket.priority === 'alta' || ticket.priority === 'urgente' ? 'border-deep-navy dark:border-blue-500 bg-deep-navy/5 dark:bg-blue-500/10 font-medium text-on-surface dark:text-gray-100' : 'border-border-subtle dark:border-gray-600 text-outline dark:text-gray-300 hover:bg-surface-container-low dark:hover:bg-gray-700'">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>High
                    </button>
                </div>
            </div>

            <!-- Attributes -->
            <div class="mb-5">
                <h4 class="text-sm font-semibold text-on-surface dark:text-gray-100 mb-3">Atributos</h4>
                <dl class="space-y-3">
                    <div v-if="isAgent" class="mb-4 space-y-3">
                        <div>
                            <label class="text-xs font-medium text-outline dark:text-gray-400 mb-1.5 block">Categor&iacute;a</label>
                            <FormSelect v-model="selCategory" :options="categoryOptions" placeholder="Seleccionar categor&iacute;a" />
                        </div>
                        <div>
                            <label class="text-xs font-medium text-outline dark:text-gray-400 mb-1.5 block">Subcategor&iacute;a</label>
                            <FormSelect v-model="selSubcategory" :options="subcategoryOptions" placeholder="Primero selecciona categor&iacute;a" :disabled="!selCategory || loadingSub" />
                        </div>
                    </div>
                    <template v-else>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-outline dark:text-gray-400">Categor&iacute;a</dt>
                            <dd class="text-sm font-medium text-on-surface dark:text-gray-100">{{ ticket.category?.name ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-outline dark:text-gray-400">Subcategor&iacute;a</dt>
                            <dd class="text-sm font-medium text-on-surface dark:text-gray-100">{{ ticket.subcategory?.name ?? '—' }}</dd>
                        </div>
                    </template>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-outline dark:text-gray-400">Creado</dt>
                        <dd class="text-sm font-medium text-on-surface dark:text-gray-100">{{ formatDate(ticket.created_at) }}</dd>
                    </div>
                    <div v-if="ticket.resolved_at" class="flex items-center justify-between">
                        <dt class="text-sm text-outline dark:text-gray-400">Resuelto</dt>
                        <dd class="text-sm font-medium text-on-surface dark:text-gray-100">{{ formatDate(ticket.resolved_at) }}</dd>
                    </div>
                    <div v-if="ticket.closed_at" class="flex items-center justify-between">
                        <dt class="text-sm text-outline dark:text-gray-400">Cerrado</dt>
                        <dd class="text-sm font-medium text-on-surface dark:text-gray-100">{{ formatDate(ticket.closed_at) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Back link -->
            <div class="pt-2">
                <Link :href="route('admin.tickets.index')" class="flex items-center gap-2 text-label-sm text-primary dark:text-blue-400 hover:underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Volver a Tickets
                </Link>
            </div>
        </div>
    </aside>
</template>
