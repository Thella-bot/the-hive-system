<template>
  <Transition
    enter-active-class="transition ease-out duration-150"
    enter-from-class="opacity-0 -translate-y-2"
    leave-active-class="transition ease-in duration-100"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <div
      v-if="selected.length > 0"
      class="mb-3 flex flex-wrap items-center gap-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2.5 dark:border-amber-700 dark:bg-amber-900/20"
    >
      <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
        {{ selected.length }} selected<span v-if="total"> of {{ total }}</span>
      </p>

      <div class="ml-auto flex flex-wrap items-center gap-2">
        <slot name="actions" :selected="selected" :clear="clear" />

        <button
          type="button"
          @click="clear"
          class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-white dark:text-gray-300 dark:hover:bg-gray-700"
        >
          Clear
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
const props = defineProps({
  selected: { type: Array, required: true },
  total: { type: Number, default: 0 },
});

const emit = defineEmits(['clear']);

const clear = () => emit('clear');
</script>
