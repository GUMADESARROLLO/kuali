<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface CalendarOpt { id: number; name: string }
interface SlaRow { id: number; name: string; description: string | null; calendar: CalendarOpt | null; conditions: Record<string, number[] | string[]> | null; first_response_hours: number; update_hours: number; solution_hours: number; sort_order: number; is_active: boolean }

const props = defineProps<{ rules: SlaRow[]; calendars: CalendarOpt[] }>();

const showModal = ref(false);
const isEditing = ref(false);
const form = ref<{ id?: number; name: string; description: string; calendar_id: number | null; conditions: Record<string, (number | string)[]>; first_response_hours: number; update_hours: number; solution_hours: number; sort_order: number; is_active: boolean }>({
    name: '', description: '', calendar_id: null, conditions: {}, first_response_hours: 0, update_hours: 0, solution_hours: 0, sort_order: 0, is_active: true,
});

const openCreate = () => {
    form.value = { name: '', description: '', calendar_id: props.calendars[0]?.id ?? null, conditions: {}, first_response_hours: 0, update_hours: 0, solution_hours: 0, sort_order: 0, is_active: true };
    isEditing.value = false;
    showModal.value = true;
};

const openEdit = (r: SlaRow) => {
    form.value = { id: r.id, name: r.name, description: r.description ?? '', calendar_id: r.calendar?.id ?? null, conditions: r.conditions ?? {}, first_response_hours: r.first_response_hours, update_hours: r.update_hours, solution_hours: r.solution_hours, sort_order: r.sort_order, is_active: r.is_active };
    isEditing.value = true;
    showModal.value = true;
};

const save = () => {
    const payload = { ...form.value, calendar_id: form.value.calendar_id };
    if (isEditing.value) {
        router.put(route('admin.sla-rules.update', form.value.id!), payload, { preserveScroll: true, preserveState: false, onSuccess: () => showModal.value = false });
    } else {
        router.post(route('admin.sla-rules.store'), payload, { preserveScroll: true, preserveState: false, onSuccess: () => showModal.value = false });
    }
};

const destroy = (id: number, name: string) => {
    if (!confirm(`¿Eliminar regla SLA "${name}"?`)) return;
    router.delete(route('admin.sla-rules.destroy', id));
};
</script>

<template>
    <AppLayout active-nav="sla">
        <Head title="Reglas SLA" />
        <div class="flex items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Reglas SLA</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Define los acuerdos de nivel de servicio para los tickets.</p>
            </div>
            <button @click="openCreate" class="bg-deep-navy hover:bg-primary text-white px-5 py-2.5 rounded-lg text-label-md flex items-center gap-2">+ Nueva</button>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Calendario</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">1ra Resp</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Update</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Soluci&oacute;n</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Activo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="r in rules" :key="r.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100 font-semibold">{{ r.name }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ r.calendar?.name ?? '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ r.first_response_hours ? r.first_response_hours + 'h' : '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ r.update_hours ? r.update_hours + 'h' : '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ r.solution_hours ? r.solution_hours + 'h' : '—' }}</td>
                        <td class="px-6 py-3"><span class="px-2 py-0.5 rounded text-[11px] font-bold" :class="r.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">{{ r.is_active ? 'Sí' : 'No' }}</span></td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="openEdit(r)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-deep-navy dark:hover:text-blue-300 hover:bg-surface-container dark:hover:bg-gray-700 rounded" title="Editar"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                                <button @click="destroy(r.id, r.name)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-error hover:bg-error-container/20 dark:hover:bg-red-900/30 rounded" title="Eliminar"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-xl mx-4 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-4">{{ isEditing ? 'Editar' : 'Nueva' }} Regla SLA</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nombre</label>
                                <input v-model="form.name" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Calendario</label>
                                <select v-model="form.calendar_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option v-for="c in calendars" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Descripci&oacute;n</label>
                            <textarea v-model="form.description" rows="2" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none" />
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">1ra Respuesta (horas)</label>
                                <input v-model.number="form.first_response_hours" type="number" min="0" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Actualizaci&oacute;n (horas)</label>
                                <input v-model.number="form.update_hours" type="number" min="0" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Soluci&oacute;n (horas)</label>
                                <input v-model.number="form.solution_hours" type="number" min="0" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Orden</label>
                                <input v-model.number="form.sort_order" type="number" min="0" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Estado</label>
                                <select v-model="form.is_active" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option :value="true">Activo</option>
                                    <option :value="false">Inactivo</option>
                                </select>
                            </div>
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
