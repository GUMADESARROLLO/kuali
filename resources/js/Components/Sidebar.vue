<script setup lang="ts">
const props = withDefaults(defineProps<{
    activeNav?: string;
}>(), {
    activeNav: 'tickets',
});

interface NavItem {
    key: string;
    label: string;
    icon: string;
    route: string;
}

const navItems: NavItem[] = [
    { key: 'dashboard', label: 'Dashboard', icon: 'dashboard', route: 'admin.dashboard' },
    { key: 'tickets', label: 'All Tickets', icon: 'confirmation_number', route: 'admin.tickets.index' },
];
</script>

<template>
    <aside class="w-20 lg:w-64 h-screen fixed left-0 top-0 bg-white border-r border-border-subtle flex flex-col py-6 px-4 z-20">
        <div class="mb-10 flex items-center gap-3 px-2">
            <div class="w-10 h-10 bg-deep-navy rounded flex items-center justify-center text-white shrink-0">
                <span class="material-symbols-outlined text-white" style="font-variation-settings:'FILL' 1;">confirmation_number</span>
            </div>
            <div class="hidden lg:block overflow-hidden">
                <h1 class="text-headline-sm font-bold text-deep-navy truncate">Kuali</h1>
                <p class="text-label-sm text-outline">IT Support</p>
            </div>
        </div>

        <nav class="flex-1 space-y-2">
            <a
                v-for="item in navItems"
                :key="item.key"
                :href="route(item.route)"
                :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200 cursor-pointer',
                    activeNav === item.key
                        ? 'bg-secondary-container text-on-secondary-container font-bold scale-95'
                        : 'text-on-surface-variant hover:bg-surface-container-low'
                ]"
            >
                <span class="material-symbols-outlined">{{ item.icon }}</span>
                <span class="hidden lg:block text-label-md">{{ item.label }}</span>
            </a>
        </nav>
    </aside>
</template>
