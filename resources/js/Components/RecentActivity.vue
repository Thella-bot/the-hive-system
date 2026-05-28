<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Activity</h3>
      <span class="text-xs text-gray-500 dark:text-gray-400">Latest updates</span>
    </div>

    <div class="space-y-3 max-h-80 overflow-y-auto">
      <div v-if="activities.length === 0" class="text-center py-8">
        <ClockIcon class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
        <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity</p>
      </div>

      <div
        v-for="activity in activities.slice(0, maxActivities)"
        :key="activity.id"
        class="flex items-start space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
      >
        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center" :class="getIconBg(activity.type)">
          <component :is="getIcon(activity.type)" class="w-4 h-4" :class="getIconColor(activity.type)" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 dark:text-white">{{ activity.title }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ activity.description }}</p>
          <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ formatTime(activity.created_at) }}</p>
        </div>
        <span v-if="activity.badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="getBadgeClass(activity.badge)">
          {{ activity.badge }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ClockIcon,
  CheckCircleIcon,
  DocumentIcon,
  AcademicCapIcon,
  UserGroupIcon,
  BellIcon,
  ClipboardDocumentCheckIcon,
} from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
  activities: {
    type: Array,
    default: () => []
  },
  maxActivities: {
    type: Number,
    default: 10
  }
});

const getIcon = (type) => {
  const icons = {
    grade: CheckCircleIcon,
    submission: DocumentIcon,
    announcement: BellIcon,
    enrollment: AcademicCapIcon,
    user: UserGroupIcon,
    assessment: ClipboardDocumentCheckIcon,
  };
  return icons[type] || ClockIcon;
};

const getIconBg = (type) => {
  const classes = {
    grade: 'bg-green-100 dark:bg-green-900/40',
    submission: 'bg-blue-100 dark:bg-blue-900/40',
    announcement: 'bg-amber-100 dark:bg-amber-900/40',
    enrollment: 'bg-purple-100 dark:bg-purple-900/40',
    user: 'bg-gray-100 dark:bg-gray-700',
    assessment: 'bg-orange-100 dark:bg-orange-900/40',
  };
  return classes[type] || 'bg-gray-100 dark:bg-gray-700';
};

const getIconColor = (type) => {
  const classes = {
    grade: 'text-green-600 dark:text-green-400',
    submission: 'text-blue-600 dark:text-blue-400',
    announcement: 'text-amber-600 dark:text-amber-400',
    enrollment: 'text-purple-600 dark:text-purple-400',
    user: 'text-gray-600 dark:text-gray-400',
    assessment: 'text-orange-600 dark:text-orange-400',
  };
  return classes[type] || 'text-gray-600 dark:text-gray-400';
};

const getBadgeClass = (badge) => {
  const classes = {
    new: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    graded: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    submitted: 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
  };
  return classes[badge] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
};

const formatTime = (date) => dayjs(date).fromNow();
</script>