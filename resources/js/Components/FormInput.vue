<script setup lang="ts">
import { onMounted, ref } from 'vue';
import InputError from '@/Components/InputError.vue';

const props = withDefaults(defineProps<{
    label?: string;
    type?: string;
    placeholder?: string;
    autocomplete?: string;
    autofocus?: boolean;
    error?: string;
    icon?: string;
    badge?: string;
}>(), {
    type: 'text',
});

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (props.autofocus) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <div class="space-y-2">
        <div v-if="badge" class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-600 text-[10px] font-bold uppercase mb-2 tracking-wider">
            {{ badge }}
        </div>
        <label v-if="label" class="block text-sm font-semibold text-gray-700" :for="label">
            {{ label }}
        </label>
        <input
            :id="label"
            :type="type"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            v-model="model"
            ref="input"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary focus:border-2 transition-all outline-none"
        />
        <InputError v-if="error" class="mt-1" :message="error" />
    </div>
</template>
