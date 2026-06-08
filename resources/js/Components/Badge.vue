<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="colorClasses[color] ?? colorClasses.gray"
  >
    <slot />
  </span>
</template>

<script setup>
defineProps({
  color: {
    type: String,
    default: 'gray',
    validator: (v) => ['gray', 'green', 'yellow', 'orange', 'red', 'blue', 'amber', 'purple'].includes(v),
  },
})

const colorClasses = {
  gray:   'bg-hbci-warm-50 text-gray-700',
  green:  'bg-green-100 text-green-700',
  yellow: 'bg-hbci-warm-100 text-yellow-700',
  orange: 'bg-orange-100 text-orange-700',
  red:    'bg-red-100 text-red-700',
  blue:   'bg-blue-100 text-blue-700',
  amber:  'bg-hbci-warm-100 text-amber-700',
  purple: 'bg-purple-100 text-purple-700',
}
</script>
