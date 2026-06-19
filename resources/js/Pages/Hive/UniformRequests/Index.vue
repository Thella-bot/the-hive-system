<template>
  <HiveLayout title="Uniform & Workwear Requests" description="Request and manage uniform items">
    <template #header-actions>
      <button @click="showRequest = !showRequest"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        Request Item
      </button>
    </template>

    <!-- Request form -->
    <div v-if="showRequest" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6 max-w-md">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">New Uniform Request</h3>
      <form @submit.prevent="submitRequest" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Type</label>
          <select v-model="form.item_type"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
            <option value="chef_jacket">Chef Jacket</option>
            <option value="trousers">Trousers</option>
            <option value="apron">Apron</option>
            <option value="safety_shoes">Safety Shoes</option>
            <option value="hat">Chef Hat</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Size</label>
          <input v-model="form.size" type="text"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
            placeholder="e.g. M, L, 42" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
          <input type="number" v-model="form.quantity" min="1" max="10"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" />
        </div>
        <button type="submit"
          class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          Submit Request
        </button>
      </form>
    </div>

    <!-- Requests table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Item</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Size</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Qty</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Requested By</th>
            <th v-if="isAdmin" class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="!requests.data || requests.data.length === 0">
            <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
              <QueueListIcon class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" />
              <p>No requests found</p>
            </td>
          </tr>
          <tr v-for="r in requests.data" :key="r.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <td class="px-6 py-4 capitalize text-gray-900 dark:text-gray-100">{{ formatItemType(r.item_type) }}</td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ r.size || '—' }}</td>
            <td class="px-6 py-4 text-gray-900 dark:text-gray-100">{{ r.quantity }}</td>
            <td class="px-6 py-4">
              <span :class="statusClass(r.status)">{{ statusLabels[r.status] ?? r.status }}</span>
            </td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ r.user?.name }}</td>
            <td v-if="isAdmin && r.status === 'pending'" class="px-6 py-4">
              <div class="flex gap-3">
                <button @click="review(r.id, 'approved')" class="text-green-600 dark:text-green-400 text-sm hover:text-green-700 dark:hover:text-green-300 font-medium">Approve</button>
                <button @click="review(r.id, 'rejected')" class="text-red-600 dark:text-red-400 text-sm hover:text-red-700 dark:hover:text-red-300 font-medium">Reject</button>
              </div>
            </td>
            <td v-else-if="isAdmin" class="px-6 py-4 text-gray-400 dark:text-gray-500 text-xs">—</td>
          </tr>
        </tbody>
      </table>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { PlusIcon, QueueListIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useUser } from '@/composables/useUser';

const props = defineProps({ requests: Object });
const showRequest = ref(false);
const { isAdmin } = useUser();
const form = ref({ item_type: 'chef_jacket', size: '', quantity: 1 });

const statusClass = (s) => {
  switch (s) {
    case 'approved': return 'px-2.5 py-1 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 rounded-full text-xs font-medium';
    case 'issued': return 'px-2.5 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 rounded-full text-xs font-medium';
    case 'rejected': return 'px-2.5 py-1 bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 rounded-full text-xs font-medium';
    default: return 'px-2.5 py-1 bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300 rounded-full text-xs font-medium';
  }
};

const statusLabels = {
  pending: 'Pending',
  approved: 'Approved',
  issued: 'Issued',
  rejected: 'Rejected',
};

const itemTypeLabels = {
  chef_jacket: 'Chef Jacket',
  trousers: 'Trousers',
  apron: 'Apron',
  safety_shoes: 'Safety Shoes',
  hat: 'Chef Hat',
  other: 'Other',
};

const formatItemType = (t) => itemTypeLabels[t] ?? t?.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) ?? '';

const submitRequest = () => {
  router.post(route('hive.uniform-requests.store'), form.value);
  showRequest.value = false;
  form.value = { item_type: 'chef_jacket', size: '', quantity: 1 };
};

const review = (id, status) => {
  router.post(route('hive.uniform-requests.review', { uniform_request: id }), { status });
};
</script>