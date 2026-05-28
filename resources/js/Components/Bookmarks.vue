<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
        <svg class="w-5 h-5 inline mr-2 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
        </svg>
        My Bookmarks
      </h3>
      <span class="text-xs text-gray-500 dark:text-gray-400">{{ bookmarks.length }} saved</span>
    </div>

    <div class="space-y-2 max-h-64 overflow-y-auto">
      <div v-if="bookmarks.length === 0" class="text-center py-8">
        <BookmarkIcon class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
        <p class="text-sm text-gray-500 dark:text-gray-400">No bookmarks yet</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Save modules and documents for quick access</p>
      </div>

      <div
        v-for="bookmark in bookmarks"
        :key="bookmark.id"
        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors group"
      >
        <a :href="bookmark.url" class="flex items-center flex-1 min-w-0">
          <component :is="getIcon(bookmark.type)" class="w-5 h-5 mr-3 text-amber-500 dark:text-amber-400 flex-shrink-0" />
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-800 dark:text-white truncate">{{ bookmark.title }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ bookmark.subtitle }}</p>
          </div>
        </a>
        <button @click="removeBookmark(bookmark)" class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { BookmarkIcon, BookOpenIcon, DocumentIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  bookmarks: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['remove']);

const getIcon = (type) => {
  const icons = {
    module: BookOpenIcon,
    document: DocumentIcon,
    default: BookmarkIcon,
  };
  return icons[type] || icons.default;
};

const removeBookmark = (bookmark) => {
  emit('remove', bookmark);
};
</script>