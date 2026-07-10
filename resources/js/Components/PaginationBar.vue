<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    links: PaginationLink[];
    from: number;
    to: number;
    total: number;
}>();

const goTo = (url: string | null) => {
    if (!url) return;
    router.visit(url, { preserveState: true, preserveScroll: true });
};

const prevLink = computed(() => props.links[0] ?? null);
const nextLink = computed(() => props.links[props.links.length - 1] ?? null);
const pageLinks = computed(() => props.links.slice(1, -1));
</script>

<template>
    <div class="p-4 sm:p-6 bg-surface-container-lowest dark:bg-gray-800 flex items-center justify-between gap-2 border-t border-border-subtle dark:border-gray-700">
        <p class="text-body-sm text-outline dark:text-gray-400 hidden sm:block">
            {{ from }}–{{ to }} de {{ total }}
        </p>

        <!-- Desktop: full pagination -->
        <div class="hidden sm:flex items-center gap-1">
            <button
                v-for="(link, i) in links"
                :key="i"
                @click="goTo(link.url)"
                :disabled="!link.url || link.active"
                :class="[
                    'min-w-9 h-9 flex items-center justify-center rounded-lg text-label-sm transition-colors',
                    link.active
                        ? 'bg-deep-navy dark:bg-blue-600 text-white font-bold'
                        : link.url
                            ? 'border border-border-subtle dark:border-gray-600 text-outline dark:text-gray-300 hover:bg-surface-container-low dark:hover:bg-gray-700'
                            : 'text-outline/30 dark:text-gray-600 cursor-not-allowed'
                ]"
                v-html="link.label"
            />
        </div>

        <!-- Mobile: compact pagination -->
        <div class="flex sm:hidden items-center justify-between w-full">
            <button
                @click="goTo(prevLink.url)"
                :disabled="!prevLink?.url"
                class="flex items-center gap-1 px-3 py-1.5 border border-border-subtle dark:border-gray-600 rounded-lg text-label-sm disabled:opacity-30 text-outline dark:text-gray-300"
            >
                <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                Ant
            </button>
            <span class="text-body-sm text-outline dark:text-gray-400">{{ from }}–{{ to }} / {{ total }}</span>
            <button
                @click="goTo(nextLink.url)"
                :disabled="!nextLink?.url"
                class="flex items-center gap-1 px-3 py-1.5 border border-border-subtle dark:border-gray-600 rounded-lg text-label-sm disabled:opacity-30 text-outline dark:text-gray-300"
            >
                Sig
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            </button>
        </div>
    </div>
</template>
