<template>
  <th
    :class="[
      align === 'right' ? 'text-right' : align === 'center' ? 'text-center' : 'text-left',
      headerClass,
    ]"
    :aria-sort="ariaSort"
    scope="col"
  >
    <button
      v-if="sortable"
      type="button"
      class="group inline-flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none"
      :class="active ? 'text-amber-700 dark:text-amber-400' : ''"
      :title="`Sort by ${label}`"
      @click="toggle"
    >
      <span>{{ label }}</span>
      <component
        :is="icon"
        class="h-3.5 w-3.5 shrink-0 transition-opacity"
        :class="active ? 'opacity-100' : 'opacity-0 group-hover:opacity-50'"
      />
    </button>
    <template v-else>{{ label }}</template>
  </th>
</template>

<script setup>
import { computed } from 'vue';
import {
  ArrowDownIcon,
  ArrowUpIcon,
  ArrowsUpDownIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  label: { type: String, required: true },
  // The column key understood by the server-side query.
  field: { type: String, default: null },
  sortable: { type: Boolean, default: false },
  align: { type: String, default: 'left' },
  activeField: { type: String, default: null },
  direction: { type: String, default: 'asc' },
  headerClass: { type: String, default: 'px-6 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400' },
});

const emit = defineEmits(['sort']);

const active = computed(() => props.sortable && props.field !== null && props.field === props.activeField);

const icon = computed(() => {
  if (!props.sortable) return ArrowsUpDownIcon;
  if (!active.value) return ArrowsUpDownIcon;
  return props.direction === 'asc' ? ArrowUpIcon : ArrowDownIcon;
});

const ariaSort = computed(() => {
  if (!active.value) return 'none';
  return props.direction === 'asc' ? 'ascending' : 'descending';
});

const toggle = () => {
  if (!props.sortable || props.field === null) return;

  // Clicking the active column flips direction; a new column starts ascending.
  const nextDirection = active.value && props.direction === 'asc' ? 'desc' : 'asc';

  emit('sort', { field: props.field, direction: nextDirection });
};
</script>
