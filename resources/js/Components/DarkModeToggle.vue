<script setup lang="ts">
import { ref, onMounted } from 'vue';

const isDark = ref(false);

onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark');
});

const toggle = () => {
    isDark.value = !isDark.value;
    const html = document.documentElement;
    if (isDark.value) {
        html.classList.add('dark');
        localStorage.setItem('dark', 'true');
    } else {
        html.classList.remove('dark');
        localStorage.setItem('dark', 'false');
    }
};
</script>

<template>
    <button
        @click="toggle"
        class="w-10 h-10 rounded-full hover:bg-surface-container hover:dark:bg-gray-700 transition-colors flex items-center justify-center"
        :title="isDark ? 'Modo claro' : 'Modo oscuro'"
    >
        <span v-if="isDark" class="material-symbols-outlined text-yellow-400">light_mode</span>
        <span v-else class="material-symbols-outlined text-outline">dark_mode</span>
    </button>
</template>
