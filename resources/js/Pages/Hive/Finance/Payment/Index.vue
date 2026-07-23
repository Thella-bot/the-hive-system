<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
  payments: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  statuses: { type: Array, default: () => [] },
  methods: { type: Array, default: () => [] },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const paymentMethod = ref(props.filters.payment_method ?? '');

const applyFilters = () => router.get(route('finance.payments.index'), {
  search: search.value,
  status: status.value,
  payment_method: paymentMethod.value,
}, { preserveState: true, replace: true });

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-LS', { style: 'currency', currency: 'ZAR' }).format(amount);
};

const statusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    failed: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    refunded: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
  };
  return classes[status] || classes.pending;
};

const methodLabel = (method) => {
  return method?.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) || '';
};
</script>

<template>
  <HiveLayout title="Payments" description="Track and manage student payments">
    <div class="mb-5 flex flex-wrap gap-3 items-center justify-between">
      <div class="flex flex-wrap gap-3 items-center">
        <div class="max-w-xs">
          <SearchInput v-model="search" @search="applyFilters" placeholder="Search payments..." />
        </div>
        <select
          v-model="status"
          @change="applyFilters"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
        >
          <option value="">All Statuses</option>
          <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
        </select>
        <select
          v-model="paymentMethod"
          @change="applyFilters"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
        >
          <option value="">All Methods</option>
          <option v-for="m in methods" :key="m" :value="m">{{ methodLabel(m) }}</option>
        </select>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Reference</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Student</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Method</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Amount</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Date</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="payments.data.length === 0">
            <td colspan="7">
              <EmptyState type="document" title="No payments found" />
            </td>
          </tr>
          <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
            <td class="px-6 py-4">
              <div>
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ payment.payment_number || '—' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ payment.invoice?.invoice_number }}</p>
              </div>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">
              <span class="text-gray-600 dark:text-gray-400">{{ payment.user?.name }}</span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span class="capitalize text-gray-600 dark:text-gray-400">{{ methodLabel(payment.payment_method) }}</span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(payment.amount) }}</span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span class="text-gray-500 dark:text-gray-400">{{ payment.payment_date }}</span>
            </td>
            <td class="px-6 py-4">
              <span :class="statusClass(payment.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ payment.status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <Link :href="route('finance.payments.show', payment.id)" class="p-1 text-gray-500 hover:text-amber-600">
                  <EyeIcon class="w-5 h-5" />
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :pagination="payments" />
    </div>
  </HiveLayout>
</template>