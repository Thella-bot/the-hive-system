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

        <div v-if="modulesByYear && Object.keys(modulesByYear).length" class="space-y-6">
          <div v-for="(yearModules, year) in modulesByYear" :key="year">
            <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-3 pb-2 border-b border-gray-200 dark:border-gray-600">
              Academic Year {{ year }}
            </h2>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                    <th class="pb-2 pr-4">Code</th>
                    <th class="pb-2 pr-4">Module</th>
                    <th class="pb-2 pr-4 text-center">Credits</th>
                    <th class="pb-2 pr-4 text-center">Assessments</th>
                    <th class="pb-2 text-right">Grade</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="module in yearModules" :key="module.id" class="border-b border-gray-100 dark:border-gray-700">
                    <td class="py-2 pr-4 font-mono text-xs">{{ module.code }}</td>
                    <td class="py-2 pr-4">{{ module.name }}</td>
                    <td class="py-2 pr-4 text-center">{{ module.credits }}</td>
                    <td class="py-2 pr-4 text-center">{{ module.gradedCount }}/{{ module.totalGradables }}</td>
                    <td class="py-2 text-right font-semibold text-amber-600 dark:text-amber-400">
                      {{ module.averageGrade !== null ? module.averageGrade + '%' : 'N/A' }}
                    </td>
                  </tr>
                </tbody>
              </table>
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
  modulesByYear: Object,
});
</script>
