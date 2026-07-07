<script setup lang="ts">
import { ref, computed } from 'vue';

const files = defineModel<File[]>({ required: true });

const dragOver = ref(false);
const inputRef = ref<HTMLInputElement | null>(null);

const MAX_SIZE = 10 * 1024 * 1024; // 10MB
const ALLOWED = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/zip'];

const error = ref('');

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
        error.value = 'M&aacute;ximo 5 archivos.';
        return;
    }
    files.value = [...files.value, ...valid];
};

const removeFile = (index: number) => {
    const arr = [...files.value];
    arr.splice(index, 1);
    files.value = arr;
};

const formatSize = (bytes: number): string => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};
</script>

<template>
    <div class="space-y-3">
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Adjuntos</label>

        <div
            class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-colors"
            :class="dragOver
                ? 'border-primary bg-primary/5'
                : 'border-gray-300 dark:border-gray-600 hover:border-primary dark:hover:border-primary'"
            @drop.prevent="dragOver = false; addFiles(($event as DragEvent).dataTransfer?.files)"
            @dragover.prevent="dragOver = true"
            @dragleave="dragOver = false"
            @click="inputRef?.click()"
        >
            <input
                ref="inputRef"
                type="file"
                multiple
                :accept="ALLOWED.join(',')"
                class="hidden"
                @change="addFiles(($event.target as HTMLInputElement).files)"
            />
            <span class="material-symbols-outlined text-4xl text-outline mb-2">cloud_upload</span>
            <p class="text-body-sm text-outline dark:text-gray-300">
                Arrastra archivos aqu&iacute; o haz clic para seleccionar
            </p>
            <p class="text-label-sm text-outline mt-1">PDF, PNG, JPG, DOCX, XLSX, ZIP — m&aacute;x 10MB c/u</p>
        </div>

        <p v-if="error" class="text-error text-label-sm">{{ error }}</p>

        <ul v-if="fileList.length" class="space-y-2">
            <li
                v-for="(f, i) in fileList"
                :key="i"
                class="flex items-center justify-between px-3 py-2 bg-surface-container-low dark:bg-gray-700 rounded-lg"
            >
                <span class="text-body-sm text-on-surface dark:text-gray-200 truncate max-w-xs">{{ f.name }}</span>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="text-label-sm text-outline">{{ formatSize(f.size) }}</span>
                    <button @click.prevent="removeFile(i)" class="text-error hover:opacity-80">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            </li>
        </ul>
    </div>
</template>
