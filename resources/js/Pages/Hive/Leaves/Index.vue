<template>
  <HiveLayout title="Leave Requests" :description="`Leave Balance: ${balance} days`">
    <template #header-actions>
      <Link v-if="!isAdmin" :href="route('hive.leaves.create')"
        class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Request Leave
      </Link>
    </template>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Start Date</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">End Date</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Days</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
            <th v-if="isAdmin" class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="leaves.length === 0">
            <td colspan="6" class="px-6 py-12 text-center text-gray-400">No leave requests found.</td>
          </tr>
          <tr v-for="leave in leaves" :key="leave.id" class="hover:bg-amber-50 transition-colors">
            <td class="px-6 py-4 text-gray-700">{{ leave.start_date }}</td>
            <td class="px-6 py-4 text-gray-700">{{ leave.end_date }}</td>
            <td class="px-6 py-4">
              <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 capitalize">
                {{ leave.type }}
              </span>
            </td>
            <td class="px-6 py-4 text-gray-700 hidden md:table-cell">{{ leave.days }}</td>
            <td class="px-6 py-4">
              <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="statusClass(leave.status)">
                {{ leave.status }}
              </span>
            </td>
            <td v-if="isAdmin && leave.status === 'pending'" class="px-6 py-4">
              <div class="flex items-center gap-3">
                <button @click="update(leave.id, 'approved')" class="text-green-600 hover:text-green-700 font-medium text-xs">Approve</button>
                <button @click="update(leave.id, 'rejected')" class="text-red-600 hover:text-red-700 font-medium text-xs">Reject</button>
              </div>
            </td>
            <td v-else-if="isAdmin" class="px-6 py-4">
              <span class="text-gray-400 text-xs">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({ leaves: Array, balance: Number });
const isAdmin = computed(() => usePage().props.auth?.user?.roles?.some(r => ['super-admin', 'school-admin'].includes(r)));

const update = (leaveId, status) => {
  if (confirm(`Are you sure you want to ${status} this leave request?`)) {
    router.post(route('hive.leaves.update', leaveId), { status });
  }
};

const statusClass = (status) => {
  switch (status) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    case 'approved':
      return 'bg-green-100 text-green-800';
    case 'rejected':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};
</script>