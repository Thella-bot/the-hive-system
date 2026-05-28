<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Grade Analytics</h3>
      <span class="text-sm text-gray-500 dark:text-gray-400">All time</span>
    </div>

    <!-- Distribution Donut Chart -->
    <div class="flex items-center justify-center mb-6">
      <div class="relative w-40 h-40">
        <svg class="w-40 h-40 transform -rotate-90" viewBox="0 0 36 36">
          <circle
            cx="18" cy="18" r="15.5"
            fill="none"
            stroke="#e5e7eb"
            stroke-width="3"
            class="dark:stroke-gray-700"
          />
          <circle
            v-for="(segment, index) in chartData"
            :key="index"
            cx="18" cy="18" r="15.5"
            fill="none"
            :stroke="segment.color"
            stroke-width="3"
            :stroke-dasharray="`${segment.percentage} ${100 - segment.percentage}`"
            :stroke-dashoffset="-segment.offset"
            class="transition-all duration-500"
          />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <span class="text-2xl font-bold text-gray-800 dark:text-white">{{ averageGrade }}%</span>
          <span class="text-xs text-gray-500 dark:text-gray-400">Avg Grade</span>
        </div>
      </div>
    </div>

    <!-- Legend -->
    <div class="grid grid-cols-2 gap-2">
      <div v-for="segment in chartData" :key="segment.label" class="flex items-center space-x-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50">
        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: segment.color }" />
        <div class="flex-1">
          <p class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ segment.label }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ segment.count }} assessments</p>
        </div>
      </div>
    </div>

    <!-- Grade Distribution Bar -->
    <div class="mt-6">
      <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Grade Distribution</p>
      <div class="space-y-2">
        <div v-for="bar in barData" :key="bar.range" class="flex items-center space-x-3">
          <span class="text-xs text-gray-600 dark:text-gray-400 w-12">{{ bar.range }}</span>
          <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <div
              class="h-2 rounded-full transition-all duration-500"
              :style="{ width: bar.percentage + '%', backgroundColor: bar.color }"
            />
          </div>
          <span class="text-xs text-gray-500 dark:text-gray-400 w-8 text-right">{{ bar.count }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  grades: {
    type: Array,
    default: () => []
  },
  averageGrade: {
    type: Number,
    default: 0
  }
});

const chartData = computed(() => {
  const distinctions = props.grades.filter(g => g >= 75).length;
  const merits = props.grades.filter(g => g >= 60 && g < 75).length;
  const passes = props.grades.filter(g => g >= 50 && g < 60).length;
  const fails = props.grades.filter(g => g < 50).length;
  const total = props.grades.length || 1;

  let offset = 0;
  const data = [
    { label: 'Distinction', count: distinctions, percentage: (distinctions / total) * 100, color: '#10b981', offset: 0 },
    { label: 'Merit', count: merits, percentage: (merits / total) * 100, color: '#f59e0b', offset: 0 },
    { label: 'Pass', count: passes, percentage: (passes / total) * 100, color: '#fbbf24', offset: 0 },
    { label: 'Fail', count: fails, percentage: (fails / total) * 100, color: '#ef4444', offset: 0 },
  ];

  data.forEach((segment, i) => {
    if (i > 0) {
      offset += data[i - 1].percentage;
    }
    segment.offset = (offset / 100) * 62.83;
  });

  return data;
});

const barData = computed(() => {
  const ranges = [
    { range: '90-100', min: 90, max: 100, color: '#059669' },
    { range: '80-89', min: 80, max: 89, color: '#10b981' },
    { range: '70-79', min: 70, max: 79, color: '#34d399' },
    { range: '60-69', min: 60, max: 69, color: '#fbbf24' },
    { range: '50-59', min: 50, max: 59, color: '#f59e0b' },
    { range: '40-49', min: 40, max: 49, color: '#f97316' },
    { range: '0-39', min: 0, max: 39, color: '#ef4444' },
  ];

  const total = props.grades.length || 1;

  return ranges.map(range => {
    const count = props.grades.filter(g => g >= range.min && g <= range.max).length;
    return {
      ...range,
      count,
      percentage: (count / total) * 100
    };
  });
});
</script>
