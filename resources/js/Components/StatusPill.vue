<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    status: string;
}>();

const cfg = computed(() => {
    const map: Record<string, {
        label: string;
        bg: string;
        text: string;
        icon: 'pending' | 'success' | 'failed' | 'inprogress' | 'submitted' | 'expired';
        spin?: boolean;
    }> = {
        abierto:    { label: 'Abierto', bg: 'bg-amber-100', text: 'text-amber-900', icon: 'submitted' },
        en_proceso: { label: 'En proceso', bg: 'bg-sky-100', text: 'text-sky-800', icon: 'inprogress', spin: true },
        en_espera:  { label: 'En espera', bg: 'bg-yellow-100', text: 'text-yellow-700', icon: 'pending' },
        resuelto:   { label: 'Resuelto', bg: 'bg-green-100', text: 'text-green-700', icon: 'success' },
        cerrado:    { label: 'Cerrado', bg: 'bg-gray-100', text: 'text-gray-700', icon: 'expired' },
        cancelado:  { label: 'Cancelado', bg: 'bg-red-100', text: 'text-red-700', icon: 'failed' },
    };
    return map[props.status] ?? { label: props.status, bg: 'bg-gray-100', text: 'text-gray-600', icon: 'expired' };
});
</script>

<template>
    <div class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5" :class="[cfg.bg, cfg.text]">
        <!-- Pending / En espera -->
        <svg v-if="cfg.icon === 'pending'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <circle cx="8" cy="12" r="1"/>
            <circle cx="12" cy="12" r="1"/>
            <circle cx="16" cy="12" r="1"/>
        </svg>

        <!-- Success / Resuelto -->
        <svg v-else-if="cfg.icon === 'success'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <path d="M8 12l3 3 5-6"/>
        </svg>

        <!-- Failed / Cancelado -->
        <svg v-else-if="cfg.icon === 'failed'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <path d="M9 9l6 6M15 9l-6 6"/>
        </svg>

        <!-- In Progress -->
        <svg v-else-if="cfg.icon === 'inprogress'" class="w-3.5 h-3.5 shrink-0 animate-spin" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation-duration:3s">
            <path stroke-linecap="round" stroke-dasharray="8 8" d="M12 3a9 9 0 109 9"/>
        </svg>

        <!-- Submitted / Abierto -->
        <svg v-else-if="cfg.icon === 'submitted'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 16V8"/>
            <path d="M8.5 11.5L12 8l3.5 3.5"/>
            <path d="M8 18h8"/>
        </svg>

        <!-- Expired / Cerrado -->
        <svg v-else class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 7v5l3 2"/>
            <path d="M18 18l1 1"/>
        </svg>

        <span class="text-[12px] font-bold leading-tight">{{ cfg.label }}</span>
    </div>
</template>
