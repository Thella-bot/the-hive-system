<template>
  <HiveLayout title="Gradebook" description="Select a module to manage grades">
    <template #header-actions>
      <Link :href="route('hive.grades.index')"
        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
        title="Gradebook">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
      </Link>
    </template>

    <!-- Empty state -->
    <div v-if="modules.length === 0" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
      <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Modules Assigned</h3>
      <p class="text-gray-500 dark:text-gray-400">You are not assigned to instruct any modules.</p>
    </div>

    <!-- Module cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Link
        v-for="mod in modules" :key="mod.id"
        :href="route('hive.grades.manage', { module: mod.id })"
        class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg hover:border-amber-500 dark:hover:border-amber-500 transition-all"
      >
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ mod.name }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ mod.code }}</p>
          </div>
          <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/40 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>

        <div v-if="mod.programme" class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          {{ mod.programme.name }}
        </div>

        <div class="grid grid-cols-3 gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
          <div class="text-center">
            <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ getStats(mod).total }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Assessments</p>
          </div>
          <div class="text-center">
            <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ getStats(mod).graded }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Graded</p>
          </div>
          <div class="text-center">
            <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ getStats(mod).pending }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
          </div>
        </div>
      </Link>
    </div>
  </HiveLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
  modules: { type: Array, default: () => [] },
});

const getStats = (mod) => {
  const gradables = mod.gradables || [];
  const total = gradables.length;
  const graded = gradables.filter(g =>
    g.submissions && g.submissions.some(s => s.grade !== null && s.grade !== undefined)
  ).length;
  const pending = gradables.filter(g =>
    g.submissions && g.submissions.some(s => s.submitted_at && (s.grade === null || s.grade === undefined))
  ).length;

  return { total, graded, pending };
};
</script>