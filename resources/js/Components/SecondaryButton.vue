<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-200 rounded-lg font-medium text-sm text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white transition-all duration-150">
        <slot />
    </button>
</template>