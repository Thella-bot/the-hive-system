<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import {
  ArrowLeftIcon,
  ArrowDownTrayIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  invoices: { type: Object, required: true },
  byAgeBracket: { type: Object, default: () => ({}) },
  totalOverdue: { type: Number, default: 0 },
  academicYear: { type: [String, Number], default: new Date().getFullYear() },
});

const search = ref('');

const applySearch = () => {
  router.get(route('finance.reports.ageAnalysis'), {
    search: search.value,
  }, { preserveState: true, replace: true });
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-LS', { style: 'currency', currency: 'ZAR' }).format(amount || 0);
};

const statusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    partial: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    overdue: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  };
  return classes[status] || classes.pending;
};

const ageBracketClass = (bracket) => {
  const classes = {
    'current': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    '1-30 days': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    '31-60 days': 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
    '61-90 days': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    '90+ days': 'bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-200',
  };
  return classes[bracket] || classes.pending;
};
</script>

<template>
  <HiveLayout title="Age Analysis" description="Overdue invoices by age bracket">
    <template #header-actions>
      <Link :href="route('finance.reports.dashboard')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Dashboard
      </Link>
    </template>

    <div class="mb-6 flex flex-wrap items-center gap-4">
      <div class="flex-1 min-w-[200px]">
        <SearchInput v-model="search" placeholder="Search by student name..." @search="applySearch" />
      </div>
      <span class="text-sm text-gray-500 dark:text-gray-400">Academic Year: {{ academicYear }}</span>
    </div>

    <!-- Summary by Age Bracket -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Current</p>
        <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ byAgeBracket['current']?.count || 0 }}</p>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ formatCurrency(byAgeBracket['current']?.amount || 0) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">1-30 Days</p>
        <p class="text-lg font-bold text-yellow-600 dark:text-yellow-400">{{ byAgeBracket['1-30 days']?.count || 0 }}</p>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ formatCurrency(byAgeBracket['1-30 days']?.amount || 0) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">31-60 Days</p>
        <p class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ byAgeBracket['31-60 days']?.count || 0 }}</p>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ formatCurrency(byAgeBracket['31-60 days']?.amount || 0) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">61-90 Days</p>
        <p class="text-lg font-bold text-red-600 dark:text-red-400">{{ byAgeBracket['61-90 days']?.count || 0 }}</p>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ formatCurrency(byAgeBracket['61-90 days']?.amount || 0) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">90+ Days</p>
        <p class="text-lg font-bold text-red-700 dark:text-red-300">{{ byAgeBracket['90+ days']?.count || 0 }}</p>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ formatCurrency(byAgeBracket['90+ days']?.amount || 0) }}</p>
      </div>
    </div>

    <!-- Total Overdue -->
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-6">
      <div class="flex items-center gap-3">
        <ExclamationTriangleIcon class="w-8 h-8 text-red-600" />
        <div>
          <p class="text-sm text-red-600 dark:text-red-400">Total Overdue</p>
          <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ formatCurrency(totalOverdue) }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Student</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Invoice #</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Due Date</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Amount</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Age</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="invoices.data.length === 0">
            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No overdue invoices</td>
          </tr>
          <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
            <td class="px-6 py-4">
              <Link :href="route('finance.reports.studentLedger', invoice.user_id)" class="text-amber-600 hover:text-amber-700 font-medium">
                {{ invoice.user?.name || 'N/A' }}
              </Link>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">
              <Link :href="route('finance.invoices.show', invoice.id)" class="text-gray-500 hover:text-amber-600">
                {{ invoice.invoice_number }}
              </Link>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span class="text-gray-600 dark:text-gray-400">{{ invoice.due_date || '—' }}</span>
            </td>
            <td class="px-6 py-4">
              <span class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(invoice.amount) }}</span>
            </td>
            <td class="px-6 py-4">
              <span :class="statusClass(invoice.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ invoice.status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span :class="ageBracketClass(invoice.age_bracket)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                {{ invoice.age_bracket }}
              </span>
            </td>
            <td class="px-6 py-4">
              <Link :href="route('finance.invoices.show', invoice.id)" class="text-gray-500 hover:text-amber-600">
                View
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :pagination="invoices" />
    </div>
  </HiveLayout>
</template>