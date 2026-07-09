<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface CompanyRow { id: number; name: string; slug: string; is_active: boolean }

const props = defineProps<{ companies: CompanyRow[] }>();

const showModal = ref(false);
const isEditing = ref(false);
const form = ref<{ id?: number; name: string; is_active: boolean }>({ name: '', is_active: true });

const openCreate = () => { form.value = { name: '', is_active: true }; isEditing.value = false; showModal.value = true; };
const openEdit = (c: CompanyRow) => { form.value = { id: c.id, name: c.name, is_active: c.is_active }; isEditing.value = true; showModal.value = true; };

const save = () => {
    const payload = { name: form.value.name, is_active: form.value.is_active };
    if (isEditing.value) {
        router.put(route('admin.companies.update', form.value.id!), payload, { preserveScroll: true, preserveState: false, onSuccess: () => showModal.value = false });
    } else {
        router.post(route('admin.companies.store'), payload, { preserveScroll: true, preserveState: false, onSuccess: () => showModal.value = false });
    }
};

const destroy = async (id: number, name: string) => {
    if (!await (window as any).confirmDialog('Eliminar', `¿Eliminar empresa "${name}"?`)) return;
    router.delete(route('admin.companies.destroy', id));
};
</script>

<template>
    <AppLayout active-nav="companies">
        <Head title="Empresas" />
        <div class="flex items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Empresas</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Gestiona las empresas del sistema.</p>
            </div>
            <button @click="openCreate" class="bg-deep-navy hover:bg-primary text-white px-5 py-2.5 rounded-lg text-label-md flex items-center gap-2">+ Nueva</button>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Activo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="c in companies" :key="c.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100 font-semibold">{{ c.name }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400 font-mono">{{ c.slug }}</td>
                        <td class="px-6 py-3"><span class="px-2 py-0.5 rounded text-[11px] font-bold" :class="c.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">{{ c.is_active ? 'Sí' : 'No' }}</span></td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="openEdit(c)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-deep-navy dark:hover:text-blue-300 hover:bg-surface-container dark:hover:bg-gray-700 rounded" title="Editar"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                                <button @click="destroy(c.id, c.name)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-error hover:bg-error-container/20 dark:hover:bg-red-900/30 rounded" title="Eliminar"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-md mx-4">
                    <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-4">{{ isEditing ? 'Editar' : 'Nueva' }} Empresa</h3>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nombre</label>
                            <input v-model="form.name" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Estado</label>
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
