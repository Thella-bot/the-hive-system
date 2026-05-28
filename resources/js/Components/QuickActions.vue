<template>
  <div class="fixed bottom-6 right-6 z-50">
    <!-- Dropdown Actions -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-4 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-4 scale-95"
    >
      <div v-if="isOpen" class="absolute bottom-16 right-0 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 py-2 overflow-hidden">
        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Quick Actions</p>
        </div>
        <div class="py-1">
          <a
            v-for="action in actions"
            :key="action.label"
            :href="action.href"
            class="flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            <component :is="action.icon" class="w-5 h-5 mr-3 text-amber-500 dark:text-amber-400" />
            {{ action.label }}
          </a>
        </div>
      </div>
    </transition>

    <!-- Main FAB -->
    <button
      @click="toggle"
      class="h-14 w-14 rounded-full bg-gradient-to-br from-amber-500 to-amber-600 dark:from-amber-600 dark:to-amber-700 text-white shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center"
      :class="{ 'rotate-45': isOpen }"
      :title="isOpen ? 'Close menu' : 'Quick actions'"
    >
      <PlusIcon class="h-6 w-6 transition-transform" />
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { PlusIcon, BookOpenIcon, DocumentIcon, CalendarDaysIcon, ClipboardDocumentCheckIcon, ChatBubbleLeftRightIcon, UserPlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  actions: {
    type: Array,
    default: () => [
      { label: 'Upload Document', href: '/hive/documents/create', icon: DocumentIcon },
      { label: 'Create Assessment', href: '/hive/gradables/create', icon: ClipboardDocumentCheckIcon },
      { label: 'Announce', href: '/hive/announcements/create', icon: CalendarDaysIcon },
      { label: 'Create Event', href: '/hive/events/create', icon: CalendarDaysIcon },
    ]
  }
});

const isOpen = ref(false);

const toggle = () => {
  isOpen.value = !isOpen.value;
};
</script>
