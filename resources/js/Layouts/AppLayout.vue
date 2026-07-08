<script setup lang="ts">
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import TopBar from '@/Components/TopBar.vue';

defineProps<{
    activeNav?: string;
}>();

const collapsed = ref(localStorage.getItem('sidebar_collapsed') === 'true');

const toggle = () => {
    collapsed.value = !collapsed.value;
    localStorage.setItem('sidebar_collapsed', String(collapsed.value));
};
</script>

<template>
    <Sidebar :active-nav="activeNav" :collapsed="collapsed" />
    <TopBar :collapsed="collapsed" @toggle-sidebar="toggle" />

    <main
        class="pt-16 min-h-screen bg-surface-gray dark:bg-gray-800 transition-all duration-300 pl-20"
        :class="collapsed ? '' : 'lg:pl-64'"
    >
        <div class="p-4 md:p-8">
            <slot />
        </div>
    </main>
</template>
