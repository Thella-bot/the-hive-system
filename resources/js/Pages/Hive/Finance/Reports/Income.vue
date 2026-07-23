<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import {
  ArrowLeftIcon,
  ArrowDownTrayIcon,
  CurrencyDollarIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  payments: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  totalAmount: { type: Number, default: 0 },
  methods: { type: Array, default: () => [] },
});

const search = ref(props.filters.search ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const academicYear = ref(props.filters.academic_year ?? new Date().getFullYear());
const paymentMethod = ref(props.filters.payment_method ?? '');

const applyFilters = () => {
  router.get(route('finance.reports.income'), {
    search: search.value,
    date_from: dateFrom.value,
    date_to: dateTo.value,
    academic_year: academicYear.value,
    payment_method: paymentMethod.value,
  }, { preserveState: true, replace: true });
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-LS', { style: 'currency', currency: 'ZAR' }).format(amount || 0);
};

const methodLabel = (method) => {
  return method?.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) || '';
};

const exportReport = () => {
  window.location.href = route('finance.reports.income') + '?' + new URLSearchParams({
    ...props.filters,
    export: 'csv',
  }).toString();
};
</script>

<template>
  <HiveLayout title="Income Report" description="Payments received">
    <template #header-actions>
      <Link :href="route('finance.reports.dashboard')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Dashboard
      </Link>
    </template>

    <div class="mb-6 flex flex-wrap items-center gap-4">
      <div class="flex-1 min-w-[200px]">
        <SearchInput v-model="search" placeholder="Search payments..." @search="applyFilters" />
      </div>
      <select v-model="academicYear" @change="applyFilters" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
        <option :value="null">All Years</option>
        <option :value="2026">2026</option>
        <option :value="2025">2025</option>
        <option :value="2024">2024</option>
      </select>
      <select v-model="paymentMethod" @change="applyFilters" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
        <option value="">All Methods</option>
        <option v-for="method in methods" :key="method" :value="method">{{ methodLabel(method) }}</option>
      </select>
      <input v-model="dateFrom" type="date" @change="applyFilters" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" placeholder="From Date" />
      <input v-model="dateTo" type="date" @change="applyFilters" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" placeholder="To Date" />
      <button @click="exportReport"         class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium">
        <ArrowDownTrayIcon class="w-5 h-5" />
        Export
      </button>
    </div>

    <!-- Summary -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
      <div class="flex items-center gap-3">
        <CurrencyDollarIcon class="w-8 h-8 text-green-600" />
        <div>
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Received</p>
          <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(totalAmount) }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Date</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Reference</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Student</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Method</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Amount</th>
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
          <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
            <td class="px-6 py-4">
              <span class="text-gray-900 dark:text-gray-100">{{ payment.payment_date }}</span>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">
              <span class="text-gray-500 dark:text-gray-400">{{ payment.reference }}</span>
            </td>
            <td class="px-6 py-4">
              <Link :href="route('finance.reports.studentLedger', payment.user_id)" class="text-amber-600 hover:text-amber-700 font-medium">
                {{ payment.user?.name || 'N/A' }}
              </Link>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span class="capitalize text-gray-600 dark:text-gray-400">{{ methodLabel(payment.payment_method) }}</span>
            </td>
            <td class="px-6 py-4">
              <span class="font-medium text-green-600 dark:text-green-400">{{ formatCurrency(payment.amount) }}</span>
            </td>
            <td class="px-6 py-4">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 capitalize">
                {{ payment.status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <Link :href="route('finance.payments.show', payment.id)" class="text-gray-500 hover:text-amber-600">
                View
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :pagination="payments" />
    </div>
  </HiveLayout>
</template>