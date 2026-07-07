<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps<{
    categories: { id: number; name: string }[];
}>();

const emit = defineEmits<{
    change: [categoryId: number, subcategoryId: number];
}>();

const selectedCategory = defineModel<number | null>('category', { required: true });
const selectedSubcategory = defineModel<number | null>('subcategory', { required: true });

const subcategories = ref<{ id: number; name: string }[]>([]);
const loading = ref(false);

watch(selectedCategory, async (catId) => {
    selectedSubcategory.value = null;
    subcategories.value = [];
    if (!catId) return;
    loading.value = true;
    try {
        const { data } = await axios.get(`/api/subcategorias/${catId}`);
        subcategories.value = data;
    } catch {
        subcategories.value = [];
    } finally {
        loading.value = false;
    }
});

watch([selectedCategory, selectedSubcategory], () => {
    if (selectedCategory.value && selectedSubcategory.value) {
        emit('change', selectedCategory.value, selectedSubcategory.value);
    }
});
</script>

<template>
    <div class="grid grid-cols-2 gap-4">
        <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Categor&iacute;a</label>
            <select
                v-model="selectedCategory"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
            >
                <option :value="null" disabled>Seleccionar categor&iacute;a</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
        </div>
        <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Subcategor&iacute;a</label>
            <select
                v-model="selectedSubcategory"
                :disabled="!selectedCategory || loading"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 disabled:opacity-50"
            >
                <option :value="null" disabled>
                    {{ loading ? 'Cargando...' : 'Seleccionar subcategor&iacute;a' }}
                </option>
                <option v-for="s in subcategories" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
        </div>
    </div>
</template>
