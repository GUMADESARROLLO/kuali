<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

interface Co { id: number; name: string }
interface DeptRow { id: number; name: string; slug: string; description: string | null; is_active: boolean; company: Co | null }
interface PagMeta { data: DeptRow[]; links: { url: string | null; label: string; active: boolean }[]; from: number; to: number; total: number }

const props = defineProps<{ departments: PagMeta; companies: Co[] }>();

const search = ref('');

const applySearch = () => {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    router.get(route('admin.departments.index'), params, { preserveState: true, preserveScroll: true, replace: true });
};

const showModal = ref(false);
const isEditing = ref(false);
const form = ref<{ id?: number; company_id: number | null; name: string; description: string; is_active: boolean }>({
    company_id: null, name: '', description: '', is_active: true,
});

const openCreate = () => {
    form.value = { company_id: props.companies[0]?.id ?? null, name: '', description: '', is_active: true };
    isEditing.value = false;
    showModal.value = true;
};

const openEdit = (d: DeptRow) => {
    form.value = { id: d.id, company_id: d.company?.id ?? null, name: d.name, description: d.description ?? '', is_active: d.is_active };
    isEditing.value = true;
    showModal.value = true;
};

const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });

const save = () => {
    const payload = { ...form.value };
    if (isEditing.value) {
        router.put(route('admin.departments.update', form.value.id!), payload, {
            preserveScroll: true, preserveState: false,
            onSuccess: () => { showModal.value = false; Toast.fire({ icon: 'success', title: 'Departamento actualizado.' }); },
            onError: () => Toast.fire({ icon: 'error', title: 'Error al actualizar departamento.' }),
        });
    } else {
        router.post(route('admin.departments.store'), payload, {
            preserveScroll: true, preserveState: false,
            onSuccess: () => { showModal.value = false; Toast.fire({ icon: 'success', title: 'Departamento creado.' }); },
            onError: () => Toast.fire({ icon: 'error', title: 'Error al crear departamento.' }),
        });
    }
};

const destroy = async (id: number) => {
    if (!await (window as any).confirmDialog('Eliminar', '¿Eliminar este departamento?')) return;
    router.delete(route('admin.departments.destroy', id));
};

const toggleActive = (d: DeptRow) => {
    router.put(route('admin.departments.update', d.id), {
        name: d.name, description: d.description, is_active: !d.is_active,
    }, { preserveScroll: true });
};
</script>

<template>
    <AppLayout active-nav="departments">
        <Head title="Departamentos" />

        <div class="flex items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Departamentos</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Gestiona los departamentos de la empresa.</p>
            </div>
            <button @click="openCreate" class="bg-deep-navy hover:bg-primary text-white px-5 py-2.5 rounded-lg text-label-md flex items-center gap-2">+ Nuevo</button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="p-4 flex items-center gap-3 bg-surface-container-lowest dark:bg-gray-800/50 border-b border-border-subtle dark:border-gray-700">
                <div class="relative w-56 shrink-0">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input v-model="search" @keydown.enter="applySearch" class="w-full pl-10 pr-4 py-2 border border-border-subtle dark:border-gray-600 rounded-lg text-body-sm bg-white dark:bg-gray-700 dark:text-gray-200" placeholder="Buscar departamento..." />
                </div>
                <button @click="applySearch" class="px-4 py-2 bg-deep-navy text-white rounded-lg text-label-sm hover:bg-primary shrink-0">Buscar</button>
            </div>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Empresa</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="d in departments.data" :key="d.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ d.company?.name ?? '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100 font-semibold">{{ d.name }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400 font-mono">{{ d.slug }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="openEdit(d)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-deep-navy dark:hover:text-blue-300 hover:bg-surface-container dark:hover:bg-gray-700 rounded transition-all" title="Editar">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button @click="destroy(d.id)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-error hover:bg-error-container/20 dark:hover:bg-red-900/30 rounded transition-all" title="Eliminar">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="p-4 flex items-center justify-between border-t border-border-subtle dark:border-gray-700">
                <p class="text-body-sm text-outline dark:text-gray-400">{{ departments.from }}–{{ departments.to }} de {{ departments.total }}</p>
                <div class="flex items-center gap-2">
                    <button v-for="l in departments.links" :key="l.label" @click="l.url && router.visit(l.url, { preserveState: true, preserveScroll: true })" :disabled="!l.url || l.active" class="min-w-9 h-9 flex items-center justify-center rounded-lg text-label-sm" :class="l.active ? 'bg-deep-navy text-white font-bold' : l.url ? 'border border-border-subtle dark:border-gray-600 text-outline dark:text-gray-300 hover:bg-surface-container-low dark:hover:bg-gray-700' : 'text-outline/30 dark:text-gray-600 cursor-not-allowed'" v-html="l.label" />
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-lg mx-4">
                    <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-4">{{ isEditing ? 'Editar' : 'Nuevo' }} Departamento</h3>
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Empresa</label>
                            <select v-model="form.company_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option :value="null">Seleccionar empresa</option>
                                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Nombre</label>
                            <input v-model="form.name" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Descripci&oacute;n</label>
                            <textarea v-model="form.description" rows="2" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Estado</label>
                            <select v-model="form.is_active" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option :value="true">Activo</option>
                                <option :value="false">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showModal = false" class="px-4 py-2 border border-border-subtle rounded-lg text-label-sm text-outline hover:bg-surface-container-low transition-all">Cancelar</button>
                        <button @click="save" class="px-6 py-2.5 bg-deep-navy text-white rounded-lg text-label-sm hover:shadow-md transition-all">{{ isEditing ? 'Actualizar' : 'Crear' }}</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
