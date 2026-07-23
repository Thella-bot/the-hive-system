<template>
  <HiveLayout title="Applications" description="Manage student applications">
    <!-- Filter Bar -->
    <div class="flex items-center justify-between mb-5 gap-4 flex-wrap">
      <div class="flex items-center gap-2">
        <button
          v-for="f in filters"
          :key="f.value"
          @click="setFilter(f.value)"
          class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
          :class="filter === f.value
            ? 'bg-amber-600 text-white'
            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
        >
          {{ f.label }}
        </button>
      </div>
      <p class="text-xs text-gray-400 dark:text-gray-500">{{ applicationCount }} application{{ applicationCount === 1 ? '' : 's' }}</p>
    </div>

    <!-- Applications Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Applicant</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Programme</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Applied On</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="!applications.data?.length">
            <td colspan="5">
              <EmptyState type="document" title="No applications found" />
            </td>
          </tr>
          <tr v-for="application in (applications.data ?? [])" :key="application.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-amber-100 dark:bg-amber-900 rounded-full flex items-center justify-center text-amber-800 dark:text-amber-200 font-semibold text-sm">
                  {{ (application.user?.name || application.name || '?').charAt(0) }}
                </div>
                <div>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ application.user?.name || application.name || 'Applicant' }}</p>
                  <p class="text-xs text-gray-400 dark:text-gray-500">{{ application.user?.email || application.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4">
              <p class="text-gray-900 dark:text-gray-100 font-medium">{{ application.programme?.name || 'Unknown' }}</p>
              <p v-if="application.variant" class="text-xs text-amber-600 dark:text-amber-400">{{ application.variant.label }}</p>
            </td>
            <td class="px-6 py-4">
              <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="statusClass(application.status)">
                {{ statusLabels[application.status] ?? application.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 hidden md:table-cell">{{ formatDate(application.created_at) }}</td>
            <td class="px-6 py-4">
              <Link
                :href="route('hive.applications.show', { application: application.id })"
                class="text-amber-600 hover:text-amber-700 dark:text-amber-400 font-medium text-xs"
              >
                View
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination v-if="applicationCount > 0 && applications.meta" :links="applications.links" :meta="applications.meta" />
  </HiveLayout>
</template>

<script setup>
import HiveLayout from '@/Layouts/HiveLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Pagination from '@/Components/Pagination.vue';
import { computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
  applications: {
    type: Object,
    default: () => ({ data: [], meta: null, links: [] }),
  },
  canUpdate: {
    type: Boolean,
    default: false,
  },
  filter: {
    type: String,
    default: 'pending',
  },
});

const applicationCount = computed(() => props.applications?.meta?.total ?? 0);

const filters = [
  { label: 'Pending', value: 'pending' },
  { label: 'Approved', value: 'approved' },
  { label: 'Rejected', value: 'rejected' },
  { label: 'All', value: 'all' },
];

const setFilter = (f) => {
  router.get(route('hive.applications.index', { filter: f }), {
    preserveScroll: true,
  });
};

const formatDate = (date) => date ? dayjs(date).fromNow() : '—';

const statusClass = (status) => {
  switch (status) {
    case 'pending':
      return 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300';
    case 'approved':
      return 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300';
    case 'rejected':
      return 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300';
    default:
      return 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
  }
};

const statusLabels = {
  pending: 'Pending',
  approved: 'Approved',
  rejected: 'Rejected',
};
</script>