<template>
  <Link
    :href="href"
    :target="target"
    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
    :class="isActive
      ? 'bg-amber-600 text-white'
      : 'text-gray-300 hover:bg-gray-800 hover:text-white'"
  >
    <span class="flex-shrink-0">
      <slot name="icon" />
    </span>
    <slot />
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  href:   { type: String, required: true },
  active: { type: [Boolean, String], default: false },
  target: { type: String, default: null },
})

const isActive = computed(() => {
  if (typeof props.active === 'boolean') {
    return props.active
  }
  return props.active ? route().current(props.active) : false
})
</script>
