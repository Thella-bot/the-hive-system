<template>
  <HiveLayout :title="documents.length + ' Documents'" description="Academic materials, recipes, schedules, and institute resources.">
    <div class="space-y-6">
      <!-- Upload Button -->
      <div class="flex justify-end">
        <Link v-if="canCreate" :href="route('hive.documents.create')"
          class="inline-flex items-center px-4 py-2.5 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 active:bg-amber-800 transition">
          <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
          Upload Document
        </Link>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-2">
        <Link :href="route('hive.documents.index')"
          class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
          :class="!selectedCategory ? 'bg-amber-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'">
          All
        </Link>
        <Link v-for="cat in categories" :key="cat"
          :href="route('hive.documents.index', { category: cat })"
          class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
          :class="selectedCategory === cat ? 'bg-amber-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'">
          {{ cat }}
        </Link>
      </div>

      <!-- Documents Grid -->
      <div v-if="documents.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="doc in documents" :key="doc.id"
          class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
              <DocumentIcon class="w-6 h-6 text-amber-600 dark:text-amber-400" />
            </div>
            <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
              {{ doc.category }}
            </span>
          </div>
          <h3 class="font-semibold text-gray-900 dark:text-white mb-1 truncate">{{ doc.title }}</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">
            {{ doc.description || 'No description' }}
          </p>
          <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-3">
            <span>{{ doc.module?.name || 'General' }}</span>
            <span class="flex items-center gap-2">
              <span v-if="doc.acknowledgements_count" class="text-green-600">✓ {{ doc.acknowledgements_count }}</span>
              <span>v{{ doc.latest_version?.version_number || 1 }}</span>
            </span>
          </div>
          <div v-if="doc.is_acknowledged" class="flex items-center gap-1 text-xs text-green-600 mb-2">
            <CheckIcon class="w-3 h-3" />
            Acknowledged
          </div>
          <div class="flex gap-2">
            <a v-if="doc.latest_version"
              :href="route('hive.documents.version.download', { version: doc.latest_version.id })"
              class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg text-xs font-medium text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition">
              <ArrowDownTrayIcon class="w-4 h-4 mr-1" />
              Download
            </a>
            <Link :href="route('hive.documents.show', { document: doc.id })"
              class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
              Details
            </Link>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <DocumentIcon class="w-16 h-16 mx-auto text-gray-400 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No documents found</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-4">
          {{ selectedCategory ? 'No documents in this category yet.' : 'Upload your first document to get started.' }}
        </p>
        <Link v-if="canCreate" :href="route('hive.documents.create')"
          class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 transition">
          Upload Document
        </Link>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowUpTrayIcon, DocumentIcon, CheckIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useUser } from '@/composables/useUser';

const props = defineProps({
  documents: Array,
  categories: Array,
});

const page = usePage();
const { currentUser } = useUser();
const selectedCategory = computed(() => page.url.includes('category=') ? page.url.split('category=')[1] : null);

const canCreate = computed(() => {
  if (!currentUser.value) return false;
  return currentUser.value.roles?.some(r => ['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff', 'department-head', 'chef-instructor'].includes(r));
});
</script>