<template>
  <HiveLayout title="My Grades" description="View your academic grades and results">
    <div class="space-y-6">
      <!-- Summary Cards -->
      <div v-if="grades.length" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">GPA</p>
          <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ gpa }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Modules Passed</p>
          <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ passedCount }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Average Score</p>
          <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ averageScore }}%</p>
        </div>
      </div>

      <!-- Grades Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
            <tr>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Module</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Assessment</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Score</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Grade</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="grades.length === 0">
            <td colspan="5">
              <EmptyState type="assignment" title="No grades available yet" description="Your grades will appear here once assessments are marked." />
            </td>
          </tr>
            <tr v-for="grade in grades" :key="grade.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ grade.module?.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 hidden md:block">{{ grade.module?.code }}</p>
              </td>
              <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden md:table-cell">
                {{ grade.assessment?.title }}
              </td>
              <td class="px-6 py-4">
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ grade.score }}%</span>
              </td>
              <td class="px-6 py-4">
                <span :class="gradeColor(grade.grade)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ grade.grade }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-500 dark:text-gray-400 hidden lg:table-cell">
                {{ formatDate(grade.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import { computed } from 'vue';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { ClipboardDocumentListIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  grades: { type: Array, default: () => [] },
});

const passedCount = computed(() => props.grades.filter(g => ['A', 'B', 'C', 'D'].includes(g.grade)).length);
const averageScore = computed(() => {
  if (props.grades.length === 0) return 0;
  return Math.round(props.grades.reduce((sum, g) => sum + g.score, 0) / props.grades.length);
});
const gpa = computed(() => {
  if (props.grades.length === 0) return '-';
  const points = { A: 4, B: 3, C: 2, D: 1, F: 0 };
  const total = props.grades.reduce((sum, g) => sum + (points[g.grade] || 0), 0);
  return (total / props.grades.length).toFixed(2);
});

const gradeColor = (grade) => {
  if (grade === 'A') return 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300';
  if (grade === 'B') return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300';
  if (grade === 'C') return 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300';
  if (grade === 'D') return 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300';
  return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
};

const formatDate = (date) => date ? new Date(date).toLocaleDateString() : '—';
</script>