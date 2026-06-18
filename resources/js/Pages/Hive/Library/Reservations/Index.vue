<script setup>
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { BookmarkIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  reservations: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-ZA');
};

const fulfillReservation = (id) => {
  router.patch(route('library.reservations.fulfill', id));
};

const cancelReservation = (id) => {
  if (confirm('Cancel this reservation?')) {
    router.patch(route('library.reservations.cancel', id));
  }
};

const statusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    fulfilled: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
    expired: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  };
  return classes[status] || classes.pending;
};
</script>

<template>
  <HiveLayout title="Reservations" description="Manage book reservations">
    <div class="mb-5 flex flex-wrap gap-3 items-center">
      <select v-model="filters.status" @change="router.get(route('library.reservations.index'), { status: filters.status }, { preserveState: true })"
        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="fulfilled">Fulfilled</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Book</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Member</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Reserved</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Expires</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="reservations.data.length === 0">
            <td colspan="6" class="px-6 py-12 text-center text-gray-500">No reservations found</td>
          </tr>
          <tr v-for="res in reservations.data" :key="res.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20">
            <td class="px-6 py-4">
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ res.book?.title }}</p>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">
              <span class="text-gray-600 dark:text-gray-400">{{ res.user?.name }}</span>
            </td>
            <td class="px-6 py-4">
              <span class="text-gray-500">{{ formatDate(res.reserved_at) }}</span>
            </td>
            <td class="px-6 py-4">
              <span class="text-gray-500">{{ formatDate(res.expires_at) }}</span>
            </td>
            <td class="px-6 py-4">
              <span :class="statusClass(res.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ res.status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2" v-if="res.status === 'pending'">
                <button @click="fulfillReservation(res.id)"
                  class="p-1 text-gray-500 hover:text-green-600" title="Fulfill (Loan Book)">
                  <CheckIcon class="w-5 h-5" />
                </button>
                <button @click="cancelReservation(res.id)"
                  class="p-1 text-gray-500 hover:text-red-600" title="Cancel">
                  <XMarkIcon class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :pagination="reservations" />
    </div>
  </HiveLayout>
</template>