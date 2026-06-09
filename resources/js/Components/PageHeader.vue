<template>
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
    <p v-if="description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ description }}</p>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
});
</script>