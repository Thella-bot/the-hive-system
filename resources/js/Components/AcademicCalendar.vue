<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Academic Calendar</h3>
      <Link :href="route('hive.events.index')" class="text-sm text-amber-600 hover:text-amber-700 dark:text-amber-400 font-medium">
        View all
      </Link>
    </div>

    <div class="space-y-3">
      <div v-if="events.length === 0" class="text-center py-8">
        <CalendarDaysIcon class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" />
        <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming events</p>
      </div>

      <div v-for="event in events.slice(0, maxEvents)" :key="event.id" class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-amber-100 dark:bg-amber-900/40 flex flex-col items-center justify-center">
          <span class="text-xs font-medium text-amber-600 dark:text-amber-400">{{ formatMonth(event.start) }}</span>
          <span class="text-lg font-bold text-amber-700 dark:text-amber-300">{{ formatDay(event.start) }}</span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 dark:text-white truncate">{{ event.title }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
            {{ formatTime(event.start) }}
            <span v-if="event.end" class="ml-1">- {{ formatTime(event.end) }}</span>
          </p>
          <span v-if="event.type" class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full" :class="getTypeClass(event.type)">
            {{ event.type }}
          </span>
        </div>
      </div>
    </div>

    <!-- Mini Calendar Legend -->
    <div v-if="deadlines.length > 0" class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
      <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Upcoming Deadlines</p>
      <div class="space-y-2">
        <div v-for="deadline in deadlines.slice(0, 3)" :key="deadline.id" class="flex items-center justify-between p-2 bg-red-50 dark:bg-red-900/20 rounded-lg">
          <div class="flex items-center space-x-2">
            <ClockIcon class="w-4 h-4 text-red-500 dark:text-red-400" />
            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ deadline.title }}</span>
          </div>
          <span class="text-xs font-semibold" :class="getDeadlineClass(deadline.due_date)">
            {{ formatDeadline(deadline.due_date) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { CalendarDaysIcon, ClockIcon } from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';

const props = defineProps({
  events: {
    type: Array,
    default: () => []
  },
  deadlines: {
    type: Array,
    default: () => []
  },
  maxEvents: {
    type: Number,
    default: 5
  }
});

const formatMonth = (date) => dayjs(date).format('MMM');
const formatDay = (date) => dayjs(date).format('D');
const formatTime = (date) => dayjs(date).format('h:mm A');

const formatDeadline = (date) => {
  const now = dayjs();
  const deadline = dayjs(date);
  const diffDays = deadline.diff(now, 'day');

  if (diffDays === 0) return 'Today';
  if (diffDays === 1) return 'Tomorrow';
  if (diffDays < 0) return 'Overdue';
  if (diffDays <= 7) return `${diffDays} days`;

  return deadline.format('MMM D');
};

const getTypeClass = (type) => {
  const classes = {
    holiday: 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
    exam: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    event: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    meeting: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
  };
  return classes[type] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
};

const getDeadlineClass = (date) => {
  const now = dayjs();
  const deadline = dayjs(date);
  const diffDays = deadline.diff(now, 'day');

  if (diffDays < 0) return 'text-red-600 dark:text-red-400 font-bold';
  if (diffDays <= 2) return 'text-orange-600 dark:text-orange-400 font-semibold';
  if (diffDays <= 7) return 'text-amber-600 dark:text-amber-400';

  return 'text-gray-500 dark:text-gray-400';
};
</script>
