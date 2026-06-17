<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center justify-center px-4 py-2.5 bg-hbci-warm-500 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-hbci-warm-600 focus:bg-hbci-warm-600 active:bg-hbci-warm-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150">
        <slot />
    </button>
</template>