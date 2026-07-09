<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface AssetRow {
    id: number; asset_tag: string; name: string; status: string;
    brand: string | null; model: string | null; serial_number: string | null;
    company: { id: number; name: string } | null;
    category: { id: number; name: string } | null;
    assigned_person: { id: number; first_name: string; last_name: string } | null;
    parent: { id: number; asset_tag: string } | null;
    children_count?: number;
}

interface PaginationMeta {
    data: AssetRow[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number; to: number; total: number;
}

interface Dept { id: number; name: string }

const props = defineProps<{ assets: PaginationMeta; companies: Dept[]; categories: Dept[] }>();

const search = ref('');
const companyFilter = ref('');
const categoryFilter = ref('');
const statusFilter = ref('');

const applyFilters = () => {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (companyFilter.value) params.company_id = companyFilter.value;
    if (categoryFilter.value) params.category_id = categoryFilter.value;
    if (statusFilter.value) params.status = statusFilter.value;
    router.get(route('admin.assets.index'), params, { preserveState: true, preserveScroll: true, replace: true });
};

const destroy = async (id: number, name: string) => {
    if (!await (window as any).confirmDialog('Eliminar', `¿Eliminar activo "${name}"?`)) return;
    router.delete(route('admin.assets.destroy', id));
};

const statusClass = (s: string) => {
    const map: Record<string, string> = { disponible: 'bg-green-100 text-green-700', asignado: 'bg-blue-100 text-blue-700', en_reparacion: 'bg-yellow-100 text-yellow-700', dado_de_baja: 'bg-gray-100 text-gray-500' };
    return map[s] || 'bg-gray-100 text-gray-500';
};
const statusLabel: Record<string, string> = { disponible: 'Disponible', asignado: 'Asignado', en_reparacion: 'En reparación', dado_de_baja: 'De baja' };
</script>

<template>
    <AppLayout active-nav="assets">
        <Head title="Activos" />
        <div class="flex items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Activos</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Inventario de equipos y dispositivos.</p>
            </div>
            <Link :href="route('admin.assets.create')" class="bg-deep-navy hover:bg-primary text-white px-5 py-2.5 rounded-lg text-label-md flex items-center gap-2">+ Nuevo</Link>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-visible flex flex-col">
            <div class="p-4 flex flex-wrap items-center gap-3 bg-surface-container-lowest dark:bg-gray-800/50 border-b border-border-subtle dark:border-gray-700">
                <div class="relative w-56 shrink-0">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input v-model="search" @keydown.enter="applyFilters" class="w-full pl-10 pr-4 py-2 border border-border-subtle dark:border-gray-600 rounded-lg text-body-sm bg-white dark:bg-gray-700 dark:text-gray-200 focus:ring-primary focus:border-primary outline-none" placeholder="Buscar por código, nombre..." />
                </div>
                <select v-model="companyFilter" class="shrink-0 border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3 focus:ring-primary focus:border-primary outline-none">
                    <option value="">Todas las empresas</option>
                    <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <select v-model="categoryFilter" class="shrink-0 border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3 focus:ring-primary focus:border-primary outline-none">
                    <option value="">Todas las categorías</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <select v-model="statusFilter" class="shrink-0 border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3 focus:ring-primary focus:border-primary outline-none">
                    <option value="">Todos los estados</option>
                    <option value="disponible">Disponible</option>
                    <option value="asignado">Asignado</option>
                    <option value="en_reparacion">En reparación</option>
                    <option value="dado_de_baja">De baja</option>
                </select>
                <button @click="applyFilters" class="px-4 py-2 bg-deep-navy text-white rounded-lg text-label-sm hover:bg-primary transition-all shrink-0">Filtrar</button>
            </div>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Código</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Marca</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Modelo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Serie</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Categoría</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="a in assets.data" :key="a.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-body-sm font-mono text-on-surface dark:text-gray-100">{{ a.asset_tag }}</td>
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100">
                            <Link :href="route('admin.assets.show', a.id)" class="hover:text-primary transition-colors">{{ a.name }}</Link>
                        </td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ a.brand || '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ a.model || '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400 font-mono">{{ a.serial_number || '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ a.category?.name ?? '—' }}</td>
                        <td class="px-6 py-3"><span class="px-2 py-0.5 rounded text-[11px] font-bold" :class="statusClass(a.status)">{{ statusLabel[a.status] || a.status }}</span></td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <Link :href="route('admin.assets.show', a.id)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-deep-navy dark:hover:text-blue-300 hover:bg-surface-container dark:hover:bg-gray-700 rounded" title="Ver"><span class="material-symbols-outlined text-[18px]">visibility</span></Link>
                                <Link :href="route('admin.assets.edit', a.id)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-deep-navy dark:hover:text-blue-300 hover:bg-surface-container dark:hover:bg-gray-700 rounded" title="Editar"><span class="material-symbols-outlined text-[18px]">edit</span></Link>
                                <button @click="destroy(a.id, a.name)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-error hover:bg-error-container/20 dark:hover:bg-red-900/30 rounded" title="Eliminar"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="p-4 flex items-center justify-between border-t border-border-subtle dark:border-gray-700">
                <p class="text-body-sm text-outline">{{ assets.from }}–{{ assets.to }} de {{ assets.total }}</p>
                <div class="flex items-center gap-2">
                    <Link v-for="l in assets.links" :key="l.label" :href="l.url || '#'" :disabled="!l.url || l.active" class="min-w-9 h-9 flex items-center justify-center rounded-lg text-label-sm transition-colors" :class="l.active ? 'bg-deep-navy text-white font-bold' : l.url ? 'border border-border-subtle text-outline hover:bg-surface-container-low' : 'text-outline/30 cursor-not-allowed'" v-html="l.label" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
