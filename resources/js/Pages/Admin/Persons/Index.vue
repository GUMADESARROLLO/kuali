<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Dept { id: number; name: string; company_id: number }
interface Co { id: number; name: string }
interface PersonRow { id: number; first_name: string; last_name: string; email: string | null; phone: string | null; is_active: boolean; company: Co | null; department: Dept | null }
interface PagMeta { data: PersonRow[]; links: any[]; from: number; to: number; total: number }

const props = defineProps<{ persons: PagMeta; companies: Co[]; departments: Dept[] }>();

const showModal = ref(false);
const isEditing = ref(false);
const form = ref<{ id?: number; first_name: string; last_name: string; email: string; phone: string; company_id: number | null; department_id: number | null; is_active: boolean }>({
    first_name: '', last_name: '', email: '', phone: '', company_id: null, department_id: null, is_active: true,
});

const openCreate = () => {
    form.value = { first_name: '', last_name: '', email: '', phone: '', company_id: null, department_id: null, is_active: true };
    isEditing.value = false; showModal.value = true;
};
const openEdit = (p: PersonRow) => {
    form.value = { id: p.id, first_name: p.first_name, last_name: p.last_name, email: p.email ?? '', phone: p.phone ?? '', company_id: p.company?.id ?? null, department_id: p.department?.id ?? null, is_active: p.is_active };
    isEditing.value = true; showModal.value = true;
};

const filteredDepts = computed(() => props.departments.filter(d => d.company_id === form.value.company_id));

const save = () => {
    const payload = { ...form.value };
    if (isEditing.value) {
        router.put(route('admin.persons.update', form.value.id!), payload, { preserveScroll: true, preserveState: false, onSuccess: () => showModal.value = false });
    } else {
        router.post(route('admin.persons.store'), payload, { preserveScroll: true, preserveState: false, onSuccess: () => showModal.value = false });
    }
};

const destroy = async (id: number, name: string) => {
    if (!await (window as any).confirmDialog('Eliminar', `¿Eliminar a "${name}"?`)) return;
    router.delete(route('admin.persons.destroy', id));
};

const search = ref('');
const companyFilter = ref('');
const applyFilters = () => {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (companyFilter.value) params.company_id = companyFilter.value;
    router.get(route('admin.persons.index'), params, { preserveState: true, preserveScroll: true, replace: true });
};
</script>

<template>
    <AppLayout active-nav="persons">
        <Head title="Personas" />
        <div class="flex items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Personas</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Directorio de personas para asignaci&oacute;n de activos.</p>
            </div>
            <button @click="openCreate" class="bg-deep-navy hover:bg-primary text-white px-5 py-2.5 rounded-lg text-label-md flex items-center gap-2">+ Nueva</button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-visible flex flex-col">
            <div class="p-4 flex flex-wrap items-center gap-3 bg-surface-container-lowest dark:bg-gray-800/50 border-b border-border-subtle">
                <div class="relative w-56 shrink-0">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input v-model="search" @keydown.enter="applyFilters" class="w-full pl-10 pr-4 py-2 border border-border-subtle dark:border-gray-600 rounded-lg text-body-sm bg-white dark:bg-gray-700 dark:text-gray-200" placeholder="Buscar persona..." />
                </div>
                <select v-model="companyFilter" class="shrink-0 border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3">
                    <option value="">Todas las empresas</option>
                    <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <button @click="applyFilters" class="px-4 py-2 bg-deep-navy text-white rounded-lg text-label-sm hover:bg-primary shrink-0">Filtrar</button>
            </div>

            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Email</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Tel&eacute;fono</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Empresa</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Departamento</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="p in persons.data" :key="p.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100 font-semibold uppercase">{{ p.first_name }} {{ p.last_name }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ p.email || '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ p.phone || '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ p.company?.name ?? '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ p.department?.name ?? '—' }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="openEdit(p)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-deep-navy dark:hover:text-blue-300 hover:bg-surface-container dark:hover:bg-gray-700 rounded transition-all" title="Editar"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                                <button @click="destroy(p.id, p.first_name + ' ' + p.last_name)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-error hover:bg-error-container/20 dark:hover:bg-red-900/30 rounded transition-all" title="Eliminar"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!persons.data.length"><td colspan="7" class="py-12 text-center text-outline text-body-sm">Sin personas registradas.</td></tr>
                </tbody>
            </table>
            <div class="p-4 flex items-center justify-between border-t border-border-subtle dark:border-gray-700">
                <p class="text-body-sm text-outline dark:text-gray-400">{{ persons.from }}–{{ persons.to }} de {{ persons.total }}</p>
                <div class="flex items-center gap-2">
                    <button v-for="l in persons.links" :key="l.label" @click="l.url && router.visit(l.url, { preserveState: true })" :disabled="!l.url || l.active" class="min-w-9 h-9 flex items-center justify-center rounded-lg text-label-sm" :class="l.active ? 'bg-deep-navy text-white font-bold' : l.url ? 'border border-border-subtle dark:border-gray-600 text-outline dark:text-gray-300 hover:bg-surface-container-low dark:hover:bg-gray-700' : 'text-outline/30 dark:text-gray-600 cursor-not-allowed'" v-html="l.label" />
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-lg mx-4">
                    <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-4">{{ isEditing ? 'Editar' : 'Nueva' }} Persona</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-xs text-outline uppercase tracking-wider">Nombre</label>
                                <input v-model="form.first_name" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs text-outline uppercase tracking-wider">Apellidos</label>
                                <input v-model="form.last_name" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-xs text-outline uppercase tracking-wider">Email</label>
                                <input v-model="form.email" type="email" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs text-outline uppercase tracking-wider">Tel&eacute;fono</label>
                                <input v-model="form.phone" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-xs text-outline uppercase tracking-wider">Empresa</label>
                                <select v-model="form.company_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option :value="null">Seleccionar</option>
                                    <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs text-outline uppercase tracking-wider">Departamento</label>
                                <select v-model="form.department_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option :value="null">Seleccionar</option>
                                    <option v-for="d in filteredDepts" :key="d.id" :value="d.id">{{ d.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs text-outline uppercase tracking-wider">Estado</label>
                            <select v-model="form.is_active" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option :value="true">Activo</option>
                                <option :value="false">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showModal = false" class="px-4 py-2 border border-border-subtle rounded-lg text-label-sm text-outline hover:bg-surface-container-low">Cancelar</button>
                        <button @click="save" class="px-6 py-2.5 bg-deep-navy text-white rounded-lg text-label-sm hover:shadow-md">{{ isEditing ? 'Actualizar' : 'Crear' }}</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
