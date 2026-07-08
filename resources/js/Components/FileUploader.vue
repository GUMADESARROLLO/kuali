<script setup lang="ts">
import { ref, computed } from 'vue';

const files = defineModel<File[]>({ required: true });

const MAX_SIZE = 10 * 1024 * 1024;
const ALLOWED = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/zip'];

const error = ref('');
const fileInput = ref<HTMLInputElement | null>(null);

const fileList = computed(() => Array.from(files.value || []));

const addFiles = (newFiles: FileList | null | undefined) => {
    if (!newFiles) return;
    error.value = '';
    const valid: File[] = [];
    for (const f of Array.from(newFiles)) {
        if (!ALLOWED.includes(f.type)) {
            error.value = `"${f.name}" no es un tipo permitido.`;
            continue;
        }
        if (f.size > MAX_SIZE) {
            error.value = `"${f.name}" excede 10MB.`;
            continue;
        }
        valid.push(f);
    }
    if (files.value.length + valid.length > 5) {
        error.value = 'Máximo 5 archivos.';
        return;
    }
    files.value = [...files.value, ...valid];
};

const openFilePicker = () => fileInput.value?.click();
const onFileChange = (e: Event) => {
    addFiles((e.target as HTMLInputElement).files);
    if (fileInput.value) fileInput.value.value = '';
};

defineExpose({ addFiles, openFilePicker });

const iconFor = (name: string): string => {
    if (/\.pdf$/i.test(name)) return '📄';
    if (/\.(png|jpe?g|gif)$/i.test(name)) return '🖼️';
    if (/\.zip$/i.test(name)) return '🗜️';
    if (/\.(docx?|xlsx?)$/i.test(name)) return '📑';
    return '📎';
};

const formatSize = (bytes: number): string => {
    if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const removeFile = (index: number) => {
    const arr = [...files.value];
    arr.splice(index, 1);
    files.value = arr;
};
</script>

<template>
    <input ref="fileInput" type="file" multiple :accept="ALLOWED.join(',')" class="hidden" @change="onFileChange" />
    <p v-if="error" class="text-error text-label-sm mb-2">{{ error }}</p>
    <button type="button" @click="openFilePicker" class="flex items-center gap-1.5 px-3 py-1.5 border border-border-subtle dark:border-gray-500 rounded-lg text-label-sm text-outline dark:text-gray-300 hover:text-deep-navy dark:hover:text-white hover:border-deep-navy dark:hover:border-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7.586a2 2 0 00-2.828 0l-5.657 5.657a4 4 0 105.657 5.657l5.657-5.657A2 2 0 0015.172 7.586z"/></svg>
        Adjuntar archivos
    </button>
    <div v-if="fileList.length" class="flex flex-wrap gap-2 mt-2">
        <div
            v-for="(f, i) in fileList"
            :key="i"
            class="inline-flex items-center gap-2 bg-surface-container-low dark:bg-gray-700 border border-border-subtle dark:border-gray-600 rounded-lg px-3 py-1.5 text-label-sm text-on-surface-variant dark:text-gray-300"
        >
            <span class="text-sm leading-none">{{ iconFor(f.name) }}</span>
            <span class="max-w-[160px] truncate">{{ f.name }}</span>
            <span class="text-outline dark:text-gray-500 text-[11px]">{{ formatSize(f.size) }}</span>
            <button @click.prevent="removeFile(i)" class="text-outline dark:text-gray-400 hover:text-error dark:hover:text-red-400 transition-colors leading-none px-0.5">✕</button>
        </div>
    </div>
</template>
