<template>
  <HiveLayout :title="document.title" :description="`${document.category} - ${document.module?.name || 'General'} `">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Document Details Card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-start">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ document.title }}</h1>
              <span class="px-3 py-1 text-sm rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                {{ document.category }}
              </span>
              <!-- Acknowledged badge -->
              <span v-if="isAcknowledged" class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 flex items-center gap-1">
                ✓ Acknowledged
              </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Module: {{ document.module?.name || 'General' }} | Created by {{ document.creator?.name || 'Unknown' }} | Audience: {{ formatAudience(document.audience) }}
            </p>
          </div>
          <div class="flex gap-2">
            <button @click="acknowledge" v-if="!isAcknowledged"
              class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
              ✓ Acknowledge Read
            </button>
            <Link v-if="canSeeAcknowledgements"
              :href="route('hive.documents.acknowledgements', { document: document.id })"
              class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
              👁 {{ acknowledgementCount }} acknowledged
            </Link>
            <Link v-if="canUpdate"
              :href="route('hive.documents.edit', { document: document.id })"
              class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 active:bg-amber-700 transition">
              Edit
            </Link>
            <button v-if="canDelete"
              @click="deleteDocument"
              class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 transition">
              Delete
            </button>
          </div>
        </div>
        <div class="px-6 py-4">
          <p v-if="document.description" class="text-gray-600 dark:text-gray-300">{{ document.description }}</p>
          <p v-else class="text-gray-400 dark:text-gray-500 italic">No description provided.</p>
        </div>
      </div>

      <!-- Version History -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Version History</h3>
          <span class="text-sm text-gray-500 dark:text-gray-400">{{ document.versions?.length || 0 }} versions</span>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-700">
          <div v-for="version in document.versions" :key="version.id"
            class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                <span class="text-amber-700 dark:text-amber-300 font-bold text-sm">v{{ version.version_number }}</span>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Version {{ version.version_number }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  Uploaded by {{ version.uploader?.name || 'Unknown' }} on {{ formatDate(version.created_at) }}
                </p>
              </div>
            </div>
            <a :href="route('hive.documents.version.download', { version: version.id })"
              class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-amber-700 dark:text-amber-300 hover:text-amber-800 dark:hover:text-amber-200">
              <ArrowDownTrayIcon class="w-4 h-4 mr-1" />
              Download
            </a>
          </div>
        </div>

        <!-- Upload New Version -->
        <div v-if="canUpdate" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">Upload New Version</h4>
          <form @submit.prevent="uploadVersion" class="flex gap-3">
            <input type="file" @input="newVersionForm.file = $event.target.files[0]" required
              class="flex-1 text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 dark:file:bg-amber-900/30 dark:file:text-amber-300 hover:file:bg-amber-100">
            <button type="submit" :disabled="newVersionForm.processing"
              class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 disabled:opacity-50 transition">
              {{ newVersionForm.processing ? 'Uploading...' : 'Upload Version' }}
            </button>
          </form>
          <p v-if="newVersionForm.error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ newVersionForm.error }}</p>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';
import { useUser } from '@/composables/useUser';

const props = defineProps({ document: Object });
const { currentUser } = useUser();

const isAcknowledged = computed(() => props.document.is_acknowledged || false);
const acknowledgementCount = computed(() => props.document.acknowledgements_count || 0);

const canUpdate = computed(() => {
  if (!currentUser.value) return false;
  return currentUser.value.roles?.some(r => ['super-admin', 'school-admin'].includes(r.name)) ||
         currentUser.value.id === props.document.created_by;
});

const canDelete = computed(() => {
  if (!currentUser.value) return false;
  return currentUser.value.roles?.some(r => ['super-admin', 'school-admin'].includes(r.name));
});

const canSeeAcknowledgements = computed(() => {
  if (!currentUser.value) return false;
  return currentUser.value.roles?.some(r => ['super-admin', 'school-admin'].includes(r.name));
});

const newVersionForm = reactive({ file: null, processing: false, error: '' });

const formatDate = (date) => dayjs(date).format('MMM D, YYYY');
const formatAudience = (audience) => ({
  module_students: 'Module Students',
  student_only: 'All Students',
  staff_only: 'Staff Only',
  all_users: 'All Users',
  everyone: 'Everyone (Public)',
}[audience] || audience);

const acknowledge = () => {
  router.post(route('hive.documents.acknowledge', { document: props.document.id }));
};

const uploadVersion = async () => {
  if (!newVersionForm.file) return;
  newVersionForm.processing = true;
  newVersionForm.error = '';

  const data = new FormData();
  data.append('file', newVersionForm.file);

  try {
    await router.post(route('hive.documents.versions.store', { document: props.document.id }), data, {
      forceFormData: true,
    });
    newVersionForm.file = null;
  } catch (e) {
    newVersionForm.error = 'Failed to upload version';
  } finally {
    newVersionForm.processing = false;
  }
};

const deleteDocument = () => {
  if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
    router.delete(route('hive.documents.destroy', { document: props.document.id }));
  }
};
</script>