<template>
  <HiveLayout :title="module.name" :description="`Code: ${module.code}`">
    <div class="bg-white rounded-xl border border-gray-200 p-6 dark:bg-gray-800 dark:border-gray-700">
      <div class="mb-6">
        <Link :href="route('hive.modules.index')"
          class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
          title="Back to Modules">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
        </Link>
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
            <div class="flex flex-col">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Delivery Mode</dt>
              <dd class="text-base font-medium" :class="{
                'text-blue-600': module.delivery_mode === 'in_person',
                'text-emerald-600': module.delivery_mode === 'online',
                'text-purple-600': module.delivery_mode === 'hybrid'
              }">{{ formatDeliveryMode(module.delivery_mode) }}</dd>
            </div>
            <div v-if="module.meeting_platform || module.meeting_link" class="flex flex-col">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Meeting</dt>
              <dd class="text-base font-medium text-gray-900 dark:text-white">
                <span v-if="module.meeting_platform">{{ module.meeting_platform }}</span>
                <a v-if="module.meeting_link" :href="module.meeting_link" target="_blank" rel="noopener noreferrer" class="text-amber-600 hover:underline">Open link</a>
              </dd>
            </div>
            <div v-if="module.location" class="flex flex-col">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Location</dt>
              <dd class="text-base font-medium text-gray-900 dark:text-white">{{ module.location }}</dd>
            </div>
          </dl>
        </div>

        <div v-if="module.description">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Description</h3>
          <p class="text-gray-700 dark:text-gray-300">{{ module.description }}</p>
        </div>
      </div>

      <!-- Module Sub-navigation -->
      <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Module Tools</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <Link :href="route('hive.modules.progress.index', module.id)"
            class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:bg-amber-50 hover:border-amber-200 transition dark:border-gray-700 dark:hover:bg-amber-900/20 dark:hover:border-amber-700">
            <svg class="h-8 w-8 text-amber-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress</span>
          </Link>
          <Link :href="route('hive.modules.lesson-plans.index', module.id)"
            class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-200 transition dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-700">
            <svg class="h-8 w-8 text-blue-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Lesson Plans</span>
          </Link>
          <Link :href="route('hive.modules.courses.index', module.id)"
            class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:bg-emerald-50 hover:border-emerald-200 transition dark:border-gray-700 dark:hover:bg-emerald-900/20 dark:hover:border-emerald-700">
            <svg class="h-8 w-8 text-emerald-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Materials</span>
          </Link>
          <Link :href="route('hive.modules.grades.manage', module.id)"
            class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:bg-purple-50 hover:border-purple-200 transition dark:border-gray-700 dark:hover:bg-purple-900/20 dark:hover:border-purple-700">
            <svg class="h-8 w-8 text-purple-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Grades</span>
          </Link>
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

const formatDeliveryMode = (value) => {
  switch (value) {
    case 'online':
      return 'Online';
    case 'hybrid':
      return 'Hybrid';
    default:
      return 'In Person';
  }
};
</script>
