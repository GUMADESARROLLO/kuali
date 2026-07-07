<script setup lang="ts">
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth?.user as { name: string; email: string } | undefined;

const showDropdown = ref(false);

const toggle = () => {
    showDropdown.value = !showDropdown.value;
};

const close = () => {
    showDropdown.value = false;
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <header class="h-16 fixed top-0 right-0 left-20 lg:left-64 z-10 bg-white border-b border-border-subtle px-4 md:px-8 flex justify-end items-center">
            <div class="relative">
                <div
                    class="flex items-center gap-3 cursor-pointer select-none rounded-lg px-2 py-1 hover:bg-surface-container-low transition-colors"
                    @click="toggle"
                >
                    <div class="text-right hidden md:block">
                        <p class="text-label-md text-on-surface leading-none">{{ user?.name ?? 'Support Agent' }}</p>
                        <p class="text-label-sm text-outline">{{ user?.email ?? '' }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-on-surface-variant border border-border-subtle">
                        <span class="material-symbols-outlined text-[20px]">person</span>
                    </div>
                    <span class="material-symbols-outlined text-outline text-[18px]" :class="{ 'rotate-180': showDropdown }">expand_more</span>
                </div>

                <div
                    v-if="showDropdown"
                    class="fixed inset-0 z-30"
                    @click="close"
                />

                <div
                    v-if="showDropdown"
                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl border border-border-subtle shadow-lg z-40 py-2"
                >
                    <div class="px-4 py-2 border-b border-border-subtle">
                        <p class="text-label-md text-on-surface">{{ user?.name }}</p>
                        <p class="text-label-sm text-outline">{{ user?.email }}</p>
                    </div>
                    <a
                        :href="route('profile.edit')"
                        class="flex items-center gap-3 px-4 py-2.5 text-body-sm text-on-surface hover:bg-surface-container-low transition-colors"
                    >
                        <span class="material-symbols-outlined text-[20px] text-outline">person</span>
                        Mi Perfil
                    </a>
                    <button
                        @click="logout"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-body-sm text-on-surface hover:bg-surface-container-low transition-colors text-left"
                    >
                        <span class="material-symbols-outlined text-[20px] text-outline">logout</span>
                        Cerrar sesi&oacute;n
                    </button>
                </div>
            </div>
    </header>
</template>
