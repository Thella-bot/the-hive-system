<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
});

const classes = computed(() => {
    return props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-amber-500 text-sm font-medium leading-5 text-gray-900 dark:text-white focus:outline-none focus:border-amber-600 transition-all duration-150'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition-all duration-150';
});
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
