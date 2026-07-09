<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import DatePicker from '@/Components/DatePicker.vue';
import SearchSelect from '@/Components/SearchSelect.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface CatOpt { id: number; name: string }

const props = defineProps<{
    asset?: { id: number; asset_tag: string; name: string; brand: string | null; model: string | null; serial_number: string | null; status: string; person_id: number | null; location: string | null; purchase_date: string | null; purchase_cost: string | null; warranty_expiry: string | null; notes: string | null; asset_category_id: number | null; parent_asset_id: number | null };
    nextCode?: string;
    categories: CatOpt[];
    people: { id: number; first_name: string; last_name: string; company: { name: string } | null; department: { name: string } | null }[];
    assets: { id: number; asset_tag: string; name: string }[];
}>();

const isEditing = !!props.asset;
const form = useForm({
    name: props.asset?.name ?? '',
    asset_category_id: props.asset?.asset_category_id ?? null,
    parent_asset_id: props.asset?.parent_asset_id ?? null,
    brand: props.asset?.brand ?? '',
    model: props.asset?.model ?? '',
    serial_number: props.asset?.serial_number ?? '',
    status: props.asset?.status ?? 'disponible',
    person_id: props.asset?.person_id ?? null,
    location: props.asset?.location ?? '',
    purchase_date: props.asset?.purchase_date ?? '',
    purchase_cost: props.asset?.purchase_cost ?? '',
    warranty_expiry: props.asset?.warranty_expiry ?? '',
    notes: props.asset?.notes ?? '',
});

const personOptions = computed(() =>
    props.people.map(p => ({
        id: p.id,
        label: `${p.last_name}, ${p.first_name}`,
        subtitle: [p.company?.name, p.department?.name].filter(Boolean).join(' · '),
    }))
);

const submit = () => {
    if (isEditing) {
        form.put(route('admin.assets.update', props.asset!.id));
    } else {
        form.post(route('admin.assets.store'));
    }
};
</script>

<template>
    <AppLayout active-nav="assets">
        <Head :title="isEditing ? 'Editar Activo' : 'Nuevo Activo'" />
        <div>
            <div class="mb-8">
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">{{ isEditing ? 'Editar' : 'Nuevo' }} Activo</h2>
            </div>
            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">C&oacute;digo</label>
                        <div class="px-4 py-3 border border-border-subtle dark:border-gray-600 rounded-lg bg-surface-container-low dark:bg-gray-700/50 text-on-surface dark:text-gray-200 font-mono text-sm flex items-center gap-3">
                            <span class="material-symbols-outlined text-outline text-[18px]">tag</span>
                            <span v-if="asset">{{ asset.asset_tag }}</span>
                            <span v-else class="text-outline">{{ nextCode }} <span class="text-outline/60 font-normal">(generado autom&aacute;ticamente)</span></span>
                        </div>
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Nombre</label>
                        <input v-model="form.name" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        <p v-if="form.errors.name" class="text-error text-label-sm">{{ form.errors.name }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Categor&iacute;a</label>
                        <select v-model="form.asset_category_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option :value="null">Seleccionar</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <p v-if="form.errors.asset_category_id" class="text-error text-label-sm">{{ form.errors.asset_category_id }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Estado</label>
                        <select v-model="form.status" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="disponible">Disponible</option>
                            <option value="asignado">Asignado</option>
                            <option value="en_reparacion">En reparaci&oacute;n</option>
                            <option value="dado_de_baja">Dado de baja</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Ubicaci&oacute;n</label>
                        <input v-model="form.location" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Marca</label>
                        <input v-model="form.brand" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Modelo</label>
                        <input v-model="form.model" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">N&uacute;mero de serie</label>
                        <input v-model="form.serial_number" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Asignado a</label>
                    <SearchSelect
                        v-model="form.person_id"
                        :options="personOptions"
                        placeholder="Buscar persona..."
                        search-placeholder="Escribe para buscar..."
                    />
                </div>

                <div class="space-y-2">
                    <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Activo padre</label>
                    <select v-model="form.parent_asset_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option :value="null">Ninguno (es equipo principal)</option>
                        <option v-for="a in assets" :key="a.id" :value="a.id">{{ a.asset_tag }} — {{ a.name }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Fecha de compra</label>
                        <DatePicker v-model="form.purchase_date" placeholder="Seleccionar" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Costo (S/)</label>
                        <input v-model="form.purchase_cost" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Garant&iacute;a hasta</label>
                        <DatePicker v-model="form.warranty_expiry" placeholder="Seleccionar" />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Notas</label>
                    <textarea v-model="form.notes" rows="3" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none" />
                </div>

                <div class="flex items-center justify-end gap-4 pt-4 border-t border-border-subtle dark:border-gray-600">
                    <a :href="route('admin.assets.index')" class="px-4 py-2 text-label-md text-outline hover:text-on-surface transition-colors">Cancelar</a>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-deep-navy text-white rounded-lg text-label-md disabled:opacity-50 hover:shadow-md transition-all">{{ isEditing ? 'Actualizar' : 'Crear' }}</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
