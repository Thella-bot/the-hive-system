<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import {
  PlusIcon,
  MagnifyingGlassIcon,
  PencilSquareIcon,
  EyeIcon,
  TrashIcon,
  FolderIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  budgets: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  statuses: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
});

const search = ref(props.filters.search ?? '');

const applyFilters = () => router.get(route('finance.budgets.index'),
  { search: search.value },
  { preserveState: true, replace: true }
);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-LS', { style: 'currency', currency: 'ZAR' }).format(amount || 0);
};

const statusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
    active: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    closed: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  };
  return classes[status] || classes.draft;
};
</script>

<template>
  <HiveLayout title="Budgets" description="Manage department budgets and allocations">
    <template #header-actions>
      <Link :href="route('finance.reports.dashboard')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <FolderIcon class="w-4 h-4" />
        Financial Reports
      </Link>
    </template>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Budget</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Academic Year</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Allocated</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Spent</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Available</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="budgets.data.length === 0">
            <td colspan="7" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center">
                <div class="w-16 h-16 mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                  <FolderIcon class="w-8 h-8 text-gray-400" />
                </div>
                <p class="text-gray-500 dark:text-gray-400">No budgets found</p>
              </div>
            </td>
          </tr>
          <tr v-for="budget in budgets.data" :key="budget.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
            <td class="px-6 py-4">
              <div>
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ budget.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ budget.category?.name || '—' }}</p>
              </div>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">
              <span class="text-gray-600 dark:text-gray-400">{{ budget.academic_year }}</span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(budget.allocated_amount) }}</span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span :class="budget.is_overspent ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100'">
                {{ formatCurrency(budget.spent_amount) }}
              </span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span :class="budget.available_amount < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'" class="font-medium">
                {{ formatCurrency(budget.available_amount) }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span :class="statusClass(budget.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ budget.status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <Link :href="route('finance.budgets.show', budget.id)" class="p-1 text-gray-500 hover:text-amber-600">
                  <EyeIcon class="w-5 h-5" />
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :pagination="budgets" />
    </div>
  </HiveLayout>
</template>