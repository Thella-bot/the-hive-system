<template>
  <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
    <div class="w-20 h-20 mb-4 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center">
      <component :is="icon" class="w-10 h-10 text-amber-400 dark:text-amber-500" />
    </div>
    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">{{ title }}</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-4">{{ description }}</p>
    <slot name="action" />
  </div>
</template>

<script setup>
import {
  DocumentTextIcon,
  AcademicCapIcon,
  ClipboardDocumentCheckIcon,
  BookOpenIcon,
  InboxIcon,
  UserGroupIcon,
  CalendarDaysIcon,
  BellIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  title: {
    type: String,
    default: 'No items found'
  },
  description: {
    type: String,
    default: 'There is nothing here yet.'
  },
  type: {
    type: String,
    default: 'default'
  }
});

const icons = {
  document: DocumentTextIcon,
  academic: AcademicCapIcon,
  assignment: ClipboardDocumentCheckIcon,
  book: BookOpenIcon,
  inbox: InboxIcon,
  users: UserGroupIcon,
  calendar: CalendarDaysIcon,
  notification: BellIcon,
  default: InboxIcon,
};

const icon = icons[props.type] || icons.default;
</script>
