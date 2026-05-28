<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Attendance</h3>
      <span class="text-sm font-medium" :class="getOverallClass">{{ overallPercentage }}%</span>
    </div>

    <!-- Overall Progress Ring -->
    <div class="flex items-center justify-center mb-6">
      <div class="relative w-32 h-32">
        <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
          <circle
            cx="18" cy="18" r="15.5"
            fill="none"
            stroke="#e5e7eb"
            stroke-width="3"
            class="dark:stroke-gray-700"
          />
          <circle
            cx="18" cy="18" r="15.5"
            fill="none"
            :stroke="getProgressColor"
            stroke-width="3"
            stroke-linecap="round"
            :stroke-dasharray="`${overallPercentage} ${100 - overallPercentage}`"
            class="transition-all duration-500"
          />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <span class="text-lg font-bold" :class="getOverallClass">{{ overallPercentage }}%</span>
          <span class="text-xs text-gray-500 dark:text-gray-400">Overall</span>
        </div>
      </div>
    </div>

    <!-- Module Attendance -->
    <div class="space-y-3">
      <div v-if="moduleAttendance.length === 0" class="text-center py-6">
        <UserGroupIcon class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
        <p class="text-sm text-gray-500 dark:text-gray-400">No attendance data</p>
      </div>

      <div v-for="attendance in moduleAttendance" :key="attendance.module_id" class="space-y-1">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate pr-2">{{ attendance.module_name }}</span>
          <span class="text-xs font-medium" :class="getModuleClass(attendance.percentage)">{{ attendance.percentage }}%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
          <div
            class="h-2 rounded-full transition-all duration-500"
            :class="getProgressBarClass(attendance.percentage)"
            :style="{ width: attendance.percentage + '%' }"
          />
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div v-if="moduleAttendance.length > 0" class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 grid grid-cols-3 gap-3">
      <div class="text-center p-2 bg-green-50 dark:bg-green-900/20 rounded-lg">
        <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ presentDays }}</p>
        <p class="text-xs text-green-600 dark:text-green-400">Present</p>
      </div>
      <div class="text-center p-2 bg-red-50 dark:bg-red-900/20 rounded-lg">
        <p class="text-lg font-bold text-red-600 dark:text-red-400">{{ absentDays }}</p>
        <p class="text-xs text-red-600 dark:text-red-400">Absent</p>
      </div>
      <div class="text-center p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
        <p class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ totalDays }}</p>
        <p class="text-xs text-amber-600 dark:text-amber-400">Total</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { UserGroupIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  moduleAttendance: {
    type: Array,
    default: () => []
  },
  presentDays: {
    type: Number,
    default: 0
  },
  absentDays: {
    type: Number,
    default: 0
  },
  totalDays: {
    type: Number,
    default: 0
  }
});

const overallPercentage = computed(() => {
  if (props.totalDays === 0) return 0;
  return Math.round((props.presentDays / props.totalDays) * 100);
});

const getProgressColor = computed(() => {
  if (overallPercentage.value >= 90) return '#10b981';
  if (overallPercentage.value >= 75) return '#f59e0b';
  if (overallPercentage.value >= 60) return '#f97316';
  return '#ef4444';
});

const getOverallClass = computed(() => {
  if (overallPercentage.value >= 90) return 'text-green-600 dark:text-green-400';
  if (overallPercentage.value >= 75) return 'text-amber-600 dark:text-amber-400';
  if (overallPercentage.value >= 60) return 'text-orange-600 dark:text-orange-400';
  return 'text-red-600 dark:text-red-400';
});

const getModuleClass = (percentage) => {
  if (percentage >= 90) return 'text-green-600 dark:text-green-400';
  if (percentage >= 75) return 'text-amber-600 dark:text-amber-400';
  if (percentage >= 60) return 'text-orange-600 dark:text-orange-400';
  return 'text-red-600 dark:text-red-400';
};

const getProgressBarClass = (percentage) => {
  if (percentage >= 90) return 'bg-green-500 dark:bg-green-400';
  if (percentage >= 75) return 'bg-amber-500 dark:bg-amber-400';
  if (percentage >= 60) return 'bg-orange-500 dark:bg-orange-400';
  return 'bg-red-500 dark:bg-red-400';
};
</script>