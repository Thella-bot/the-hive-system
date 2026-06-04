<template>
  <HiveLayout title="Key & Access Management">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">🗝️ Key & Access Management</h1>
      <button v-if="canCreate" @click="showAdd = !showAdd"
        class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm">
        + Add Key
      </button>
    </div>

    <div v-if="showAdd" class="bg-white rounded-xl border p-6 mb-6 max-w-md">
      <form @submit.prevent="submitKey" class="space-y-3">
        <div>
          <label class="block text-sm font-medium mb-1">Key Label</label>
          <input v-model="form.label" class="w-full border-gray-300 rounded-md shadow-sm" required placeholder="e.g. Kitchen Lab 2" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Location</label>
          <input v-model="form.location" class="w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Add Key</button>
      </form>
    </div>

    <div class="space-y-3">
      <div v-for="key in keys" :key="key.id" class="bg-white rounded-xl border p-5">
        <div class="flex justify-between items-center">
          <div>
            <div class="flex items-center gap-2">
              <span class="text-lg">🗝️</span>
              <h3 class="font-semibold text-gray-900">{{ key.label }}</h3>
              <span :class="statusClass(key.status)" class="text-xs px-2 py-0.5 rounded-full">{{ key.status }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-0.5">{{ key.location || 'No location set' }}</p>
          </div>
          <div class="flex gap-2">
            <button v-if="canIssue && key.status === 'available'" @click="openIssue(key)"
              class="text-sm text-blue-600 hover:text-blue-700 border border-blue-200 px-3 py-1 rounded">
              Issue
            </button>
            <button v-if="canIssue && key.status === 'issued'" @click="returnKey(key.id)"
              class="text-sm text-green-600 hover:text-green-700 border border-green-200 px-3 py-1 rounded">
              Return
            </button>
            <button v-if="canIssue && key.status === 'issued'" @click="reportLost(key.id)"
              class="text-sm text-red-600 hover:text-red-700 border border-red-200 px-3 py-1 rounded">
              Lost
            </button>
          </div>
        </div>
        <div v-if="key.current_assignment" class="mt-2 text-sm text-gray-500">
          Held by: <span class="font-medium text-gray-700">{{ key.current_assignment[0]?.user?.name }}</span>
        </div>
      </div>
    </div>

    <!-- Issue Modal -->
    <DialogModal :show="showIssue" @close="showIssue = false">
      <template #title>Issue Key: {{ selectedKey?.label }}</template>
      <template #content>
        <select v-model="issueUserId" class="w-full border-gray-300 rounded-md shadow-sm">
          <option value="">Select staff member...</option>
          <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
        </select>
      </template>
      <template #footer>
        <button @click="showIssue = false" class="mr-3 text-sm text-gray-600">Cancel</button>
        <button @click="issueKey" :disabled="!issueUserId" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 disabled:opacity-50">Issue</button>
      </template>
    </DialogModal>
  </HiveLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';

const props = defineProps({ keys: Array, users: Array });
const showAdd = ref(false);
const showIssue = ref(false);
const selectedKey = ref(null);
const issueUserId = ref('');
const roles = computed(() => usePage().props.auth?.user?.roles || []);
const canCreate = computed(() => roles.value.some(r => ['super-admin', 'school-admin'].includes(r)));
const canIssue = computed(() => roles.value.some(r => ['super-admin', 'school-admin'].includes(r)));
const form = ref({ label: '', location: '' });

const statusClass = (s) => {
  switch (s) {
    case 'available': return 'bg-green-100 text-green-800';
    case 'issued': return 'bg-yellow-100 text-yellow-800';
    case 'lost': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-500';
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