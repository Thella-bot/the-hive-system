<template>
  <HiveLayout title="Uniform & Workwear Requests">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">👔 Uniform & Workwear Requests</h1>
      <button @click="showRequest = !showRequest" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm">
        Request Item
      </button>
    </div>

    <!-- Request form -->
    <div v-if="showRequest" class="bg-white rounded-xl border p-6 mb-6 max-w-md">
      <form @submit.prevent="submitRequest" class="space-y-3">
        <div>
          <label class="block text-sm font-medium mb-1">Item Type</label>
          <select v-model="form.item_type" class="w-full border-gray-300 rounded-md shadow-sm">
            <option value="chef_jacket">Chef Jacket</option>
            <option value="trousers">Trousers</option>
            <option value="apron">Apron</option>
            <option value="safety_shoes">Safety Shoes</option>
            <option value="hat">Chef Hat</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Size</label>
          <input v-model="form.size" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="e.g. M, L, 42" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Quantity</label>
          <input type="number" v-model="form.quantity" min="1" max="10" class="w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Submit Request</button>
      </form>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Item</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Size</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Qty</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Requested By</th>
            <th v-if="isAdmin" class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-if="requests.data.length === 0">
            <td colspan="6" class="px-6 py-8 text-center text-gray-400">No requests found.</td>
          </tr>
          <tr v-for="r in requests.data" :key="r.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 capitalize">{{ formatItemType(r.item_type) }}</td>
            <td class="px-6 py-4">{{ r.size || '—' }}</td>
            <td class="px-6 py-4">{{ r.quantity }}</td>
            <td class="px-6 py-4">
              <span :class="statusClass(r.status)">{{ statusLabels[r.status] ?? r.status }}</span>
            </td>
            <td class="px-6 py-4 text-gray-500">{{ r.user?.name }}</td>
            <td v-if="isAdmin && r.status === 'pending'" class="px-6 py-4">
              <div class="flex gap-2">
                <button @click="review(r.id, 'approved')" class="text-green-600 text-xs hover:text-green-700">Approve</button>
                <button @click="review(r.id, 'rejected')" class="text-red-600 text-xs hover:text-red-700">Reject</button>
              </div>
            </td>
            <td v-else-if="isAdmin" class="px-6 py-4 text-gray-400 text-xs">—</td>
          </tr>
        </tbody>
      </table>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({ requests: Object });
const showRequest = ref(false);
const roles = computed(() => usePage().props.auth?.user?.roles || []);
const isAdmin = computed(() => roles.value.some(r => ['super-admin', 'school-admin'].includes(r)));
const form = ref({ item_type: 'chef_jacket', size: '', quantity: 1 });

const statusClass = (s) => {
  switch (s) {
    case 'approved': return 'px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs';
    case 'issued': return 'px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs';
    case 'rejected': return 'px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-xs';
    default: return 'px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs';
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
};

const review = (id, status) => {
  router.post(route('hive.uniform-requests.review', { uniform_request: id }), { status });
};
</script>