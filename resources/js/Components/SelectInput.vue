<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
    disabled: Boolean,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <select
        ref="input"
        :disabled="disabled"
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        class="w-full rounded-lg border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 transition-colors appearance-none cursor-pointer"
    >
        <slot />
    </select>
</template>