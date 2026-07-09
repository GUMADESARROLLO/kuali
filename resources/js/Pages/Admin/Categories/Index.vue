<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

interface Subcategory { id: number; name: string; slug: string; is_active: boolean; sort_order: number }
interface Category {
    id: number; name: string; slug: string; description: string | null;
    is_active: boolean; sort_order: number; subcategories: Subcategory[];
}

const props = defineProps<{ categories: Category[] }>();

const expanded = ref<Record<number, boolean>>({});
const toggleExpand = (id: number) => { expanded.value[id] = !expanded.value[id]; };

// Drag & drop
const dragCatId = ref<number | null>(null);
const dragSubId = ref<number | null>(null);
const dropTarget = ref<string | null>(null);

const onCatDragStart = (e: DragEvent, catId: number) => {
    dragCatId.value = catId;
    if (e.dataTransfer) e.dataTransfer.effectAllowed = 'move';
};

const onSubDragStart = (e: DragEvent, subId: number, _catId: number) => {
    dragSubId.value = subId;
    if (e.dataTransfer) e.dataTransfer.effectAllowed = 'move';
};

const onDragOver = (e: DragEvent, target: string | null) => {
    if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
    dropTarget.value = target;
};

const onDragEnd = () => {
    dragCatId.value = null;
    dragSubId.value = null;
    dropTarget.value = null;
};

const onCatDrop = (e: DragEvent, targetCatId: number) => {
    if (dragCatId.value && dragCatId.value !== targetCatId) {
        const ids = props.categories.map(c => c.id);
        const fromIdx = ids.indexOf(dragCatId.value);
        const toIdx = ids.indexOf(targetCatId);
        if (fromIdx > -1 && toIdx > -1) {
            ids.splice(fromIdx, 1);
            ids.splice(toIdx, 0, dragCatId.value);
            router.post(route('admin.categories.reorder'), { ids }, { preserveScroll: true, preserveState: false });
        }
    }
    onDragEnd();
};

const onSubDrop = (e: DragEvent, targetSubId: number, catId: number) => {
    const cat = props.categories.find(c => c.id === catId);
    if (!cat || !dragSubId.value) return;
    const subIds = cat.subcategories.map(s => s.id);
    const fromIdx = subIds.indexOf(dragSubId.value);
    const toIdx = subIds.indexOf(targetSubId);
    if (fromIdx > -1 && toIdx > -1) {
        subIds.splice(fromIdx, 1);
        subIds.splice(toIdx, 0, dragSubId.value);
        router.post(route('admin.subcategories.reorder'), { ids: subIds, category_id: catId }, { preserveScroll: true, preserveState: false });
    }
    onDragEnd();
};

// --- Category modal ---
const showCatModal = ref(false);
const editingCat = ref<{ id?: number; name: string; description: string; is_active: boolean; sort_order: number }>({
    name: '', description: '', is_active: true, sort_order: 0,
});
const isEditingCat = ref(false);

const openCreateCat = () => {
    editingCat.value = { name: '', description: '', is_active: true, sort_order: 0 };
    isEditingCat.value = false;
    showCatModal.value = true;
};

const openEditCat = (cat: Category) => {
    editingCat.value = { id: cat.id, name: cat.name, description: cat.description ?? '', is_active: cat.is_active, sort_order: cat.sort_order };
    isEditingCat.value = true;
    showCatModal.value = true;
};

const saveCat = () => {
    if (!editingCat.value.name.trim()) return;
    if (isEditingCat.value) {
        router.put(route('admin.categories.update', editingCat.value.id!), {
            name: editingCat.value.name,
            description: editingCat.value.description,
            is_active: editingCat.value.is_active,
            sort_order: editingCat.value.sort_order,
        }, { preserveScroll: true, preserveState: false, onSuccess: () => { showCatModal.value = false; } });
    } else {
        router.post(route('admin.categories.store'), {
            name: editingCat.value.name,
            description: editingCat.value.description,
            is_active: editingCat.value.is_active,
            sort_order: editingCat.value.sort_order,
        }, { preserveScroll: true, preserveState: false, onSuccess: () => { showCatModal.value = false; } });
    }
};

const destroyCategory = async (id: number, name: string) => {
    if (!await (window as any).confirmDialog('Eliminar', `¿Eliminar la categoría "${name}"?`)) return;
    router.delete(route('admin.categories.destroy', id));
};

// --- Subcategory modal ---
const showSubModal = ref(false);
const editingSub = ref<{ id?: number; name: string; is_active: boolean; sort_order: number; category_id?: number }>({ name: '', is_active: true, sort_order: 0 });
const isEditingSub = ref(false);

const openCreateSub = (catId: number) => {
    editingSub.value = { name: '', is_active: true, sort_order: 0, category_id: catId };
    isEditingSub.value = false;
    showSubModal.value = true;
};

const openEditSub = (sub: Subcategory, catId: number) => {
    editingSub.value = { id: sub.id, name: sub.name, is_active: sub.is_active, sort_order: sub.sort_order, category_id: catId };
    isEditingSub.value = true;
    showSubModal.value = true;
};

const saveSub = () => {
    if (!editingSub.value.name.trim()) return;
    if (isEditingSub.value) {
        router.put(route('admin.subcategories.update', editingSub.value.id!), {
            name: editingSub.value.name,
            is_active: editingSub.value.is_active,
            sort_order: editingSub.value.sort_order,
        }, { preserveScroll: true, preserveState: false, onSuccess: () => { showSubModal.value = false; } });
    } else {
        router.post(route('admin.categories.subcategories.store', editingSub.value.category_id!), {
            name: editingSub.value.name,
            is_active: editingSub.value.is_active,
            sort_order: editingSub.value.sort_order,
        }, { preserveScroll: true, preserveState: false, onSuccess: () => { showSubModal.value = false; } });
    }
};

const destroySub = async (id: number, name: string) => {
    if (!await (window as any).confirmDialog('Eliminar', `¿Eliminar la subcategoría "${name}"?`)) return;
    router.delete(route('admin.subcategories.destroy', id));
};
</script>

<template>
    <AppLayout active-nav="categories">
        <Head title="Categorías &amp; Subcategorías" />

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div>
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Categor&iacute;as &amp; Subcategor&iacute;as</h2>
                <p class="text-body-md text-outline dark:text-gray-400">Gestiona la clasificaci&oacute;n jer&aacute;rquica de tickets.</p>
            </div>
            <button @click="openCreateCat" class="bg-deep-navy hover:bg-primary text-white px-6 py-2.5 rounded-lg text-label-md flex items-center gap-2 transition-all shadow-sm hover:shadow-md active:scale-95">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Nueva Categor&iacute;a
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="grid grid-cols-12 bg-surface-container-low dark:bg-gray-700 px-6 py-3 border-b border-border-subtle dark:border-gray-700 text-[11px] font-bold uppercase tracking-wider text-outline dark:text-gray-400">
                <div class="col-span-5">Nombre</div>
                <div class="col-span-2">Estado</div>
                <div class="col-span-1 text-center text-[10px]">Sub</div>
                <div class="col-span-4 text-right pr-4">Acciones</div>
            </div>

            <div v-if="!categories.length" class="px-6 py-12 text-center text-outline text-body-sm">Sin categor&iacute;as.</div>

            <div
                v-for="cat in categories"
                :key="cat.id"
                class="divide-y divide-border-subtle dark:divide-gray-700 transition-all duration-200"
                draggable="true"
                @dragstart="onCatDragStart($event, cat.id)"
                @dragover.prevent="onDragOver($event, 'cat-' + cat.id)"
                @dragleave="dropTarget = null"
                @drop.prevent="onCatDrop($event, cat.id)"
                @dragend="onDragEnd"
                :class="{ 'opacity-50 scale-[1.02]': dragCatId === cat.id, 'border-t-2 border-deep-navy dark:border-blue-400': dropTarget === 'cat-' + cat.id }"
            >
                <div class="grid grid-cols-12 px-6 py-4 items-center hover:bg-surface-container-lowest dark:hover:bg-gray-700/50 transition-colors cursor-pointer" @click="toggleExpand(cat.id)">
                    <div class="col-span-5 flex items-center gap-3">
                        <span class="material-symbols-outlined text-outline dark:text-gray-500 cursor-grab active:cursor-grabbing text-[18px]">drag_indicator</span>
                        <span class="material-symbols-outlined text-deep-navy dark:text-blue-400 transition-transform" :class="{ '-rotate-90': !expanded[cat.id] }">keyboard_arrow_down</span>
                        <span class="material-symbols-outlined text-deep-navy/40 dark:text-blue-400/40">category</span>
                        <span class="text-[16px] text-deep-navy dark:text-blue-300 font-semibold">{{ cat.name }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium" :class="cat.is_active ? 'bg-secondary-container/70 text-on-secondary-container' : 'bg-surface-variant text-on-surface-variant'">
                            {{ cat.is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div class="col-span-1 text-center text-body-sm text-outline dark:text-gray-400">{{ cat.subcategories.length }}</div>
                    <div class="col-span-4 flex justify-end gap-2">
                        <button @click.stop="openCreateSub(cat.id)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-deep-navy dark:hover:text-blue-300 hover:bg-surface-container dark:hover:bg-gray-700 rounded transition-all" title="Añadir subcategoría">
                            <span class="material-symbols-outlined text-[18px]">add_circle</span>
                        </button>
                        <button @click.stop="openEditCat(cat)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-deep-navy dark:hover:text-blue-300 hover:bg-surface-container dark:hover:bg-gray-700 rounded transition-all" title="Editar">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                        </button>
                        <button @click.stop="destroyCategory(cat.id, cat.name)" class="p-1.5 text-on-surface-variant dark:text-gray-300 hover:text-error hover:bg-error-container/20 dark:hover:bg-red-900/30 rounded transition-all" title="Eliminar">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </div>
                </div>

                <!-- Subcategories -->
                <div v-if="expanded[cat.id] && cat.subcategories.length" class="bg-surface-gray/50 dark:bg-gray-800/50 border-y border-border-subtle/50 dark:border-gray-700/50">
                    <div
                        v-for="sub in cat.subcategories"
                        :key="sub.id"
                        draggable="true"
                        @dragstart="onSubDragStart($event, sub.id, cat.id)"
                        @dragover.prevent="onDragOver($event, 'sub-' + sub.id)"
                        @dragleave="dropTarget = null"
                        @drop.prevent="onSubDrop($event, sub.id, cat.id)"
                        @dragend="onDragEnd"
                        :class="{ 'opacity-50 scale-[1.02]': dragSubId === sub.id, 'border-l-2 border-deep-navy dark:border-blue-400': dropTarget === 'sub-' + sub.id }"
                        class="grid grid-cols-12 px-6 py-3 items-center pl-12 border-b border-border-subtle/30 dark:border-gray-700/30 last:border-b-0 text-on-surface-variant dark:text-gray-300 transition-all duration-200"
                    >
                        <div class="col-span-5 flex items-center gap-2">
                            <span class="material-symbols-outlined text-outline dark:text-gray-500 cursor-grab active:cursor-grabbing text-[16px]">drag_indicator</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-outline-variant dark:bg-gray-500 shrink-0" />
                            <span class="text-body-sm">{{ sub.name }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase" :class="sub.is_active ? 'bg-secondary-container/50 text-on-secondary-container' : 'bg-surface-variant text-on-surface-variant'">
                                {{ sub.is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <div class="col-span-1" />
                        <div class="col-span-4 flex justify-end gap-2">
                            <button @click.stop="openEditSub(sub, cat.id)" class="p-1 text-on-surface-variant dark:text-gray-300 hover:text-deep-navy dark:hover:text-blue-300" title="Editar">
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                            </button>
                            <button @click.stop="destroySub(sub.id, sub.name)" class="p-1 text-on-surface-variant dark:text-gray-300 hover:text-error" title="Eliminar">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else-if="!cat.subcategories.length" class="px-6 py-3 pl-12 text-label-sm text-outline italic bg-surface-gray/50 dark:bg-gray-800/50 border-y border-border-subtle/50 dark:border-gray-700/50">
                    Sin subcategor&iacute;as
                </div>
            </div>
        </div>

        <!-- Category Modal -->
        <Teleport to="body">
            <div v-if="showCatModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showCatModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-lg mx-4">
                    <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-4">{{ isEditingCat ? 'Editar' : 'Nueva' }} Categor&iacute;a</h3>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nombre</label>
                            <input v-model="editingCat.name" @keyup.enter="saveCat"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                placeholder="Nombre de la categoría" />
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Descripci&oacute;n</label>
                            <textarea v-model="editingCat.description" rows="2"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none"
                                placeholder="Descripción opcional" />
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Estado</label>
                            <select v-model="editingCat.is_active"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option :value="true">Activo</option>
                                <option :value="false">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showCatModal = false" class="px-4 py-2 border border-border-subtle rounded-lg text-label-sm text-outline hover:bg-surface-container-low transition-all">Cancelar</button>
                        <button @click="saveCat" class="px-4 py-2 bg-deep-navy text-white rounded-lg text-label-sm hover:shadow-md transition-all">{{ isEditingCat ? 'Actualizar' : 'Crear' }}</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Subcategory Modal -->
        <Teleport to="body">
            <div v-if="showSubModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showSubModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-md mx-4">
                    <h3 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-4">{{ isEditingSub ? 'Editar' : 'Nueva' }} Subcategor&iacute;a</h3>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nombre</label>
                            <input v-model="editingSub.name" @keyup.enter="saveSub"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                placeholder="Nombre de la subcategoría" />
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Estado</label>
                            <select v-model="editingSub.is_active"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option :value="true">Activo</option>
                                <option :value="false">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showSubModal = false" class="px-4 py-2 border border-border-subtle rounded-lg text-label-sm text-outline hover:bg-surface-container-low transition-all">Cancelar</button>
                        <button @click="saveSub" class="px-4 py-2 bg-deep-navy text-white rounded-lg text-label-sm hover:shadow-md transition-all">{{ isEditingSub ? 'Actualizar' : 'Crear' }}</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
