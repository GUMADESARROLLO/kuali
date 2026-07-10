<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchSelect from '@/Components/SearchSelect.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface PersonOpt {
    id: number; first_name: string; last_name: string; email: string | null;
    company_name: string | null; department_name: string | null;
}
interface AssetItem {
    id: number; asset_tag: string; name: string; brand: string | null;
    model: string | null; serial_number: string | null; status: string;
    category_name: string | null; assigned_date: string | null;
}
interface PersonInfo {
    id: number; first_name: string; last_name: string; email: string | null;
    company_name: string | null; department_name: string | null;
}

const props = defineProps<{
    people: PersonOpt[];
    person: PersonInfo | null;
    assets: AssetItem[];
    stats: { total: number; active: number; maintenance: number } | null;
}>();

const personId = ref<number | null>(props.person?.id ?? null);

const personOptions = computed(() =>
    props.people.map(p => ({
        id: p.id,
        label: `${p.first_name} ${p.last_name}`,
        subtitle: [p.company_name, p.department_name].filter(Boolean).join(' · ') || undefined,
    }))
);

const selectPerson = () => {
    if (!personId.value) return;
    router.get(route('admin.reports.assets-by-person'), { person_id: personId.value }, { preserveState: true, replace: true });
};

const folio = computed(() => {
    const now = new Date();
    return `RA-${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}`;
});

const today = new Date().toLocaleDateString('es', { day: 'numeric', month: 'short', year: 'numeric' });

const initials = computed(() => {
    if (!props.person) return '??';
    return `${props.person.first_name.charAt(0)}${props.person.last_name.charAt(0)}`.toUpperCase();
});

const statusClass = (s: string) => {
    if (s === 'asignado') return 'text-green-700 dark:text-green-400';
    if (s === 'en_reparacion') return 'text-yellow-700 dark:text-yellow-400';
    if (s === 'dado_de_baja') return 'text-gray-500 dark:text-gray-400';
    return 'text-blue-700 dark:text-blue-400';
};
const statusBg = (s: string) => {
    if (s === 'asignado') return 'bg-green-100/70 dark:bg-green-900/20';
    if (s === 'en_reparacion') return 'bg-yellow-100/70 dark:bg-yellow-900/20';
    if (s === 'dado_de_baja') return 'bg-gray-100/70 dark:bg-gray-700/50';
    return 'bg-blue-100/70 dark:bg-blue-900/20';
};
const statusLabel: Record<string, string> = { asignado: 'Activo', disponible: 'Disponible', en_reparacion: 'Mantenimiento', dado_de_baja: 'De baja' };

const catIcon = (cat: string | null): string => {
    if (!cat) return 'devices';
    const c = cat.toLowerCase();
    if (c.includes('laptop') || c.includes('portatil')) return 'laptop';
    if (c.includes('monitor') || c.includes('pantalla')) return 'desktop_windows';
    if (c.includes('teclado')) return 'keyboard';
    if (c.includes('mouse')) return 'mouse';
    if (c.includes('telefono') || c.includes('celular') || c.includes('iphone')) return 'phone_iphone';
    if (c.includes('impresora')) return 'print';
    if (c.includes('tablet')) return 'tablet';
    if (c.includes('pc') || c.includes('desktop') || c.includes('escritorio')) return 'computer';
    return 'devices';
};
</script>

<template>
    <AppLayout active-nav="person-report">
        <Head title="Activos por Persona" />

        <div class="flex items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Activos por Persona</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Reporte individual de equipos asignados.</p>
            </div>
            <a v-if="person" :href="route('admin.reports.assets-by-person.pdf', { person_id: person.id })"
                class="flex items-center gap-2 px-4 py-2 border border-border-subtle dark:border-gray-600 rounded-lg text-label-sm text-outline hover:text-on-surface bg-white dark:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-[16px]">download</span>
                Descargar PDF
            </a>
        </div>

        <!-- Person selector -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-6 mb-8">
            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider mb-2">Seleccionar persona</label>
            <div class="flex items-center gap-3">
                <div class="flex-1 max-w-md">
                    <SearchSelect v-model="personId" :options="personOptions" placeholder="Buscar persona..." search-placeholder="Escribe un nombre..." />
                </div>
                <button @click="selectPerson" :disabled="!personId" class="px-6 py-2.5 bg-deep-navy text-white rounded-lg text-label-sm hover:bg-primary transition-all disabled:opacity-50">Consultar</button>
            </div>
        </div>

        <!-- Report body -->
        <div v-if="person && stats" class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="px-8 sm:px-10 pt-9 pb-7 border-b border-border-subtle dark:border-gray-700 flex items-start justify-between gap-6">
                <div>
                    <h1 class="font-display font-bold text-2xl sm:text-[26px] tracking-tight text-deep-navy dark:text-blue-300">Reporte de activos asignados</h1>
                    <p class="text-sm text-outline mt-1.5">Inventario individual de equipos de c&oacute;mputo y accesorios de TI</p>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-[11px] font-mono text-outline uppercase">Folio</div>
                    <div class="font-mono text-sm font-semibold text-on-surface dark:text-gray-200">{{ folio }}</div>
                    <div class="text-xs text-outline mt-1">Generado {{ today }}</div>
                </div>
            </div>

            <!-- Person info -->
            <div class="px-8 sm:px-10 py-8 border-b border-border-subtle dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                    <div class="w-16 h-16 rounded-full bg-deep-navy/10 dark:bg-blue-400/10 text-deep-navy dark:text-blue-400 flex items-center justify-center font-display font-semibold text-xl shrink-0">
                        {{ initials }}
                    </div>
                    <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4">
                        <div class="col-span-2 sm:col-span-3">
                            <div class="text-[11px] uppercase font-mono text-outline mb-0.5">Colaborador</div>
                            <div class="font-display font-semibold text-lg text-on-surface dark:text-gray-100 uppercase">{{ person.first_name }} {{ person.last_name }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] uppercase font-mono text-outline mb-0.5">Empresa</div>
                            <div class="text-sm font-medium text-outline dark:text-gray-300">{{ person.company_name || '—' }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] uppercase font-mono text-outline mb-0.5">Departamento</div>
                            <div class="text-sm font-medium text-outline dark:text-gray-300">{{ person.department_name || '—' }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] uppercase font-mono text-outline mb-0.5">Correo</div>
                            <div class="text-sm font-medium text-outline dark:text-gray-300">{{ person.email || '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="px-8 sm:px-10 py-6 border-b border-border-subtle dark:border-gray-700 grid grid-cols-3 gap-4">
                <div>
                    <div class="font-display font-bold text-2xl text-deep-navy dark:text-blue-300">{{ stats.total }}</div>
                    <div class="text-xs text-outline mt-0.5">Activos asignados</div>
                </div>
                <div>
                    <div class="font-display font-bold text-2xl text-green-600 dark:text-green-400">{{ stats.active }}</div>
                    <div class="text-xs text-outline mt-0.5">En uso activo</div>
                </div>
                <div>
                    <div class="font-display font-bold text-2xl text-yellow-600 dark:text-yellow-400">{{ stats.maintenance }}</div>
                    <div class="text-xs text-outline mt-0.5">En mantenimiento</div>
                </div>
            </div>

            <!-- Assets table -->
            <div class="px-8 sm:px-10 py-8">
                <h2 class="text-[11px] uppercase font-mono text-outline mb-4">Detalle de activos</h2>
                <div class="divide-y divide-border-subtle dark:divide-gray-700 border-t border-b border-border-subtle dark:border-gray-700">
                    <div v-for="a in assets" :key="a.id" class="py-4 flex items-center gap-4">
                        <div class="w-9 h-9 rounded-lg bg-surface-container-low dark:bg-gray-700 border border-border-subtle dark:border-gray-600 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-outline text-[18px]">{{ catIcon(a.category_name) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-on-surface dark:text-gray-100 truncate">{{ a.name }}</div>
                            <div class="text-xs text-outline">{{ a.category_name || 'Sin categoría' }}{{ a.brand ? ' · ' + a.brand : '' }}{{ a.model ? ' · ' + a.model : '' }}</div>
                        </div>
                        <div class="hidden sm:block text-right shrink-0">
                            <div class="font-mono text-xs text-outline">Tag</div>
                            <div class="font-mono text-sm text-on-surface dark:text-gray-200">{{ a.asset_tag }}</div>
                        </div>
                        <div class="hidden sm:block text-right w-28 shrink-0">
                            <div class="text-xs text-outline">Serie</div>
                            <div class="text-sm font-mono text-on-surface dark:text-gray-200">{{ a.serial_number || '—' }}</div>
                        </div>
                        <div class="hidden sm:block text-right w-28 shrink-0">
                            <div class="text-xs text-outline">Asignado</div>
                            <div class="text-sm text-on-surface dark:text-gray-200">{{ a.assigned_date || '—' }}</div>
                        </div>
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full shrink-0" :class="statusBg(a.status) + ' ' + statusClass(a.status)">{{ statusLabel[a.status] || a.status }}</span>
                    </div>
                    <div v-if="!assets.length" class="py-8 text-center text-outline text-sm">Sin activos asignados a esta persona.</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 sm:px-10 py-6 bg-surface-container-lowest dark:bg-gray-800/50 border-t border-border-subtle dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <p class="text-xs text-outline max-w-md">Este documento certifica la asignaci&oacute;n de los activos listados bajo la responsabilidad del colaborador indicado, seg&uacute;n pol&iacute;tica interna de uso de equipo de TI.</p>
                <div class="text-xs text-outline font-mono shrink-0">Emitido por: Dept. de TI</div>
            </div>
        </div>

        <div v-else-if="!person" class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-10 text-center">
            <span class="material-symbols-outlined text-outline text-4xl mb-3">person_search</span>
            <p class="text-body-md text-outline">Selecciona una persona para consultar sus activos asignados.</p>
        </div>
    </AppLayout>
</template>
