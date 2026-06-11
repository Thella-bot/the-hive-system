<template>
  <HiveLayout title="My Grades" description="View your grades by module">
    <template #header-actions>
      <Link :href="route('hive.grades.index')"
        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
        title="My Grades">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
      </Link>
    </template>
    <div v-if="modules.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
      <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Modules Enrolled</h3>
      <p class="text-gray-500">You are not enrolled in any modules yet.</p>
    </div>

    <div v-for="mod in modules" :key="mod.id" class="mb-8">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ mod.name }}</h2>

      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Assessment</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Due Date</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
              <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Grade</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="mod.gradables.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-gray-400">No assessments yet</td>
            </tr>
            <tr v-for="gradable in mod.gradables" :key="gradable.id" class="hover:bg-amber-50 transition-colors">
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900">{{ gradable.title }}</p>
              </td>
              <td class="px-6 py-4">
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="getTypeClass(gradable.type)">
                  {{ formatType(gradable.type) }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-500">{{ formatDate(gradable.due_date) }}</td>
              <td class="px-6 py-4">
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="getStatusClass(gradable)">
                  {{ getStatus(gradable) }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <span v-if="gradable.submission?.grade !== null && gradable.submission?.grade !== undefined" class="font-semibold text-gray-900">
                  {{ gradable.submission.grade }}/{{ gradable.max_marks || 100 }}
                </span>
                <span v-else class="text-gray-400">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Summary Card -->
    <div v-if="modules.length > 0" class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl">
        <p class="text-3xl font-bold text-amber-700">{{ averageGrade || 'N/A' }}</p>
        <p class="text-sm text-amber-600">Average Grade</p>
      </div>
      <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl">
        <p class="text-3xl font-bold text-green-700">{{ completedCount }}</p>
        <p class="text-sm text-green-600">Graded Assessments</p>
      </div>
      <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl">
        <p class="text-3xl font-bold text-orange-700">{{ pendingCount }}</p>
        <p class="text-sm text-orange-600">Pending Assessment</p>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({
  modules: { type: Array, default: () => [] },
});

// Calculate statistics
const allGradables = computed(() => props.modules.flatMap(m => m.gradables || []));

const completedCount = computed(() =>
  allGradables.value.filter(g => g.submission?.grade !== null && g.submission?.grade !== undefined).length
);

const pendingCount = computed(() =>
  allGradables.value.filter(g => g.submission?.submitted_at && g.submission?.grade === null).length
);

const averageGrade = computed(() => {
  const graded = allGradables.value.filter(g => g.submission?.grade !== null && g.submission?.grade !== undefined);
  if (graded.length === 0) return null;
  const total = graded.reduce((sum, g) => sum + parseFloat(g.submission.grade), 0);
  return (total / graded.length).toFixed(1);
});

const formatDate = (date) => dayjs(date).format('MMM D, YYYY');
const isOverdue = (date) => dayjs(date).isBefore(dayjs());

const formatType = (type) => {
  const labels = {
    quiz: 'Quiz',
    test: 'Test',
    assignment: 'Assignment',
    mid_term_exam: 'Mid-Term',
    final_exam: 'Final',
  };
  return labels[type] || type;
};

const getTypeClass = (type) => ({
  quiz: 'bg-blue-100 text-blue-800',
  test: 'bg-green-100 text-green-800',
  assignment: 'bg-amber-100 text-amber-800',
  mid_term_exam: 'bg-purple-100 text-purple-800',
  final_exam: 'bg-red-100 text-red-800',
}[type] || 'bg-gray-100 text-gray-800');

const getStatus = (gradable) => {
  if (!gradable.submission?.submitted_at) {
    return isOverdue(gradable.due_date) ? 'Overdue' : 'Not Submitted';
  }
  if (gradable.submission?.grade !== null && gradable.submission?.grade !== undefined) {
    return 'Graded';
  }
  return 'Submitted';
};

const getStatusClass = (gradable) => {
  if (!gradable.submission?.submitted_at) {
    return isOverdue(gradable.due_date) ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800';
  }
  if (gradable.submission?.grade !== null && gradable.submission?.grade !== undefined) {
    return 'bg-green-100 text-green-800';
  }
  return 'bg-yellow-100 text-yellow-800';
};
</script>