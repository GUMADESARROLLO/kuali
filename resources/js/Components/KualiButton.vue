<script setup lang="ts">
withDefaults(defineProps<{
    type?: 'button' | 'submit' | 'reset';
}>(), {
    type: 'submit',
});
</script>

<template>
    <button
        :type="type"
        class="w-full bg-primary text-white font-bold py-4 rounded-full hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
    >
        <slot />
    </button>
</template>
