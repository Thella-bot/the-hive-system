<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import {
  PlusIcon,
  MagnifyingGlassIcon,
  EyeIcon,
  DocumentTextIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  invoices: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  statuses: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const academicYear = ref(props.filters.academic_year ?? '');

const applyFilters = () => router.get(route('finance.invoices.index'), {
  search: search.value,
  status: status.value,
  academic_year: academicYear.value,
}, { preserveState: true, replace: true });

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-LS', { style: 'currency', currency: 'ZAR' }).format(amount);
};

const statusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    partial: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    paid: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    overdue: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
  };
  return classes[status] || classes.pending;
};
</script>

<template>
  <HiveLayout title="Invoices" description="Manage student invoices and billing">
    <template #header-actions>
      <Link :href="route('finance.invoices.generate')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <DocumentTextIcon class="w-4 h-4" />
        Generate Invoices
      </Link>
    </template>

    <div class="mb-5 flex flex-wrap gap-3 items-center justify-between">
      <div class="flex flex-wrap gap-3 items-center">
        <div class="max-w-xs">
          <SearchInput v-model="search" @search="applyFilters" placeholder="Search invoices..." />
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
          v-model="academicYear"
          @change="applyFilters"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
        >
          <option value="">All Years</option>
          <option v-for="y in academicYears" :key="y" :value="y">{{ y }}</option>
        </select>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Invoice</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Student</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Type</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Amount</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Due Date</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="invoices.data.length === 0">
            <td colspan="7" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center">
                <div class="w-16 h-16 mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                  <DocumentTextIcon class="w-8 h-8 text-gray-400" />
                </div>
                <p class="text-gray-500 dark:text-gray-400">No invoices found</p>
              </div>
            </td>
          </tr>
          <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
            <td class="px-6 py-4">
              <div>
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ invoice.invoice_number }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ invoice.academic_year }} Semester {{ invoice.semester }}</p>
              </div>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">
              <span class="text-gray-600 dark:text-gray-400">{{ invoice.user?.name }}</span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span class="capitalize text-gray-600 dark:text-gray-400">{{ invoice.type }}</span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(invoice.amount) }}</span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span :class="invoice.is_overdue ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400'">
                {{ invoice.due_date || '—' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span :class="statusClass(invoice.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ invoice.status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <Link :href="route('finance.invoices.show', invoice.id)" class="p-1 text-gray-500 hover:text-amber-600">
                  <EyeIcon class="w-5 h-5" />
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :pagination="invoices" />
    </div>
  </HiveLayout>
</template>