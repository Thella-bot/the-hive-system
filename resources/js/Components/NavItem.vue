<template>
  <Link
    :href="href"
    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
    :class="active
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
import { Link } from '@inertiajs/vue3'

defineProps({
  href:   { type: String, required: true },
  active: { type: Boolean, default: false },
})
</script>
