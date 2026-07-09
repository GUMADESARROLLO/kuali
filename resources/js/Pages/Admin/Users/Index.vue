<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Dept { id: number; name: string }
interface UserRow {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    is_active: boolean;
    department_id: number | null;
    department: string | null;
    roles: string;
    role: string | null;
}

const props = defineProps<{ users: UserRow[]; departments: Dept[]; roles: string[] }>();

interface UserForm {
    id?: number; name: string; email: string; password?: string;
    department_id: number | null; phone: string; is_active: boolean; role: string;
}

const showModal = ref(false);
const isEditing = ref(false);
const editingUser = ref<UserForm>({ name: '', email: '', password: '', department_id: null, phone: '', is_active: true, role: 'usuario' });
const saving = ref(false);
const openCreate = () => {
    editingUser.value = { name: '', email: '', password: '', department_id: null, phone: '', is_active: true, role: 'usuario' };
    isEditing.value = false;
    showModal.value = true;
};

const openEdit = (u: UserRow) => {
    editingUser.value = { id: u.id, name: u.name, email: u.email, password: '', department_id: u.department_id, phone: u.phone ?? '', is_active: u.is_active, role: u.role ?? 'usuario' };
    isEditing.value = true;
    showModal.value = true;
};

const save = () => {
    saving.value = true;
    const e = editingUser.value;
    const payload: Record<string, unknown> = {
        name: e.name, email: e.email, department_id: e.department_id,
        phone: e.phone, is_active: e.is_active, role: e.role,
    };
    if (!isEditing.value || e.password) payload.password = e.password;
    const options = {
        preserveScroll: true as const, preserveState: false as const,
        onSuccess: () => { showModal.value = false; saving.value = false; },
        onError: () => { saving.value = false; },
        onFinish: () => { saving.value = false; },
    };
    if (isEditing.value) {
        router.put(route('admin.users.update', e.id!), payload as never, options);
    } else {
        router.post(route('admin.users.store'), payload as never, options);
    }
};

const destroy = async (id: number) => {
    if (!await (window as any).confirmDialog('Eliminar', '¿Eliminar este usuario?')) return;
    router.delete(route('admin.users.destroy', id));
};

const roleLabel = (role: string | null): string => {
    const map: Record<string, string> = { admin_it: 'Admin', agente_it: 'Agente', usuario: 'Usuario' };
    return map[role ?? ''] || role || '—';
};
</script>

<template>
    <AppLayout active-nav="users">
        <Head title="Usuarios" />

        <div class="flex items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Usuarios</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Gestiona los usuarios y sus roles.</p>
            </div>
            <button @click="openCreate" class="bg-deep-navy hover:bg-primary text-white px-5 py-2.5 rounded-lg text-label-md flex items-center gap-2">+ Nuevo</button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-gray-700 border-b border-border-subtle dark:border-gray-700">
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Email</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Depto</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Roles</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider">Activo</th>
                        <th class="px-6 py-3 text-label-sm uppercase text-outline dark:text-gray-400 font-bold tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-gray-700">
                    <tr v-for="u in users" :key="u.id" class="hover:bg-surface-container-lowest dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-body-sm text-on-surface dark:text-gray-100">{{ u.name }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ u.email }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ u.department || '—' }}</td>
                        <td class="px-6 py-3 text-body-sm text-outline dark:text-gray-400">{{ roleLabel(u.role) }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold" :class="u.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">{{ u.is_active ? 'Sí' : 'No' }}</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="openEdit(u)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-deep-navy dark:hover:text-blue-300 hover:bg-surface-container dark:hover:bg-gray-700 rounded transition-all" title="Editar">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button @click="destroy(u.id)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-error hover:bg-error-container/20 dark:hover:bg-red-900/30 rounded transition-all" title="Eliminar">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- User Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-4">{{ isEditing ? 'Editar' : 'Nuevo' }} Usuario</h3>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nombre</label>
                                <input v-model="editingUser.name"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Email</label>
                                <input v-model="editingUser.email" type="email"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                                Contraseña <span v-if="isEditing" class="text-outline font-normal">(dejar vacío para mantener)</span>
                            </label>
                            <input v-model="editingUser.password" type="password"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Departamento</label>
                                <select v-model="editingUser.department_id"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option :value="null">Sin departamento</option>
                                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Teléfono</label>
                                <input v-model="editingUser.phone"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Rol</label>
                                <select v-model="editingUser.role"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Estado</label>
                                <select v-model="editingUser.is_active"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option :value="true">Activo</option>
                                    <option :value="false">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showModal = false" class="px-4 py-2 border border-border-subtle rounded-lg text-label-sm text-outline hover:bg-surface-container-low transition-all">Cancelar</button>
                        <button @click="save" :disabled="saving" class="px-6 py-2.5 bg-deep-navy text-white rounded-lg text-label-sm disabled:opacity-50 hover:shadow-md transition-all">
                            {{ saving ? 'Guardando...' : isEditing ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
