<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface DayHours { enabled: boolean; start: string; end: string }
interface CalendarRow { id: number; name: string; description: string | null; is_active: boolean; hours: Record<string, DayHours> }

const props = defineProps<{ calendars: CalendarRow[] }>();

const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
const dayLabels: Record<string, string> = { mon: 'Lun', tue: 'Mar', wed: 'Mié', thu: 'Jue', fri: 'Vie', sat: 'Sáb', sun: 'Dom' };

const showModal = ref(false);
const isEditing = ref(false);
const form = ref<{ id?: number; name: string; description: string; hours: Record<string, DayHours> }>({
    name: '', description: '',
    hours: Object.fromEntries(days.map(d => [d, { enabled: d !== 'sun', start: '08:00', end: '17:00' }])) as Record<string, DayHours>,
});

const openCreate = () => {
    form.value = { name: '', description: '', hours: Object.fromEntries(days.map(d => [d, { enabled: d !== 'sun', start: '08:00', end: '17:00' }])) };
    isEditing.value = false;
    showModal.value = true;
};

const openEdit = (c: CalendarRow) => {
    form.value = { id: c.id, name: c.name, description: c.description ?? '', hours: { ...c.hours } };
    isEditing.value = true;
    showModal.value = true;
};

const save = () => {
    const payload = { name: form.value.name, description: form.value.description, hours: JSON.stringify(form.value.hours) };
    if (isEditing.value) {
        router.put(route('admin.calendars.update', form.value.id!), payload, { preserveScroll: true, preserveState: false, onSuccess: () => showModal.value = false });
    } else {
        router.post(route('admin.calendars.store'), payload, { preserveScroll: true, preserveState: false, onSuccess: () => showModal.value = false });
    }
};

const destroy = async (id: number, name: string) => {
    if (!await (window as any).confirmDialog('Eliminar', `¿Eliminar calendario "${name}"?`)) return;
    router.delete(route('admin.calendars.destroy', id));
};
</script>

<template>
    <AppLayout active-nav="calendars">
        <Head title="Calendarios" />
        <div class="flex items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Calendarios</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Configura los horarios laborales para el c&aacute;lculo de SLA.</p>
            </div>
            <button @click="openCreate" class="bg-deep-navy hover:bg-primary text-white px-5 py-2.5 rounded-lg text-label-md flex items-center gap-2">+ Nuevo</button>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Horario</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider">Activo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline font-bold tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="c in calendars" :key="c.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100 font-semibold">{{ c.name }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">
                            <div v-for="d in days" :key="d" class="inline-block mr-3 text-[11px]" :class="c.hours[d]?.enabled ? '' : 'opacity-40'">
                                <span class="font-bold">{{ dayLabels[d] }}:</span>
                                <span v-if="c.hours[d]?.enabled">{{ c.hours[d].start }}–{{ c.hours[d].end }}</span>
                                <span v-else>—</span>
                            </div>
                        </td>
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
        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-4">{{ isEditing ? 'Editar' : 'Nuevo' }} Calendario</h3>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nombre</label>
                            <input v-model="form.name" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Descripci&oacute;n</label>
                            <textarea v-model="form.description" rows="2" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none" />
                        </div>
                        <div class="space-y-3">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Horarios</label>
                            <div v-for="d in days" :key="d" class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-container-low dark:hover:bg-gray-700/50">
                                <label class="flex items-center gap-2 w-20 cursor-pointer">
                                    <input type="checkbox" v-model="form.hours[d].enabled" class="rounded border-gray-300 text-primary focus:ring-primary" />
                                    <span class="text-label-sm font-semibold text-gray-700 dark:text-gray-200">{{ dayLabels[d] }}</span>
                                </label>
                                <template v-if="form.hours[d].enabled">
                                    <input type="time" v-model="form.hours[d].start" class="w-28 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-body-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-primary focus:border-primary outline-none" />
                                    <span class="text-outline">—</span>
                                    <input type="time" v-model="form.hours[d].end" class="w-28 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-body-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-primary focus:border-primary outline-none" />
                                </template>
                                <span v-else class="text-label-sm text-outline">Cerrado</span>
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
