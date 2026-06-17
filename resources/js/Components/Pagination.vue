<template>
  <div v-if="links?.length > 3" class="flex items-center justify-between mt-6">
    <p class="text-sm text-gray-500 dark:text-gray-400">
      Showing {{ meta?.from ?? 1 }}–{{ meta?.to ?? 0 }} of {{ meta?.total ?? 0 }} results
    </p>
    <div class="flex items-center gap-1">
      <template v-for="link in links" :key="link.label">
        <Link
          v-if="link.url"
          :href="link.url"
          preserve-scroll
          class="px-3 py-1.5 text-sm rounded-lg transition-all duration-150"
          :class="link.active
            ? 'bg-amber-500 text-white font-medium shadow-sm'
            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
          v-html="link.label"
        />
        <span
          v-else
          class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600"
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
  meta:  { type: Object, default: () => ({}) },
})
</script>
