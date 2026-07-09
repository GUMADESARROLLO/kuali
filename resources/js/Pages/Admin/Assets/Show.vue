<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import DatePicker from '@/Components/DatePicker.vue';
import SearchSelect from '@/Components/SearchSelect.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface AssetDetail {
    id: number; asset_tag: string; name: string; status: string; brand: string | null;
    model: string | null; serial_number: string | null; location: string | null;
    purchase_date: string | null; purchase_cost: string | null; warranty_expiry: string | null;
    notes: string | null;
    company: { id: number; name: string } | null;
    category: { id: number; name: string } | null;
    assigned_person: { id: number; first_name: string; last_name: string; email: string | null; company: { name: string } | null; department: { name: string } | null } | null;
    parent: { id: number; asset_tag: string; name: string } | null;
    children: ChildAsset[];
    maintenance: MaintRecord[];
    software_licenses: LicenseAssign[];
}
interface ChildAsset { id: number; asset_tag: string; name: string; brand: string | null; model: string | null; serial_number: string | null; status: string }
interface MaintRecord { id: number; maintenance_type: string; component: string | null; description: string; cost: string | null; performed_by: string | null; performed_at: string; notes: string | null }
interface LicenseAssign { id: number; name: string; pivot: { installed_at: string | null; uninstalled_at: string | null } }
interface PersonOpt { id: number; first_name: string; last_name: string; email: string | null }
interface CatOpt { id: number; name: string }
interface LicenseOpt { id: number; name: string; seats_used: number; seats_total: number }

const props = defineProps<{ asset: AssetDetail; people: PersonOpt[]; categories: CatOpt[]; companies: CatOpt[]; licenses: LicenseOpt[] }>();

const showAssignModal = ref(false);
const personId = ref(props.asset.assigned_person?.id ?? '');

const showMaintModal = ref(false);
const editingMaint = ref<{ id?: number; asset_id: number; maintenance_type: string; component: string; description: string; cost: string; performed_by: string; performed_at: string }>({
    asset_id: props.asset.id, maintenance_type: 'reparacion', component: '', description: '', cost: '', performed_by: '', performed_at: new Date().toISOString().slice(0, 16),
});

const showLicenseModal = ref(false);
const selLicenseId = ref('');

const saveAssign = () => {
    router.post(route('admin.assets.assign', props.asset.id), { person_id: personId.value || null }, { preserveScroll: true, preserveState: false, onSuccess: () => showAssignModal.value = false });
};

const saveMaint = () => {
    const payload = { ...editingMaint.value, cost: editingMaint.value.cost || null };
    router.post(route('admin.maintenance.store'), payload, { preserveScroll: true, onSuccess: () => showMaintModal.value = false });
};

const deleteMaint = async (id: number) => {
    if (!await (window as any).confirmDialog('Eliminar', '¿Eliminar este registro de mantenimiento?')) return;
    router.delete(route('admin.maintenance.destroy', id), { preserveScroll: true });
};

const licenseOptions = computed(() => props.licenses.map(l => ({
    id: l.id,
    label: l.name,
    subtitle: `${l.seats_used}/${l.seats_total} asientos`,
})));

const saveLicense = () => {
    if (!selLicenseId.value) return;
    router.post(route('admin.licenses.assign', selLicenseId.value), { asset_id: props.asset.id }, { preserveScroll: true, preserveState: false, onSuccess: () => showLicenseModal.value = false });
};

const statusLabel: Record<string, string> = { disponible: 'Disponible', asignado: 'Asignado', en_reparacion: 'En reparación', dado_de_baja: 'De baja' };
const statusClass = (s: string) => ({ 'bg-green-100 text-green-700': s === 'disponible', 'bg-blue-100 text-blue-700': s === 'asignado', 'bg-yellow-100 text-yellow-700': s === 'en_reparacion', 'bg-gray-100 text-gray-500': s === 'dado_de_baja' });
</script>

<template>
    <AppLayout active-nav="assets">
        <Head :title="asset.asset_tag + ' - ' + asset.name" />
        <div class="mb-6">
            <Link :href="route('admin.assets.index')" class="text-label-sm text-outline hover:text-on-surface transition-colors flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">arrow_back</span> Volver a activos</Link>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-display-sm text-deep-navy dark:text-blue-300 mb-1">{{ asset.asset_tag }}</h2>
                            <p class="text-body-md text-on-surface dark:text-gray-100">{{ asset.name }}</p>
                        </div>
                        <span class="px-3 py-1 rounded text-label-sm font-bold" :class="statusClass(asset.status)">{{ statusLabel[asset.status] || asset.status }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-body-sm">
                        <div><span class="text-outline">Marca:</span> <span class="text-on-surface dark:text-gray-200">{{ asset.brand || '—' }}</span></div>
                        <div><span class="text-outline">Modelo:</span> <span class="text-on-surface dark:text-gray-200">{{ asset.model || '—' }}</span></div>
                        <div><span class="text-outline">Serie:</span> <span class="text-on-surface dark:text-gray-200">{{ asset.serial_number || '—' }}</span></div>
                        <div><span class="text-outline">Ubicación:</span> <span class="text-on-surface dark:text-gray-200">{{ asset.location || '—' }}</span></div>
                        <div><span class="text-outline">Compra:</span> <span class="text-on-surface dark:text-gray-200">{{ asset.purchase_date || '—' }}</span></div>
                        <div><span class="text-outline">Costo:</span> <span class="text-on-surface dark:text-gray-200">{{ asset.purchase_cost ? 'S/ ' + asset.purchase_cost : '—' }}</span></div>
                        <div><span class="text-outline">Garantía:</span> <span class="text-on-surface dark:text-gray-200">{{ asset.warranty_expiry || '—' }}</span></div>
                        <div><span class="text-outline">Empresa:</span> <span class="text-on-surface dark:text-gray-200">{{ asset.company?.name || '—' }}</span></div>
                    </div>
                    <div v-if="asset.notes" class="mt-4 pt-4 border-t border-border-subtle"><p class="text-body-sm text-outline">{{ asset.notes }}</p></div>
                </div>

                <!-- Components -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-4">Componentes</h3>
                    <div v-if="!asset.children?.length" class="text-body-sm text-outline dark:text-gray-400">Sin componentes asociados.</div>
                    <table v-else class="w-full text-left">
                        <thead><tr class="border-b border-border-subtle dark:border-gray-600"><th class="py-2 px-3 text-label-sm text-outline dark:text-gray-400 font-bold uppercase tracking-wider">Código</th><th class="py-2 px-3 text-label-sm text-outline dark:text-gray-400 font-bold uppercase tracking-wider">Nombre</th><th class="py-2 px-3 text-label-sm text-outline dark:text-gray-400 font-bold uppercase tracking-wider">Marca</th><th class="py-2 px-3 text-label-sm text-outline dark:text-gray-400 font-bold uppercase tracking-wider">Modelo</th><th class="py-2 px-3 text-label-sm text-outline dark:text-gray-400 font-bold uppercase tracking-wider">Serie</th></tr></thead>
                        <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                            <tr v-for="ch in asset.children" :key="ch.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50">
                                <td class="py-2 px-3"><Link :href="route('admin.assets.show', ch.id)" class="text-body-sm font-mono text-deep-navy/70 dark:text-blue-400 hover:text-deep-navy dark:hover:text-blue-300 transition-colors">{{ ch.asset_tag }}</Link></td>
                                <td class="py-2 px-3 text-body-sm text-on-surface dark:text-gray-200">{{ ch.name }}</td>
                                <td class="py-2 px-3 text-body-sm text-outline dark:text-gray-400">{{ ch.brand || '—' }}</td>
                                <td class="py-2 px-3 text-body-sm text-outline dark:text-gray-400">{{ ch.model || '—' }}</td>
                                <td class="py-2 px-3 text-body-sm text-outline dark:text-gray-400 font-mono">{{ ch.serial_number || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Maintenance -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100">Mantenimiento</h3>
                        <button @click="editingMaint = { asset_id: asset.id, maintenance_type: 'reparacion', component: '', description: '', cost: '', performed_by: '', performed_at: '' }; showMaintModal = true" class="px-3 py-1.5 bg-deep-navy text-white rounded-lg text-label-sm hover:bg-primary transition-all">+ Agregar</button>
                    </div>
                    <div v-if="!asset.maintenance?.length" class="text-body-sm text-outline">Sin registros de mantenimiento.</div>
                    <div v-for="m in asset.maintenance" :key="m.id" class="border border-border-subtle dark:border-gray-600 rounded-lg p-4 mb-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase mr-2" :class="m.maintenance_type === 'reparacion' ? 'bg-red-100 text-red-700' : m.maintenance_type === 'reemplazo' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700'">{{ m.maintenance_type }}</span>
                                <span v-if="m.component" class="text-label-sm text-outline">{{ m.component }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-label-sm text-outline">{{ new Date(m.performed_at).toLocaleDateString('es') }}</span>
                                <button @click="deleteMaint(m.id)" class="text-error hover:underline text-label-sm">Eliminar</button>
                            </div>
                        </div>
                        <p class="mt-2 text-body-sm text-on-surface dark:text-gray-200">{{ m.description }}</p>
                        <div class="mt-1 flex gap-4 text-label-sm text-outline">
                            <span v-if="m.cost">Costo: S/ {{ m.cost }}</span>
                            <span v-if="m.performed_by">Por: {{ m.performed_by }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Assign -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-5">
                    <h3 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-3">Asignado a</h3>
                    <div v-if="asset.assigned_person" class="mb-3">
                        <p class="text-body-sm font-semibold text-on-surface dark:text-gray-100">{{ asset.assigned_person.first_name }} {{ asset.assigned_person.last_name }}</p>
                        <p v-if="asset.assigned_person.email" class="text-body-sm text-outline">{{ asset.assigned_person.email }}</p>
                        <div v-if="asset.assigned_person.company || asset.assigned_person.department" class="mt-2 pt-2 border-t border-border-subtle dark:border-gray-600">
                            <p v-if="asset.assigned_person.company" class="text-label-sm text-outline">{{ asset.assigned_person.company.name }}</p>
                            <p v-if="asset.assigned_person.department" class="text-label-sm text-outline">{{ asset.assigned_person.department.name }}</p>
                        </div>
                    </div>
                    <p v-else class="text-body-sm text-outline mb-3 italic">Sin asignar</p>
                    <button @click="personId = asset.assigned_person?.id ?? ''; showAssignModal = true" class="w-full px-3 py-2 bg-deep-navy text-white rounded-lg text-label-sm hover:bg-primary transition-all">{{ asset.assigned_person ? 'Reasignar' : 'Asignar' }}</button>
                </div>

                <!-- Parent -->
                <div v-if="asset.parent" class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-5">
                    <h3 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-3">Equipo principal</h3>
                    <Link :href="route('admin.assets.show', asset.parent.id)" class="text-primary hover:underline text-body-sm">{{ asset.parent.asset_tag }} — {{ asset.parent.name }}</Link>
                </div>

                <!-- Licenses -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-label-sm font-semibold text-outline uppercase tracking-wider">Licencias</h3>
                        <button @click="selLicenseId = ''; showLicenseModal = true" class="px-3 py-1.5 bg-deep-navy text-white rounded-lg text-label-sm hover:bg-primary transition-all">+ Asignar</button>
                    </div>
                    <div v-if="!asset.software_licenses?.length" class="text-body-sm text-outline">Sin licencias asignadas.</div>
                    <div v-for="l in asset.software_licenses" :key="l.id" class="flex items-center justify-between py-2 border-b border-border-subtle last:border-0">
                        <span class="text-body-sm text-on-surface dark:text-gray-200">{{ l.name }}</span>
                        <span class="text-[10px] text-outline">{{ l.pivot.installed_at ? new Date(l.pivot.installed_at).toLocaleDateString('es') : '—' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Modal -->
        <Teleport to="body">
            <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showAssignModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-md mx-4">
                    <h3 class="text-headline-sm font-semibold mb-4">Asignar Activo</h3>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold">Usuario</label>
                        <select v-model="personId" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">Sin asignar</option>
                            <option v-for="p in people" :key="p.id" :value="p.id">{{ p.first_name }} {{ p.last_name }}{{ p.email ? ' (' + p.email + ')' : '' }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showAssignModal = false" class="px-4 py-2 border border-border-subtle rounded-lg text-label-sm text-outline hover:bg-surface-container-low">Cancelar</button>
                        <button @click="saveAssign" class="px-6 py-2.5 bg-deep-navy text-white rounded-lg text-label-sm hover:shadow-md">Guardar</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Maintenance Modal -->
        <Teleport to="body">
            <div v-if="showMaintModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showMaintModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-lg mx-4">
                    <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-4">Registrar Mantenimiento</h3>
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Fecha</label>
                            <DatePicker v-model="editingMaint.performed_at" placeholder="Seleccionar fecha" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Tipo</label>
                            <select v-model="editingMaint.maintenance_type" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="reparacion">Reparación</option>
                                <option value="reemplazo">Reemplazo</option>
                                <option value="preventivo">Mantenimiento preventivo</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Componente</label>
                            <input v-model="editingMaint.component" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" placeholder="Ej: Fuente, batería" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Descripci&oacute;n</label>
                            <textarea v-model="editingMaint.description" rows="2" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"></textarea>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Costo (S/)</label>
                            <input v-model="editingMaint.cost" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider">Realizado por</label>
                            <input v-model="editingMaint.performed_by" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showMaintModal = false" class="px-4 py-2 border border-border-subtle rounded-lg text-label-sm text-outline hover:bg-surface-container-low">Cancelar</button>
                        <button @click="saveMaint" class="px-6 py-2.5 bg-deep-navy text-white rounded-lg text-label-sm hover:shadow-md">Guardar</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- License assign Modal -->
        <Teleport to="body">
            <div v-if="showLicenseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showLicenseModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-lg mx-4">
                    <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-4">Asignar Licencia</h3>
                    <div class="space-y-2">
                        <label class="block text-xs text-outline dark:text-gray-400 uppercase tracking-wider mb-1">Licencia</label>
                        <SearchSelect
                            v-model="selLicenseId"
                            :options="licenseOptions"
                            placeholder="Buscar licencia..."
                            search-placeholder="Escribe para buscar..."
                        />
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showLicenseModal = false" class="px-4 py-2 border border-border-subtle rounded-lg text-label-sm text-outline hover:bg-surface-container-low">Cancelar</button>
                        <button @click="saveLicense" class="px-6 py-2.5 bg-deep-navy text-white rounded-lg text-label-sm hover:shadow-md">Asignar</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
