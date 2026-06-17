<template>
  <HiveLayout title="Transcript" description="View your academic transcript">
    <div class="max-w-4xl mx-auto">
      <div class="mb-6">
        <Link :href="route('hive.dashboard')" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-sm flex items-center gap-1">
          <ArrowLeftIcon class="w-4 h-4" />
          Back to Dashboard
        </Link>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">My Transcript</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ student.name }}</p>
          </div>
          <a :href="route('hive.transcript.download', { student: student.id })"
             class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <ArrowDownTrayIcon class="w-4 h-4" />
            Download PDF
          </a>
        </div>

        <div v-if="modules && modules.length" class="space-y-4">
          <div v-for="module in modules" :key="module.id" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex justify-between items-center mb-3">
              <div>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ module.code }} — {{ module.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ module.totalGradables }} assessments · {{ module.gradedCount }} graded</p>
              </div>
              <span v-if="module.averageGrade !== null" class="text-lg font-bold text-amber-600 dark:text-amber-400">
                {{ module.averageGrade }}%
              </span>
              <span v-else class="text-sm text-gray-400">No grades yet</span>
            </div>
          </div>
        </div>

        <p v-else class="text-gray-500 dark:text-gray-400 text-sm py-4 text-center">No modules enrolled.</p>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowLeftIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
  student: Object,
  modules: Array,
});
</script>