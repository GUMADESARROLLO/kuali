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
    <div class="p-6 bg-surface-container-lowest flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-border-subtle">
        <p class="text-body-sm text-outline">
            Showing <span class="font-bold text-on-surface">{{ from }}</span>
            to <span class="font-bold text-on-surface">{{ to }}</span>
            of <span class="font-bold text-on-surface">{{ total }}</span> tickets
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
                        ? 'bg-deep-navy text-white font-bold'
                        : link.url
                            ? 'border border-border-subtle text-outline hover:bg-surface-container-low'
                            : 'text-outline/30 cursor-not-allowed'
                ]"
                v-html="link.label"
            />
        </div>
    </div>
</template>
