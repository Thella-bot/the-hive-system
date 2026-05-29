<template>
  <HiveLayout title="My Transcript">
    <div class="max-w-4xl mx-auto">
      <div class="mb-6">
        <Link :href="route('hive.dashboard')" class="text-gray-500 hover:text-gray-700">&larr; Back to Dashboard</Link>
      </div>

      <div class="bg-white shadow rounded p-6">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-800">My Transcript</h1>
            <p class="text-sm text-gray-500 mt-1">{{ student.name }}</p>
          </div>
          <a :href="route('hive.transcript.download', student.id)"
             class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700 text-sm">
            Download PDF
          </a>
        </div>

        <div v-if="modules && modules.length" class="space-y-4">
          <div v-for="module in modules" :key="module.id" class="border rounded-lg p-4">
            <div class="flex justify-between items-center mb-3">
              <div>
                <p class="font-semibold text-gray-800">{{ module.code }} — {{ module.name }}</p>
                <p class="text-xs text-gray-500">{{ module.totalGradables }} assessments · {{ module.gradedCount }} graded</p>
              </div>
              <span v-if="module.averageGrade !== null" class="text-lg font-bold text-amber-600">
                {{ module.averageGrade }}%
              </span>
              <span v-else class="text-sm text-gray-400">No grades yet</span>
            </div>
          </div>
        </div>

        <p v-else class="text-gray-500 text-sm py-4 text-center">No modules enrolled.</p>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
  student: Object,
  modules: Array,
});
</script>
