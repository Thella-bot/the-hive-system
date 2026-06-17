<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message" class="mt-1.5">
        <p class="text-sm text-red-600 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ message }}
        </p>
    </div>
</template>