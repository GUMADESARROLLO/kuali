<script setup lang="ts">
defineProps<{
    priority: string;
}>();

const priorityMap: Record<string, { label: string; cls: string }> = {
    baja:    { label: 'Low', cls: 'bg-secondary-fixed text-on-secondary-fixed-variant' },
    media:   { label: 'Medium', cls: 'bg-tertiary-fixed text-on-tertiary-fixed-variant' },
    alta:    { label: 'High', cls: 'bg-error-container text-on-error-container' },
    urgente: { label: 'Urgent', cls: 'bg-error text-on-error' },
};
</script>

<template>
    <span
        :class="[
            'px-3 py-1 rounded-full text-[12px] font-bold',
            priorityMap[priority]?.cls ?? 'bg-surface-container text-outline'
        ]"
    >
        {{ priorityMap[priority]?.label ?? priority }}
    </span>
</template>
