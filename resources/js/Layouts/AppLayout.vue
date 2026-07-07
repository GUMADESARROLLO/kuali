<script setup lang="ts">
import Sidebar from '@/Components/Sidebar.vue';
import TopBar from '@/Components/TopBar.vue';

defineProps<{
    activeNav?: string;
}>();
</script>

<template>
    <Sidebar :active-nav="activeNav" />
    <TopBar />

    <main class="pt-16 pl-20 lg:pl-64 min-h-screen bg-surface-gray dark:bg-gray-800">
        <div class="p-4 md:p-8">
            <slot />
        </div>
    </main>
</template>
