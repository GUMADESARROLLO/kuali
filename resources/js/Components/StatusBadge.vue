<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    status: string;
}>();

const cfg = computed(() => {
    const map: Record<string, { label: string; dot: string; text: string }> = {
        abierto:    { label: 'Open', dot: 'bg-primary', text: 'text-primary' },
        en_proceso: { label: 'In Progress', dot: 'bg-[#dba263]', text: 'text-[#dba263]' },
        en_espera:  { label: 'On-Hold', dot: 'bg-[#dba263]', text: 'text-[#dba263]' },
        resuelto:   { label: 'Resolved', dot: 'bg-success-green', text: 'text-success-green' },
        cerrado:    { label: 'Closed', dot: 'bg-outline', text: 'text-outline' },
        cancelado:  { label: 'Canceled', dot: 'bg-error', text: 'text-error' },
    };
    return map[props.status] ?? { label: props.status, dot: 'bg-outline', text: 'text-outline' };
});
</script>

<template>
    <div class="flex items-center gap-1.5" :class="cfg.text">
        <span class="w-1.5 h-1.5 rounded-full" :class="cfg.dot" />
        <span class="text-label-md">{{ cfg.label }}</span>
    </div>
</template>
