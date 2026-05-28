<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white">My Progress</h3>
      <span class="text-sm text-gray-500 dark:text-gray-400">{{ completedModules }} of {{ totalModules }} modules</span>
    </div>

    <!-- Overall Progress -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Overall Completion</span>
        <span class="text-sm font-bold text-amber-600">{{ overallPercentage }}%</span>
      </div>
      <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
        <div
          class="bg-gradient-to-r from-amber-400 to-amber-600 h-3 rounded-full transition-all duration-500"
          :style="{ width: overallPercentage + '%' }"
        />
      </div>
    </div>

    <!-- Module Progress -->
    <div class="space-y-4">
      <div v-for="(module, index) in moduleProgress" :key="module.id" class="relative">
        <div class="flex items-center justify-between mb-1">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate pr-2">{{ module.name }}</span>
          <span class="text-xs font-medium" :class="module.percentage >= 100 ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
            {{ module.percentage }}%
          </span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
          <div
            class="h-2 rounded-full transition-all duration-500"
            :class="getProgressColor(module.percentage)"
            :style="{ width: module.percentage + '%' }"
          />
        </div>
      </div>
    </div>

    <!-- Grade Average -->
    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <span class="text-sm text-gray-500 dark:text-gray-400">Average Grade</span>
        <span class="text-lg font-bold" :class="gradeColorClass">
          {{ averageGrade !== null ? averageGrade + '%' : 'N/A' }}
        </span>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="mt-4 grid grid-cols-3 gap-3">
      <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
        <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ completedModules }}</p>
        <p class="text-xs text-amber-600 dark:text-amber-400">Completed</p>
      </div>
      <div class="text-center p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
        <p class="text-xl font-bold text-orange-600 dark:text-orange-400">{{ inProgressModules }}</p>
        <p class="text-xs text-orange-600 dark:text-orange-400">In Progress</p>
      </div>
      <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
        <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ pendingAssessments }}</p>
        <p class="text-xs text-green-600 dark:text-green-400">Pending</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  completedModules: {
    type: Number,
    default: 0
  },
  totalModules: {
    type: Number,
    default: 0
  },
  moduleProgress: {
    type: Array,
    default: () => []
  },
  averageGrade: {
    type: Number,
    default: null
  },
  pendingAssessments: {
    type: Number,
    default: 0
  }
});

const overallPercentage = computed(() => {
  if (props.totalModules === 0) return 0;
  return Math.round((props.completedModules / props.totalModules) * 100);
});

const inProgressModules = computed(() => {
  return props.moduleProgress.filter(m => m.percentage > 0 && m.percentage < 100).length;
});

const gradeColorClass = computed(() => {
  if (props.averageGrade === null) return 'text-gray-400';
  if (props.averageGrade >= 75) return 'text-green-600 dark:text-green-400';
  if (props.averageGrade >= 50) return 'text-amber-600 dark:text-amber-400';
  return 'text-red-600 dark:text-red-400';
});

const getProgressColor = (percentage) => {
  if (percentage >= 100) return 'bg-green-500 dark:bg-green-400';
  if (percentage >= 75) return 'bg-amber-500 dark:bg-amber-400';
  if (percentage >= 50) return 'bg-amber-400 dark:bg-amber-500';
  if (percentage >= 25) return 'bg-orange-400 dark:bg-orange-500';
  return 'bg-orange-500 dark:bg-orange-400';
};
</script>
