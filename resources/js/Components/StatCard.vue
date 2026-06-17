<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6 flex items-start gap-4 shadow-sm hover:shadow-md transition-shadow">
    <div
      class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
      :class="iconBg"
    >
      <slot name="icon" />
    </div>
    <div>
      <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ label }}</p>
      <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ value }}</p>
      <p v-if="sub" class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ sub }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  label:   { type: String, required: true },
  value:   { type: [String, Number], required: true },
  sub:     { type: String, default: '' },
  iconBg:  { type: String, default: 'bg-amber-100' },
})
</script>
