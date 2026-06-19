<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import {
  ArrowLeftIcon,
  ArrowDownTrayIcon,
  CurrencyDollarIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  expenses: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  totalAmount: { type: Number, default: 0 },
  statuses: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
});

const search = ref(props.filters.search ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const academicYear = ref(props.filters.academic_year ?? new Date().getFullYear());
const status = ref(props.filters.status ?? '');
const categoryId = ref(props.filters.category_id ?? '');

const applyFilters = () => {
  router.get(route('finance.reports.expenses'), {
    search: search.value,
    date_from: dateFrom.value,
    date_to: dateTo.value,
    academic_year: academicYear.value,
    status: status.value,
    category_id: categoryId.value,
  }, { preserveState: true, replace: true });
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-LS', { style: 'currency', currency: 'ZAR' }).format(amount || 0);
};

const statusClass = (s) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    approved: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    paid: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
  };
  return classes[s] || classes.pending;
};

const exportReport = () => {
  window.location.href = route('finance.reports.expenses') + '?' + new URLSearchParams({
    ...props.filters,
    export: 'csv',
  }).toString();
};
</script>

<template>
  <HiveLayout title="Expense Report" description="Expenses by category and status">
    <template #header-actions>
      <Link :href="route('finance.reports.dashboard')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Dashboard
      </Link>
    </template>

    <div class="mb-6 flex flex-wrap items-center gap-4">
      <div class="flex-1 min-w-[200px]">
        <SearchInput v-model="search" placeholder="Search expenses..." @search="applyFilters" />
      </div>
      <select v-model="academicYear" @change="applyFilters" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
        <option :value="null">All Years</option>
        <option :value="2026">2026</option>
        <option :value="2025">2025</option>
        <option :value="2024">2024</option>
      </select>
      <select v-model="status" @change="applyFilters" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
        <option value="">All Statuses</option>
        <option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s }}</option>
      </select>
      <select v-model="categoryId" @change="applyFilters" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
        <option value="">All Categories</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>
      <input v-model="dateFrom" type="date" @change="applyFilters" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" placeholder="From Date" />
      <input v-model="dateTo" type="date" @change="applyFilters" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" placeholder="To Date" />
      <button @click="exportReport" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium">
        <ArrowDownTrayIcon class="w-5 h-5" />
        Export
      </button>
    </div>

    <!-- Summary -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
      <div class="flex items-center gap-3">
        <CurrencyDollarIcon class="w-8 h-8 text-red-600" />
        <div>
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Expenses</p>
          <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ formatCurrency(totalAmount) }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Date</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Number</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Description</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Category</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Amount</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="expenses.data.length === 0">
            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No expenses found</td>
          </tr>
          <tr v-for="expense in expenses.data" :key="expense.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
            <td class="px-6 py-4">
              <span class="text-gray-900 dark:text-gray-100">{{ expense.expense_date }}</span>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">
              <span class="text-gray-500 dark:text-gray-400">{{ expense.expense_number }}</span>
            </td>
            <td class="px-6 py-4">
              <Link :href="route('finance.expenses.show', expense.id)" class="text-gray-900 dark:text-gray-100 hover:text-amber-600">
                {{ expense.description }}
              </Link>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span class="text-gray-600 dark:text-gray-400">{{ expense.category?.name || '—' }}</span>
            </td>
            <td class="px-6 py-4">
              <span class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(expense.amount) }}</span>
            </td>
            <td class="px-6 py-4">
              <span :class="statusClass(expense.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ expense.status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <Link :href="route('finance.expenses.show', expense.id)" class="text-gray-500 hover:text-amber-600">
                View
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :pagination="expenses" />
    </div>
  </HiveLayout>
</template>