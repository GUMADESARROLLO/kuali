<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = withDefaults(defineProps<{
    activeNav?: string;
    collapsed?: boolean;
}>(), {
    activeNav: 'tickets',
    collapsed: false,
});

interface NavItem {
    key: string;
    label: string;
    icon: string;
    route: string;
}

const auth = usePage().props.auth as any;
const roles = auth?.roles as string[] | undefined;
const isAdmin = computed(() => roles?.includes('admin_it') ?? roles?.includes('agente_it') ?? false);

const navItems = computed<NavItem[]>(() => {
    if (isAdmin.value) {
        return [
            { key: 'dashboard', label: 'Dashboard', icon: 'dashboard', route: 'admin.dashboard' },
            { key: 'tickets', label: 'All Tickets', icon: 'confirmation_number', route: 'admin.tickets.index' },
            { key: 'categories', label: 'Categorías', icon: 'category', route: 'admin.categories.index' },
            { key: 'reports', label: 'Reportes', icon: 'monitoring', route: 'admin.reports.index' },
            { key: 'departments', label: 'Departamentos', icon: 'business', route: 'admin.departments.index' },
            { key: 'users', label: 'Usuarios', icon: 'group', route: 'admin.users.index' },
        ];
    }
    return [
        { key: 'dashboard', label: 'Mis Tickets', icon: 'confirmation_number', route: 'user.dashboard' },
    ];
});
</script>

<template>
    <aside
        class="fixed left-0 top-0 h-screen bg-white dark:bg-gray-900 border-r border-border-subtle dark:border-gray-700 flex flex-col py-6 px-4 z-20 transition-all duration-300 overflow-hidden"
        :class="collapsed ? 'w-20' : 'w-20 lg:w-64'"
    >
        <div class="mb-10 flex items-center gap-3 px-2">
            <div class="w-10 h-10 bg-deep-navy rounded flex items-center justify-center text-white shrink-0">
                <span class="material-symbols-outlined text-white" style="font-variation-settings:'FILL' 1;">confirmation_number</span>
            </div>
            <div class="overflow-hidden transition-all duration-300" :class="collapsed ? 'w-0 opacity-0' : 'hidden lg:block'">
                <h1 class="text-headline-sm font-bold text-deep-navy dark:text-blue-300 truncate">Kuali</h1>
                <p class="text-label-sm text-outline dark:text-gray-400">IT Support</p>
            </div>
        </div>

        <nav class="flex-1 space-y-2">
            <a
                v-for="item in navItems"
                :key="item.key"
                :href="route(item.route)"
                :title="collapsed ? item.label : undefined"
                :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200 cursor-pointer justify-center lg:justify-start',
                    activeNav === item.key
                        ? 'bg-secondary-container dark:bg-green-900 text-on-secondary-container dark:text-green-200 font-bold'
                        : 'text-on-surface-variant dark:text-gray-300 hover:bg-surface-container-low dark:hover:bg-gray-800'
                ]"
            >
                <span class="material-symbols-outlined">{{ item.icon }}</span>
                <span class="overflow-hidden transition-all duration-300" :class="collapsed ? 'w-0 opacity-0 hidden' : 'hidden lg:block text-label-md ml-3'">{{ item.label }}</span>
            </a>
        </nav>
    </aside>
</template>
