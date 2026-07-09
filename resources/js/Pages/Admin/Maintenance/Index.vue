<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface AssetOpt { id: number; asset_tag: string; name: string; company: { name: string } | null }
interface MaintRow { id: number; maintenance_type: string; component: string | null; description: string; cost: string | null; performed_by: string | null; performed_at: string; asset: AssetOpt | null }
interface PagMeta { data: MaintRow[]; links: any[]; from: number; to: number; total: number }

const props = defineProps<{ records: PagMeta; assets: AssetOpt[] }>();

const assetFilter = ref('');
const typeFilter = ref('');

const applyFilters = () => {
    const params: Record<string, string> = {};
    if (assetFilter.value) params.asset_id = assetFilter.value;
    if (typeFilter.value) params.maintenance_type = typeFilter.value;
    router.get(route('admin.maintenance.index'), params, { preserveState: true, preserveScroll: true, replace: true });
};

const destroy = async (id: number) => {
    if (!await (window as any).confirmDialog('Eliminar', '¿Eliminar este registro?')) return;
    router.delete(route('admin.maintenance.destroy', id), { preserveScroll: true });
};

const typeLabel: Record<string, string> = { reparacion: 'Reparación', reemplazo: 'Reemplazo', preventivo: 'Preventivo' };
const typeClass = (t: string) => t === 'reparacion' ? 'bg-red-100 text-red-700' : t === 'reemplazo' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700';
</script>

<template>
    <AppLayout active-nav="maintenance">
        <Head title="Mantenimiento" />
        <div class="flex items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Mantenimiento</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Historial de reparaciones y mantenimiento de activos.</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-visible flex flex-col">
            <div class="p-4 flex flex-wrap items-center gap-3 bg-surface-container-lowest dark:bg-gray-800/50 border-b border-border-subtle dark:border-gray-700">
                <select v-model="assetFilter" class="shrink-0 border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3">
                    <option value="">Todos los activos</option>
                    <option v-for="a in assets" :key="a.id" :value="a.id">{{ a.asset_tag }} — {{ a.name }} ({{ a.company?.name || '?' }})</option>
                </select>
                <select v-model="typeFilter" class="shrink-0 border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3">
                    <option value="">Todos los tipos</option>
                    <option value="reparacion">Reparación</option>
                    <option value="reemplazo">Reemplazo</option>
                    <option value="preventivo">Preventivo</option>
                </select>
                <button @click="applyFilters" class="px-4 py-2 bg-deep-navy text-white rounded-lg text-label-sm hover:bg-primary shrink-0">Filtrar</button>
            </div>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Activo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Componente</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Descripción</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Costo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="r in records.data" :key="r.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-body-sm font-mono text-on-surface dark:text-gray-100">{{ r.asset?.asset_tag ?? '—' }}</td>
                        <td class="px-6 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="typeClass(r.maintenance_type)">{{ typeLabel[r.maintenance_type] || r.maintenance_type }}</span></td>
                        <td class="px-6 py-3 text-body-sm text-outline">{{ r.component || '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline max-w-xs truncate">{{ r.description }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline">{{ r.cost ? 'S/ ' + r.cost : '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline">{{ new Date(r.performed_at).toLocaleDateString('es') }}</td>
                        <td class="px-6 py-3"><div class="flex items-center justify-center"><button @click="destroy(r.id)" class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error-container/20 rounded" title="Eliminar"><span class="material-symbols-outlined text-[18px]">delete</span></button></div></td>
                    </tr>
                    <tr v-if="!records.data.length"><td colspan="7" class="py-12 text-center text-outline text-body-sm">Sin registros.</td></tr>
                </tbody>
            </table>
            <div class="p-4 flex items-center justify-between border-t border-border-subtle">
                <p class="text-body-sm text-outline">{{ records.from }}–{{ records.to }} de {{ records.total }}</p>
                <div class="flex items-center gap-2">
                    <button v-for="l in records.links" :key="l.label" @click="l.url && router.visit(l.url, { preserveState: true })" :disabled="!l.url || l.active" class="min-w-9 h-9 flex items-center justify-center rounded-lg text-label-sm" :class="l.active ? 'bg-deep-navy text-white font-bold' : l.url ? 'border border-border-subtle text-outline hover:bg-surface-container-low' : 'text-outline/30 cursor-not-allowed'" v-html="l.label" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
