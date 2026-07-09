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

interface NavSection {
    title?: string;
    items: NavItem[];
}

const auth = usePage().props.auth as any;
const roles = auth?.roles as string[] | undefined;
const isAdmin = computed(() => roles?.includes('admin_it') ?? roles?.includes('agente_it') ?? false);

const navSections = computed<NavSection[]>(() => {
    if (isAdmin.value) {
        return [
            {
                title: undefined,
                items: [
                    { key: 'dashboard', label: 'Dashboard', icon: 'dashboard', route: 'admin.dashboard' },
                    { key: 'tickets', label: 'All Tickets', icon: 'confirmation_number', route: 'admin.tickets.index' },
                    { key: 'reports', label: 'Reportes', icon: 'monitoring', route: 'admin.reports.index' },
                    { key: 'person-report', label: 'Activos por persona', icon: 'person_check', route: 'admin.reports.assets-by-person' },
                ],
            },
            {
                title: 'Configuración',
                items: [
                    { key: 'users', label: 'Usuarios', icon: 'group', route: 'admin.users.index' },
                    { key: 'categories', label: 'Categorías', icon: 'category', route: 'admin.categories.index' },
                    { key: 'calendars', label: 'Calendarios', icon: 'calendar_month', route: 'admin.calendars.index' },
                    { key: 'sla', label: 'Reglas SLA', icon: 'timer', route: 'admin.sla-rules.index' },
                ],
            },
            {
                title: 'Inventario',
                items: [
                    { key: 'companies', label: 'Empresas', icon: 'business', route: 'admin.companies.index' },
                    { key: 'departments', label: 'Departamentos', icon: 'business', route: 'admin.departments.index' },
                    { key: 'persons', label: 'Personas', icon: 'person', route: 'admin.persons.index' },
                    { key: 'assets', label: 'Activos', icon: 'devices', route: 'admin.assets.index' },
                    { key: 'maintenance', label: 'Mantenimiento', icon: 'build', route: 'admin.maintenance.index' },
                    { key: 'licenses', label: 'Licencias', icon: 'verified', route: 'admin.licenses.index' },
                ],
            },
        ];
    }
    return [
        {
            title: undefined,
            items: [
                { key: 'dashboard', label: 'Mis Tickets', icon: 'confirmation_number', route: 'user.dashboard' },
            ],
        },
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

        <nav class="flex-1 overflow-y-auto scrollbar-none">
            <template v-for="(section, si) in navSections" :key="si">
                <div v-if="section.title" class="overflow-hidden transition-all duration-300" :class="collapsed ? 'w-0 opacity-0 hidden' : 'hidden lg:block px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-outline dark:text-gray-500'">
                    {{ section.title }}
                </div>
                <div :class="si > 0 ? 'mt-0' : ''">
                    <a
                        v-for="item in section.items"
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
                </div>
            </template>
        </nav>
    </aside>
</template>
