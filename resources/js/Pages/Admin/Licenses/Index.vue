<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import DatePicker from '@/Components/DatePicker.vue';
import PaginationBar from '@/Components/PaginationBar.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface LicenseRow { id: number; name: string; license_key: string | null; vendor: string | null; seats_total: number; seats_used: number; recurring: boolean; billing_period: string | null; purchase_date: string | null; expiry_date: string | null; cost: string | null; company: { id: number; name: string } | null }
interface PagMeta { data: LicenseRow[]; links: any[]; from: number; to: number; total: number }
interface Dept { id: number; name: string }

const props = defineProps<{ licenses: PagMeta; companies: Dept[] }>();

const showModal = ref(false);
const isEditing = ref(false);
const form = ref<{ id?: number; company_id: number | null; name: string; license_key: string; vendor: string; seats_total: number; seats_used: number; billing_type: string; purchase_date: string; expiry_date: string; cost: string; notes: string }>({
    company_id: null, name: '', license_key: '', vendor: '', seats_total: 1, seats_used: 0, billing_type: 'unico', purchase_date: '', expiry_date: '', cost: '', notes: '',
});

const openCreate = () => {
    form.value = { company_id: props.companies[0]?.id ?? null, name: '', license_key: '', vendor: '', seats_total: 1, seats_used: 0, billing_type: 'unico', purchase_date: '', expiry_date: '', cost: '', notes: '' };
    isEditing.value = false; showModal.value = true;
};
const openEdit = (l: LicenseRow) => {
    const bt = l.recurring ? (l.billing_period || 'mensual') : 'unico';
    form.value = { id: l.id, company_id: l.company?.id ?? null, name: l.name, license_key: l.license_key ?? '', vendor: l.vendor ?? '', seats_total: l.seats_total, seats_used: l.seats_used, billing_type: bt, purchase_date: l.purchase_date ?? '', expiry_date: l.expiry_date ?? '', cost: l.cost ?? '', notes: '' };
    isEditing.value = true; showModal.value = true;
};

const save = () => {
    const bt = form.value.billing_type;
    const payload: Record<string, unknown> = {
        ...form.value, company_id: form.value.company_id,
        recurring: bt !== 'unico',
        billing_period: bt !== 'unico' ? bt : null,
    };
    delete payload.billing_type;
    if (isEditing.value) {
        router.put(route('admin.licenses.update', form.value.id!), payload as never, { preserveScroll: true, preserveState: false, onSuccess: () => showModal.value = false });
    } else {
        router.post(route('admin.licenses.store'), payload as never, { preserveScroll: true, preserveState: false, onSuccess: () => showModal.value = false });
    }
};

const destroy = async (id: number, name: string) => {
    if (!await (window as any).confirmDialog('Eliminar', `¿Eliminar licencia "${name}"?`)) return;
    router.delete(route('admin.licenses.destroy', id));
};

const search = ref('');
const companyFilter = ref('');
const applyFilters = () => {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (companyFilter.value) params.company_id = companyFilter.value;
    router.get(route('admin.licenses.index'), params, { preserveState: true, preserveScroll: true, replace: true });
};
</script>

<template>
    <AppLayout active-nav="licenses">
        <Head title="Licencias" />
        <div class="flex items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Licencias de Software</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Gestiona las licencias de software y su asignaci&oacute;n a activos.</p>
            </div>
            <button @click="openCreate" class="bg-deep-navy hover:bg-primary text-white px-5 py-2.5 rounded-lg text-label-md flex items-center gap-2">+ Nueva</button>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-visible flex flex-col">
            <div class="p-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 bg-surface-container-lowest dark:bg-gray-800/50 border-b border-border-subtle dark:border-gray-700">
                <div class="relative w-full sm:w-56 shrink-0">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input v-model="search" @keydown.enter="applyFilters" class="w-full pl-10 pr-4 py-2 border border-border-subtle dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-gray-200" placeholder="Buscar licencia..." />
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <select v-model="companyFilter" class="flex-1 sm:w-auto shrink-0 border-border-subtle dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-label-sm py-2 px-3">
                        <option value="">Todas las empresas</option>
                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <button @click="applyFilters" class="px-4 py-2 bg-deep-navy text-white rounded-lg text-label-sm hover:bg-primary shrink-0">Filtrar</button>
                </div>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full text-left min-w-[650px]">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider hidden sm:table-cell">Empresa</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider hidden md:table-cell">Proveedor</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Asientos</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider hidden md:table-cell">Vence</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Tipo pago</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider hidden sm:table-cell">Coste</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="l in licenses.data" :key="l.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100 font-semibold">{{ l.name }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400 hidden sm:table-cell">{{ l.company?.name ?? '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400 hidden md:table-cell">{{ l.vendor || '—' }}</td>
                        <td class="px-6 py-3 text-body-sm"><span class="font-bold" :class="l.seats_used >= l.seats_total ? 'text-error' : 'text-on-surface dark:text-gray-200'">{{ l.seats_used }}/{{ l.seats_total }}</span></td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400 hidden md:table-cell">{{ l.expiry_date ? new Date(l.expiry_date).toLocaleDateString('es') : '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ l.recurring ? (l.billing_period === 'mensual' ? 'Mensual' : 'Anual') : 'Único' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400 font-mono hidden sm:table-cell">{{ l.cost || '—' }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="openEdit(l)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-deep-navy dark:hover:text-blue-300 hover:bg-surface-container dark:hover:bg-gray-700 rounded transition-all" title="Editar"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                                <button @click="destroy(l.id, l.name)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-error hover:bg-error-container/20 dark:hover:bg-red-900/30 rounded transition-all" title="Eliminar"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!licenses.data.length"><td colspan="8" class="py-12 text-center text-outline dark:text-gray-400 text-body-sm">Sin licencias.</td></tr>
                </tbody>
            </table>
            </div>
            <PaginationBar :links="licenses.links" :from="licenses.from" :to="licenses.to" :total="licenses.total" />
        </div>

        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-4">{{ isEditing ? 'Editar' : 'Nueva' }} Licencia</h3>
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Empresa</label>
                            <select v-model="form.company_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Nombre</label>
                            <input v-model="form.name" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Clave / Serial</label>
                            <input v-model="form.license_key" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Proveedor</label>
                            <input v-model="form.vendor" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Asientos totales</label>
                            <input v-model.number="form.seats_total" type="number" min="1" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Asientos usados</label>
                            <input v-model.number="form.seats_used" type="number" min="0" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Tipo de pago</label>
                            <select v-model="form.billing_type" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="unico">&Uacute;nico</option>
                                <option value="mensual">Mensual</option>
                                <option value="anual">Anual</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Fecha de compra</label>
                            <DatePicker v-model="form.purchase_date" placeholder="Seleccionar" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Fecha de vencimiento</label>
                            <DatePicker v-model="form.expiry_date" placeholder="Seleccionar" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Costo (S/)</label>
                            <input v-model="form.cost" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Notas</label>
                            <textarea v-model="form.notes" rows="2" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none" />
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
