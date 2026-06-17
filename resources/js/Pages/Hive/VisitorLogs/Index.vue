<template>
  <HiveLayout title="Visitor Management Log" description="Track and manage visitor check-ins">
    <template #header-actions>
      <button @click="showLog = !showLog"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        Log Visitor
      </button>
    </template>

    <!-- Log form -->
    <div v-if="showLog" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6 max-w-lg">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Check In Visitor</h3>
      <form @submit.prevent="submitLog" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Visitor Name</label>
          <input v-model="form.visitor_name" type="text"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
            required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID Number</label>
          <input v-model="form.id_number" type="text"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Host Staff Member</label>
          <select v-model="form.host_user_id"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
            <option value="">Select...</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Purpose</label>
          <input v-model="form.purpose" type="text"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Arrival Time</label>
          <input type="datetime-local" v-model="form.arrived_at"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
            required />
        </div>
        <button type="submit"
          class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          Check In Visitor
        </button>
      </form>
    </div>

    <!-- Logs table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Visitor</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">ID</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Host</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Purpose</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Arrived</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="!logs.data || logs.data.length === 0">
            <td colspan="7" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
              <UserGroupIcon class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" />
              <p>No visitor logs found</p>
            </td>
          </tr>
          <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ log.visitor_name }}</td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs">{{ log.id_number || '—' }}</td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ log.host?.name || '—' }}</td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs">{{ log.purpose || '—' }}</td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs">{{ formatDate(log.arrived_at) }}</td>
            <td class="px-6 py-4">
              <span :class="log.status === 'checked_in' ? 'px-2.5 py-1 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 rounded-full text-xs font-medium' : 'px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full text-xs font-medium'">
                {{ log.status === 'checked_in' ? 'Checked In' : 'Checked Out' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <button v-if="log.status === 'checked_in'" @click="checkOut(log.id)"
                class="text-amber-600 dark:text-amber-400 text-sm hover:text-amber-700 dark:hover:text-amber-300 font-medium">
                Check Out
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="logs.links" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center text-sm">
        <span class="text-gray-500 dark:text-gray-400">Showing {{ logs.from }} to {{ logs.to }} of {{ logs.total }}</span>
        <div class="flex gap-2">
          <Link v-if="logs.prev_page_url" :href="logs.prev_page_url"
            class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-medium transition-colors">
            &laquo; Prev
          </Link>
          <Link v-if="logs.next_page_url" :href="logs.next_page_url"
            class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-medium transition-colors">
            Next &raquo;
          </Link>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { PlusIcon, UserGroupIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({ logs: Object, users: Array });
const showLog = ref(false);
const form = ref({ visitor_name: '', id_number: '', host_user_id: '', purpose: '', arrived_at: '' });

const formatDate = (d) => dayjs(d).format('MMM D, YYYY h:mm A');

const submitLog = () => {
  router.post(route('hive.visitor-logs.store'), form.value);
  showLog.value = false;
  form.value = { visitor_name: '', id_number: '', host_user_id: '', purpose: '', arrived_at: '' };
};

const checkOut = (id) => {
  router.post(route('hive.visitor-logs.checkout', { log: id }));
};
</script>