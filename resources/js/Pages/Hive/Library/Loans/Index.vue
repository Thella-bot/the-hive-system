<script setup>
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ClockIcon, ArrowPathIcon, CheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  loans: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-ZA');
};

const returnBook = (loanId) => {
  router.patch(route('library.loans.return', loanId));
};

const renewBook = (loanId) => {
  router.patch(route('library.loans.renew', loanId));
};

const statusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    returned: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    overdue: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    lost: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
  };
  return classes[status] || classes.active;
};

const isOverdue = (loan) => {
  return loan.status === 'active' && new Date(loan.due_date) < new Date();
};
</script>

<template>
  <HiveLayout title="Book Loans" description="Manage book loans">
    <div class="mb-5 flex flex-wrap gap-3 items-center">
      <select v-model="filters.status" @change="router.get(route('library.loans.index'), { status: filters.status }, { preserveState: true })"
        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="returned">Returned</option>
        <option value="overdue">Overdue</option>
      </select>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Book</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Member</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Loan Date</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Due Date</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="loans.data.length === 0">
            <td colspan="6" class="px-6 py-12 text-center text-gray-500">No loans found</td>
          </tr>
          <tr v-for="loan in loans.data" :key="loan.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20">
            <td class="px-6 py-4">
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ loan.book?.title }}</p>
              <p class="text-xs text-gray-500">{{ loan.book?.author }}</p>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">
              <span class="text-gray-600 dark:text-gray-400">{{ loan.user?.name }}</span>
            </td>
            <td class="px-6 py-4">
              <span class="text-gray-500">{{ formatDate(loan.loan_date) }}</span>
            </td>
            <td class="px-6 py-4">
              <span :class="isOverdue(loan) ? 'text-red-600 font-medium' : 'text-gray-500'">
                {{ formatDate(loan.due_date) }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span :class="statusClass(loan.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ loan.status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2" v-if="loan.status === 'active'">
                <button @click="renewBook(loan.id)" :disabled="loan.renewal_count >= 2"
                  class="p-1 text-gray-500 hover:text-amber-600 disabled:opacity-50" title="Renew">
                  <ArrowPathIcon class="w-5 h-5" />
                </button>
                <button @click="returnBook(loan.id)"
                  class="p-1 text-gray-500 hover:text-green-600" title="Mark Returned">
                  <CheckIcon class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :pagination="loans" />
    </div>
  </HiveLayout>
</template>