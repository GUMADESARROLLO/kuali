<script setup lang="ts">
import { router } from '@inertiajs/vue3';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{
    links: PaginationLink[];
    from: number;
    to: number;
    total: number;
}>();

const goTo = (url: string | null) => {
    if (!url) return;
    router.visit(url, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <div class="p-6 bg-surface-container-lowest dark:bg-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-border-subtle dark:border-gray-700">
        <p class="text-body-sm text-outline dark:text-gray-400">
            Showing <span class="font-bold text-on-surface dark:text-gray-100">{{ from }}</span>
            to <span class="font-bold text-on-surface dark:text-gray-100">{{ to }}</span>
            of <span class="font-bold text-on-surface dark:text-gray-100">{{ total }}</span> tickets
        </p>

        <div class="flex items-center gap-2">
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
    </div>
</template>
