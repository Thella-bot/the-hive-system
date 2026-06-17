<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
});

const classes = computed(() => {
    return props.active
        ? 'block w-full ps-3 pe-4 py-2.5 border-l-4 border-amber-500 text-start text-base font-medium text-gray-900 dark:text-white bg-amber-50 dark:bg-amber-900/20 focus:outline-none focus:text-gray-900 dark:focus:text-white focus:bg-amber-50 dark:focus:bg-amber-900/20 focus:border-amber-600 transition-all duration-150'
        : 'block w-full ps-3 pe-4 py-2.5 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-300 hover:text-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-white hover:border-gray-300 focus:outline-none focus:text-gray-800 dark:focus:text-white focus:bg-gray-50 dark:focus:bg-gray-800 focus:border-gray-300 transition-all duration-150';
});
</script>

<template>
    <div>
        <button v-if="as == 'button'" :class="classes" class="w-full text-start">
            <slot />
        </button>

        <a v-else-if="as == 'a'" :class="classes" class="w-full text-start" :href="href">
            <slot />
        </a>

        <Link v-else :href="href" :class="classes">
            <slot />
        </Link>
    </div>
</template>
