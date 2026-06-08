<template>
  <div class="bg-white rounded-xl border border-hbci-warm-100 p-6 flex items-start gap-4 shadow-sm hover:shadow-md transition-shadow">
    <div
      class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 bg-hbci-warm-100"
      :class="iconBg"
    >
      <slot name="icon" />
    </div>
    <div>
      <p class="text-sm text-gray-500 font-medium">{{ label }}</p>
      <p class="text-3xl font-bold text-gray-900 mt-1">{{ value }}</p>
      <p v-if="sub" class="text-xs text-gray-400 mt-1">{{ sub }}</p>
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
