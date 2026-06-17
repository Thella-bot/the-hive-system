<template>
  <HiveLayout title="Keys" description="Key and access management">
    <template #header-actions>
      <button v-if="canCreate" @click="showAdd = !showAdd"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        Add Key
      </button>
    </template>

    <div v-if="showAdd" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6 max-w-md">
      <form @submit.prevent="submitKey" class="space-y-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Key Label</label>
          <input v-model="form.label" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" required placeholder="e.g. Kitchen Lab 2" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
          <input v-model="form.location" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" />
        </div>
        <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm font-medium">Add Key</button>
      </form>
    </div>

    <div class="space-y-3">
      <div v-for="key in keys" :key="key.id" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex justify-between items-center">
          <div>
            <div class="flex items-center gap-2">
              <KeyIcon class="w-5 h-5 text-amber-600 dark:text-amber-400" />
              <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ key.label }}</h3>
              <span :class="statusClass(key.status)" class="text-xs px-2 py-0.5 rounded-full">{{ statusLabels[key.status] ?? key.status }}</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ key.location || 'No location set' }}</p>
          </div>
          <div class="flex gap-2">
            <button v-if="canIssue && key.status === 'available'" @click="openIssue(key)"
              class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 border border-blue-200 dark:border-blue-700 px-3 py-1 rounded-lg">
              Issue
            </button>
            <button v-if="canIssue && key.status === 'issued'" @click="returnKey(key.id)"
              class="text-sm text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 border border-green-200 dark:border-green-700 px-3 py-1 rounded-lg">
              Return
            </button>
            <button v-if="canIssue && key.status === 'issued'" @click="reportLost(key.id)"
              class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 border border-red-200 dark:border-red-700 px-3 py-1 rounded-lg">
              Lost
            </button>
          </div>
        </div>
        <div v-if="key.current_assignment" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
          Held by: <span class="font-medium text-gray-700 dark:text-gray-300">{{ key.current_assignment[0]?.user?.name }}</span>
        </div>
      </div>
    </div>

    <!-- Issue Modal -->
    <DialogModal :show="showIssue" @close="showIssue = false">
      <template #title>Issue Key: {{ selectedKey?.label }}</template>
      <template #content>
        <select v-model="issueUserId" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500">
          <option value="">Select staff member...</option>
          <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
        </select>
      </template>
      <template #footer>
        <button @click="showIssue = false" class="mr-3 text-sm text-gray-600 dark:text-gray-400">Cancel</button>
        <button @click="issueKey" :disabled="!issueUserId" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50">Issue</button>
      </template>
    </DialogModal>
  </HiveLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import { KeyIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { useUser } from '@/composables/useUser';

const props = defineProps({ keys: Array, users: Array });
const showAdd = ref(false);
const showIssue = ref(false);
const selectedKey = ref(null);
const issueUserId = ref('');
const { isAdmin } = useUser();
const canCreate = computed(() => isAdmin.value);
const canIssue = computed(() => isAdmin.value);
const form = ref({ label: '', location: '' });

const statusLabels = {
  available: 'Available',
  issued: 'Issued',
  lost: 'Lost',
};

const statusClass = (s) => {
  switch (s) {
    case 'available': return 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300';
    case 'issued': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300';
    case 'lost': return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
  }
};

const submitKey = () => {
  router.post(route('hive.keys.store'), form.value);
  showAdd.value = false;
};

const openIssue = (key) => {
  selectedKey.value = key;
  issueUserId.value = '';
  showIssue.value = true;
};

const issueKey = () => {
  router.post(route('hive.keys.issue', { key: selectedKey.value.id }), { user_id: issueUserId.value });
  showIssue.value = false;
};

const returnKey = (id) => router.post(route('hive.keys.return', { key: id }));
const reportLost = (id) => router.post(route('hive.keys.report-lost', { key: id }));
</script>