<template>
  <div>
    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-0.5">{{ label }}</p>
    <p class="text-sm text-gray-900 font-medium">{{ displayValue }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], default: '—' },
})

const displayValue = computed(() => {
  if (props.value === null || props.value === undefined || props.value === '') {
    return '—'
  }
  return props.value
})
</script>