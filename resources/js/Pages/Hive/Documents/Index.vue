<template>
  <HiveLayout :title="pageTitle" description="Academic materials and institute resources">
    <div class="space-y-6">
      <!-- Upload Button -->
      <div class="flex justify-end">
        <Link v-if="canCreate" :href="route('hive.documents.create')"
          class="inline-flex items-center px-4 py-2.5 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 active:bg-amber-800 transition">
          <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
          Upload Document
        </Link>
      </div>

      <!-- Category Filter Cards (show when no category selected) -->
      <div v-if="!selectedCategory" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <Link v-for="cat in categories" :key="cat"
          :href="route('hive.documents.index', { category: cat })"
          class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md hover:border-amber-300 dark:hover:border-amber-500 transition-all group">
          <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center mb-3 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/50 transition-colors">
            <DocumentIcon class="w-5 h-5 text-amber-600 dark:text-amber-400" />
          </div>
          <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ cat }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ getCategoryCount(cat) }} files</p>
        </Link>
      </div>

      <!-- Module Filter Cards (for staff/admin when no category filtered) -->
      <div v-if="!selectedCategory && modules?.length && canCreate" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
        <Link :href="route('hive.documents.index')"
          class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md hover:border-amber-300 dark:hover:border-amber-500 transition-all group"
          :class="{ 'ring-2 ring-amber-500': !selectedModule }">
          <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mb-3 group-hover:bg-gray-200 dark:group-hover:bg-gray-600 transition-colors">
            <FolderIcon class="w-5 h-5 text-gray-600 dark:text-gray-400" />
          </div>
          <h3 class="font-semibold text-gray-900 dark:text-white text-sm">All Modules</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ documents.length }} files</p>
        </Link>
        <Link v-for="mod in modules" :key="mod.id"
          :href="route('hive.documents.index', { module_id: mod.id })"
          class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md hover:border-amber-300 dark:hover:border-amber-500 transition-all group"
          :class="{ 'ring-2 ring-amber-500': selectedModule == mod.id }">
          <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center mb-3 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/50 transition-colors">
            <FolderIcon class="w-5 h-5 text-amber-600 dark:text-amber-400" />
          </div>
          <h3 class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ mod.name }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ mod.programme?.name || 'General' }}</p>
        </Link>
      </div>

      <!-- Back to Categories/Modules (when filtered) -->
      <div v-if="selectedCategory || selectedModule" class="flex items-center gap-4 mb-4">
        <Link v-if="selectedCategory" :href="route('hive.documents.index')"
          class="flex items-center text-sm text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400">
          <ArrowLeftIcon class="w-4 h-4 mr-1" />
          All Categories
        </Link>
        <Link v-if="selectedModule && !selectedCategory && !canCreate" :href="route('hive.documents.module-select')"
          class="flex items-center text-sm text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400">
          <ArrowLeftIcon class="w-4 h-4 mr-1" />
          Select Module
        </Link>
        <Link v-if="selectedModule && !selectedCategory && canCreate" :href="route('hive.documents.index')"
          class="flex items-center text-sm text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400">
          <ArrowLeftIcon class="w-4 h-4 mr-1" />
          All Modules
        </Link>
        <span class="text-gray-300 dark:text-gray-600">|</span>
        <span class="text-sm text-gray-600 dark:text-gray-300">{{ documents.length }} documents</span>
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
            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 ml-1">
              {{ formatAudience(doc.audience) }}
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
import { ArrowUpTrayIcon, DocumentIcon, CheckIcon, ArrowDownTrayIcon, ArrowLeftIcon, FolderIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useUser } from '@/composables/useUser';

const props = defineProps({
  documents: Array,
  categories: Array,
  modules: Array,
});

const page = usePage();
const { currentUser } = useUser();

const selectedModule = computed(() => {
  const params = new URLSearchParams(page.url.split('?')[1] || '');
  return params.get('module_id');
});

const selectedCategory = computed(() => page.url.includes('category=') ? page.url.split('category=')[1] : null);

const pageTitle = computed(() => {
  if (selectedCategory.value) {
    return selectedCategory.value + ' Documents';
  }
  if (selectedModule.value && canCreate.value && modules.value) {
    const mod = modules.value.find(m => m.id == selectedModule.value);
    return mod ? mod.name + ' Documents' : 'Documents';
  }
  return 'Documents';
});

const canCreate = computed(() => {
  if (!currentUser.value) return false;
  return currentUser.value.roles?.some(r => ['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff', 'department-head', 'chef-instructor'].includes(r));
});

// Get document count per category
const getCategoryCount = (category) => {
  return documents.value?.filter(d => d.category === category).length || 0;
};

const formatAudience = (audience) => ({
  module_students: 'Module',
  student_only: 'Students',
  staff_only: 'Staff',
  all_users: 'All Users',
  everyone: 'Public',
}[audience] || audience);
</script>