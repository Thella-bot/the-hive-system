<template>
  <HiveLayout title="Gradebook" description="Select a module to manage grades">
    <template #header-actions>
      <Link :href="route('hive.grades.index')" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
        ← Gradebook
      </Link>
    </template>
    <div v-if="modules.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
      <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Modules Assigned</h3>
      <p class="text-gray-500">You are not assigned to instruct any modules.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Link
        v-for="mod in modules" :key="mod.id"
        :href="route('hive.grades.manage', { module: mod.id })"
        class="block bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg hover:border-amber-500 transition-all"
      >
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ mod.name }}</h3>
            <p class="text-sm text-gray-500">{{ mod.code }}</p>
          </div>
          <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>

        <div v-if="mod.programme" class="text-sm text-gray-600 mb-4">
          {{ mod.programme.name }}
        </div>

        <div class="grid grid-cols-3 gap-2 pt-4 border-t border-gray-100">
          <div class="text-center">
            <p class="text-xl font-bold text-gray-900">{{ getStats(mod).total }}</p>
            <p class="text-xs text-gray-500">Assessments</p>
          </div>
          <div class="text-center">
            <p class="text-xl font-bold text-green-700">{{ getStats(mod).graded }}</p>
            <p class="text-xs text-gray-500">Graded</p>
          </div>
          <div class="text-center">
            <p class="text-xl font-bold text-yellow-700">{{ getStats(mod).pending }}</p>
            <p class="text-xs text-gray-500">Pending</p>
          </div>
        </div>
      </Link>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue';
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
