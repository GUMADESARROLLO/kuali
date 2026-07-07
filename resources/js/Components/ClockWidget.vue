<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

const time = ref('');
const date = ref('');
let interval: ReturnType<typeof setInterval> | null = null;

const update = () => {
    const now = new Date();
    time.value = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: false });
    date.value = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
};

onMounted(() => {
    update();
    interval = setInterval(update, 1000);
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});
</script>

<template>
    <div class="text-center mt-6">
        <div class="text-5xl font-bold text-gray-900 leading-tight">{{ time }}</div>
        <div class="text-sm text-gray-500 mt-1">{{ date }}</div>
    </div>
</template>
