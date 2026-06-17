<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center justify-center px-4 py-2.5 bg-red-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:ring-offset-2 transition-all duration-150">
        <slot />
    </button>
</template>
