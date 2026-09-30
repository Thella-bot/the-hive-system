<script setup>
import { computed } from 'vue';
import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  href: { type: String, required: true },
  params: { type: Object, default: () => ({}) },
  total: { type: Number, default: null },
  label: { type: String, default: 'Export CSV' },
});

/**
 * The export must match what the user is looking at, so the active filters
 * ride along on the query string. An empty filter set produces a bare href
 * rather than a trailing "?".
 */
const downloadHref = computed(() => {
  const query = new URLSearchParams(
    Object.entries(props.params).filter(([, value]) => value !== null && value !== undefined && value !== '')
  ).toString();

  return query ? `${props.href}?${query}` : props.href;
});

const countLabel = computed(() => {
  if (props.total === null) return '';

  return `${props.total} row${props.total === 1 ? '' : 's'}`;
});
</script>

<template>
  <a
    :href="downloadHref"
    class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 transition-colors"
    :title="countLabel
      ? `Download the ${countLabel} matching these filters as CSV`
      : `Download as CSV`"
  >
    <ArrowDownTrayIcon class="w-4 h-4" />
    {{ label }}
  </a>
</template>
