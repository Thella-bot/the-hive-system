<template>
  <HiveLayout :title="module.name" :description="`Code: ${module.code}`">
    <div class="bg-white rounded-xl border border-gray-200 p-6 dark:bg-gray-800 dark:border-gray-700">
      <div class="mb-6">
        <Link :href="route('hive.modules.index')" class="text-sm text-amber-600 hover:text-amber-700 dark:text-amber-400">← Back to Modules</Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Module Details</h3>
          <dl class="space-y-3">
            <div class="flex flex-col">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Code</dt>
              <dd class="text-base font-medium text-gray-900 dark:text-white">{{ module.code }}</dd>
            </div>
            <div class="flex flex-col">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Credits</dt>
              <dd class="text-base font-medium text-gray-900 dark:text-white">{{ module.credits }}</dd>
            </div>
            <div class="flex flex-col">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Programme</dt>
              <dd class="text-base font-medium text-gray-900 dark:text-white">{{ module.programme?.name || 'N/A' }}</dd>
            </div>
            <div class="flex flex-col">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Department</dt>
              <dd class="text-base font-medium text-gray-900 dark:text-white">{{ module.department?.name || 'N/A' }}</dd>
            </div>
          </dl>
        </div>

        <div v-if="module.description">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Description</h3>
          <p class="text-gray-700 dark:text-gray-300">{{ module.description }}</p>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
  module: Object,
});
</script>
