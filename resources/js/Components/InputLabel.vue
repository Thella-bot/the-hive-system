<script setup>
defineProps({
    value: String,
    required: Boolean,
});
</script>

<template>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
        <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>
</template>