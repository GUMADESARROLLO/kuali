<script setup lang="ts">
import { ref, computed } from 'vue';

interface Option { id: number | string; label: string; subtitle?: string }

const props = withDefaults(defineProps<{
    options: Option[];
    modelValue: number | string | null;
    placeholder?: string;
    searchPlaceholder?: string;
}>(), {
    placeholder: 'Seleccionar...',
    searchPlaceholder: 'Buscar...',
});

const emit = defineEmits<{ 'update:modelValue': [value: number | string | null] }>();

const open = ref(false);
const search = ref('');

const filtered = computed(() => {
    if (!search.value) return props.options;
    const q = search.value.toLowerCase();
    return props.options.filter(o =>
        o.label.toLowerCase().includes(q) ||
        (o.subtitle && o.subtitle.toLowerCase().includes(q))
    );
});

const selectedLabel = computed(() => {
    if (!props.modelValue) return '';
    const found = props.options.find(o => o.id === props.modelValue);
    return found ? found.label : '';
});

const select = (id: number | string) => {
    emit('update:modelValue', id);
    search.value = '';
    open.value = false;
};

const toggle = () => {
    open.value = !open.value;
    if (open.value) search.value = '';
};

const close = () => {
    open.value = false;
    search.value = '';
};
</script>

<template>
    <div class="relative">
        <button type="button" @click="toggle"
            class="w-full flex items-center gap-2 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-left transition-all"
            :class="open ? 'ring-2 ring-primary border-primary' : 'hover:border-gray-400 dark:hover:border-gray-500'">
            <span class="material-symbols-outlined text-outline text-[18px] shrink-0">person</span>
            <span class="flex-1 text-body-sm truncate" :class="modelValue ? 'text-gray-900 dark:text-gray-100' : 'text-outline'">
                {{ modelValue ? selectedLabel : placeholder }}
            </span>
            <span class="material-symbols-outlined text-outline text-[18px] transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
        </button>

        <div v-if="open" class="fixed inset-0 z-40" @click="close"></div>
        <div v-if="open"
            class="absolute top-full mt-1 left-0 right-0 z-50 bg-white dark:bg-gray-800 border border-border-subtle dark:border-gray-600 rounded-xl shadow-lg overflow-hidden">
            <div class="p-2 border-b border-border-subtle dark:border-gray-700">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input v-model="search"
                        class="w-full pl-10 pr-4 py-2 border border-border-subtle dark:border-gray-600 rounded-lg text-body-sm bg-surface-container-lowest dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-primary focus:border-primary outline-none"
                        :placeholder="searchPlaceholder" />
                </div>
            </div>
            <div class="max-h-60 overflow-y-auto">
                <div v-if="!filtered.length" class="px-4 py-8 text-center text-body-sm text-outline">
                    Sin resultados
                </div>
                <button v-for="opt in filtered" :key="String(opt.id)" type="button" @click="select(opt.id)"
                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-surface-container-low dark:hover:bg-gray-700/50 transition-colors border-b border-border-subtle/50 last:border-0"
                    :class="{ 'bg-primary/10 dark:bg-blue-900/20': modelValue === opt.id }">
                    <div class="w-8 h-8 rounded-full bg-deep-navy/10 dark:bg-blue-400/10 flex items-center justify-center text-deep-navy dark:text-blue-400 text-xs font-bold shrink-0">
                        {{ opt.label.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-body-sm font-medium text-on-surface dark:text-gray-100 truncate">{{ opt.label }}</p>
                        <p v-if="opt.subtitle" class="text-[11px] text-outline truncate">{{ opt.subtitle }}</p>
                    </div>
                    <span v-if="modelValue === opt.id" class="material-symbols-outlined text-primary text-[18px]">check</span>
                </button>
            </div>
        </div>
    </div>
</template>
