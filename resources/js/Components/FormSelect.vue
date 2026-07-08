<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';

interface SelectOption {
    value: string | number;
    label: string;
    prefix?: string;
}

const props = withDefaults(defineProps<{
    options: SelectOption[];
    placeholder?: string;
    disabled?: boolean;
}>(), {
    placeholder: 'Seleccionar...',
    disabled: false,
});

const model = defineModel<string | number>({ required: true });

const open = ref(false);
const selectedLabel = computed(() => {
    const opt = props.options.find(o => o.value === model.value);
    return opt ? opt.label : props.placeholder;
});

const selectedPrefix = computed(() => {
    const opt = props.options.find(o => o.value === model.value);
    return opt?.prefix ?? null;
});

const toggle = () => { if (props.disabled) return; open.value = !open.value; };
const select = (val: string | number) => {
    model.value = val;
    open.value = false;
};

const onClickOutside = (e: MouseEvent) => {
    const el = (e.target as HTMLElement);
    if (!el.closest('.form-select-container')) open.value = false;
};

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));
</script>

<template>
    <div class="form-select-container relative">
        <button type="button" @click="toggle"
            class="w-full flex items-center gap-2 border border-border-subtle dark:border-gray-600 rounded-xl px-3 py-2.5 bg-white dark:bg-gray-800 hover:bg-surface-container-low dark:hover:bg-gray-700 transition-colors text-left"
            :class="{ 'cursor-pointer': !disabled, 'cursor-not-allowed opacity-50': disabled }"
            :disabled="disabled">
            <slot name="prefix">
                <span v-if="selectedPrefix" class="text-sm text-on-surface dark:text-gray-100">{{ selectedPrefix }}</span>
            </slot>
            <span class="text-sm text-on-surface dark:text-gray-100 flex-1 truncate">{{ selectedLabel }}</span>
            <svg class="w-4 h-4 text-outline dark:text-gray-400 shrink-0 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div v-if="open"
            class="absolute top-full mt-1 left-0 right-0 z-50 bg-white dark:bg-gray-800 border border-border-subtle dark:border-gray-700 rounded-xl shadow-lg overflow-hidden">
            <button
                v-for="opt in options"
                :key="String(opt.value)"
                type="button"
                @click="select(opt.value)"
                class="w-full flex items-center gap-2 px-3 py-2.5 text-sm text-left hover:bg-surface-container-low dark:hover:bg-gray-700 transition-colors"
                :class="model === opt.value ? 'bg-deep-navy/5 dark:bg-blue-600/20 font-semibold text-on-surface dark:text-white' : 'text-on-surface/80 dark:text-gray-300'"
            >
                <span v-if="opt.prefix" class="text-sm shrink-0">{{ opt.prefix }}</span>
                <span class="truncate">{{ opt.label }}</span>
                <svg v-if="model === opt.value" class="w-4 h-4 text-deep-navy dark:text-blue-400 ml-auto shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
            </button>
        </div>
    </div>
</template>
