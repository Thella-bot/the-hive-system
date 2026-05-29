<template>
  <HiveLayout title="Visitor Management Log">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">🪪 Visitor Management</h1>
      <button @click="showLog = !showLog" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm">
        + Log Visitor
      </button>
    </div>

    <div v-if="showLog" class="bg-white rounded-xl border p-6 mb-6 max-w-lg">
      <form @submit.prevent="submitLog" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Visitor Name</label>
          <input v-model="form.visitor_name" class="w-full border-gray-300 rounded-md shadow-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">ID Number</label>
          <input v-model="form.id_number" class="w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Host Staff Member</label>
          <select v-model="form.host_user_id" class="w-full border-gray-300 rounded-md shadow-sm">
            <option value="">Select...</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Purpose</label>
          <input v-model="form.purpose" class="w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Arrival Time</label>
          <input type="datetime-local" v-model="form.arrived_at" class="w-full border-gray-300 rounded-md shadow-sm" required />
        </div>
        <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Check In Visitor</button>
      </form>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Visitor</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">ID</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Host</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Purpose</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Arrived</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-if="logs.data.length === 0">
            <td colspan="7" class="px-6 py-8 text-center text-gray-400">No visitor logs found.</td>
          </tr>
          <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-medium text-gray-900">{{ log.visitor_name }}</td>
            <td class="px-6 py-4 text-gray-500 text-xs">{{ log.id_number || '—' }}</td>
            <td class="px-6 py-4 text-gray-500">{{ log.host?.name || '—' }}</td>
            <td class="px-6 py-4 text-gray-500 text-xs">{{ log.purpose || '—' }}</td>
            <td class="px-6 py-4 text-gray-500 text-xs">{{ formatDate(log.arrived_at) }}</td>
            <td class="px-6 py-4">
              <span :class="log.status === 'checked_in' ? 'px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs' : 'px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs'">
                {{ log.status === 'checked_in' ? 'Checked In' : 'Checked Out' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <button v-if="log.status === 'checked_in'" @click="checkOut(log.id)" class="text-amber-600 text-xs hover:text-amber-700">Check Out</button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="logs.links" class="px-6 py-3 border-t flex justify-between text-sm text-gray-500">
        <span>Showing {{ logs.from }} to {{ logs.to }} of {{ logs.total }}</span>
        <div class="flex gap-2">
          <Link v-if="logs.prev_page_url" :href="logs.prev_page_url" class="px-3 py-1 border rounded hover:bg-gray-50">&laquo; Prev</Link>
          <Link v-if="logs.next_page_url" :href="logs.next_page_url" class="px-3 py-1 border rounded hover:bg-gray-50">Next &raquo;</Link>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({ logs: Object, users: Array });
const showLog = ref(false);
const form = ref({ visitor_name: '', id_number: '', host_user_id: '', purpose: '', arrived_at: '' });

const formatDate = (d) => dayjs(d).format('MMM D, YYYY h:mm A');

const submitLog = () => {
  router.post(route('hive.visitor-logs.store'), form.value);
  showLog.value = false;
};

const checkOut = (id) => {
  router.post(route('hive.visitor-logs.checkout', id));
};
</script>