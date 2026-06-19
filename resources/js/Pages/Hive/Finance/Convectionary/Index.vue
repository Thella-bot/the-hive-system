<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import {
  PlusIcon,
  EyeIcon,
  CurrencyDollarIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  incomes: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  sources: { type: Object, default: () => ({}) },
  statuses: { type: Array, default: () => [] },
  totalReceived: { type: Number, default: 0 },
  totalsBySource: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const source = ref(props.filters.source ?? '');
const status = ref(props.filters.status ?? '');

const applyFilters = () => router.get(route('finance.convectionary.index'), {
  search: search.value,
  source: source.value,
  status: status.value,
}, { preserveState: true, replace: true });

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-LS', { style: 'currency', currency: 'ZAR' }).format(amount || 0);
};

const statusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    received: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
  };
  return classes[status] || classes.pending;
};

const sourceLabel = (key) => {
  return props.sources[key] || key;
};
</script>

<template>
  <HiveLayout title="Convectionary Income" description="Track convectionary income streams">
    <template #header-actions>
      <Link :href="route('finance.convectionary.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        Record Income
      </Link>
    </template>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Received</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(totalReceived) }}</p>
      </div>
      <div v-for="(label, key) in sources" :key="key" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ label }}</p>
        <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(totalsBySource[key]) }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-5 flex flex-wrap gap-3 items-center justify-between">
      <div class="flex flex-wrap gap-3 items-center">
        <div class="max-w-xs">
          <SearchInput v-model="search" @search="applyFilters" placeholder="Search..." />
        </div>
        <select
          v-model="source"
          @change="applyFilters"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
        >
          <option value="">All Sources</option>
          <option v-for="(label, key) in sources" :key="key" :value="key">{{ label }}</option>
        </select>
        <select
          v-model="status"
          @change="applyFilters"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
        >
          <option value="">All Statuses</option>
          <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Reference</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Source</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Amount</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Date</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="incomes.data.length === 0">
            <td colspan="6" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center">
                <div class="w-16 h-16 mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                  <CurrencyDollarIcon class="w-8 h-8 text-gray-400" />
                </div>
                <p class="text-gray-500 dark:text-gray-400">No convectionary income recorded</p>
              </div>
            </td>
          </tr>
          <tr v-for="income in incomes.data" :key="income.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
            <td class="px-6 py-4">
              <div>
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ income.reference }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ income.description }}</p>
              </div>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">
              <span class="text-gray-600 dark:text-gray-400">{{ sourceLabel(income.source) }}</span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(income.amount) }}</span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span class="text-gray-500 dark:text-gray-400">{{ income.income_date }}</span>
            </td>
            <td class="px-6 py-4">
              <span :class="statusClass(income.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ income.status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <Link :href="route('finance.convectionary.show', income.id)" class="p-1 text-gray-500 hover:text-amber-600">
                  <EyeIcon class="w-5 h-5" />
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :pagination="incomes" />
    </div>
  </HiveLayout>
</template>