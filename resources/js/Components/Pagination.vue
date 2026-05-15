<template>
  <div v-if="links.length > 3" class="flex items-center justify-between mt-6">
    <p class="text-sm text-gray-500">
      Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }} results
    </p>
    <div class="flex items-center gap-1">
      <template v-for="link in links" :key="link.label">
        <Link
          v-if="link.url"
          :href="link.url"
          preserve-scroll
          class="px-3 py-1.5 text-sm rounded-lg transition-colors"
          :class="link.active
            ? 'bg-amber-500 text-white font-medium'
            : 'text-gray-600 hover:bg-gray-100'"
          v-html="link.label"
        />
        <span
          v-else
          class="px-3 py-1.5 text-sm text-gray-300"
          v-html="link.label"
        />
      </template>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  links: { type: Array, required: true },
  meta:  { type: Object, required: true },
})
</script>
