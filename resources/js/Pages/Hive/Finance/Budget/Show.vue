<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import {
  ArrowLeftIcon,
  CheckCircleIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  budget: { type: Object, required: true },
});

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

const activateBudget = () => {
  if (confirm('Activate this budget? This will close any other active budgets in the same category.')) {
    router.patch(route('finance.budgets.activate', props.budget.id));
  }
};

const closeBudget = () => {
  if (confirm('Close this budget?')) {
    router.patch(route('finance.budgets.close', props.budget.id));
  }
};

const deleteBudget = () => {
  if (confirm('Are you sure you want to delete this budget?')) {
    router.delete(route('finance.budgets.destroy', props.budget.id));
  }
};
</script>

<template>
  <HiveLayout :title="budget.name" :description="`Budget for ${budget.academic_year}`">
    <template #header-actions>
      <Link :href="route('finance.budgets.index')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Budgets
      </Link>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Details -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Budget Summary -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Budget Summary</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Approved Budget</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(budget.approved_budget) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Allocated</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(budget.allocated_amount) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Spent</p>
              <p class="text-2xl font-bold" :class="budget.is_overspent ? 'text-red-600' : 'text-gray-900 dark:text-gray-100'">
                {{ formatCurrency(budget.spent_amount) }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Available</p>
              <p class="text-2xl font-bold" :class="budget.available_amount < 0 ? 'text-red-600' : 'text-green-600'">
                {{ formatCurrency(budget.available_amount) }}
              </p>
            </div>
          </div>

          <!-- Progress Bar -->
          <div>
            <div class="flex justify-between text-sm mb-2">
              <span class="text-gray-500 dark:text-gray-400">Utilization</span>
              <span class="font-medium text-gray-900 dark:text-gray-100">{{ budget.percent_used }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
              <div
                class="bg-amber-500 h-3 rounded-full transition-all"
                :style="{ width: `${Math.min(budget.percent_used, 100)}%` }"
                :class="budget.is_overspent ? 'bg-red-500' : ''"
              ></div>
            </div>
          </div>
        </div>

        <!-- Expenses List -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Expenses</h3>
          <div v-if="budget.expenses?.length === 0" class="text-center py-8 text-gray-500">
            No expenses recorded
          </div>
          <div v-else class="space-y-3">
            <div v-for="expense in budget.expenses" :key="expense.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
              <div>
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ expense.expense_number }}</p>
                <p class="text-xs text-gray-500">{{ expense.description }}</p>
              </div>
              <div class="text-right">
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(expense.amount) }}</p>
                <span :class="expense.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                  class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">
                  {{ expense.status }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Budget Details -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Budget Details</h3>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500 dark:text-gray-400">Academic Year</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ budget.academic_year }}</p>
            </div>
            <div v-if="budget.semester">
              <p class="text-gray-500 dark:text-gray-400">Semester</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ budget.semester }}</p>
            </div>
            <div v-if="budget.category">
              <p class="text-gray-500 dark:text-gray-400">Category</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ budget.category?.name }}</p>
            </div>
            <div v-if="budget.department">
              <p class="text-gray-500 dark:text-gray-400">Department</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ budget.department?.name }}</p>
            </div>
            <div v-if="budget.start_date">
              <p class="text-gray-500 dark:text-gray-400">Start Date</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ budget.start_date }}</p>
            </div>
            <div v-if="budget.end_date">
              <p class="text-gray-500 dark:text-gray-400">End Date</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ budget.end_date }}</p>
            </div>
            <div v-if="budget.notes" class="col-span-2">
              <p class="text-gray-500 dark:text-gray-400">Notes</p>
              <p class="text-gray-900 dark:text-gray-100">{{ budget.notes }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Status Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Status</h3>
          <div class="space-y-4">
            <div>
              <span :class="statusClass(budget.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize">
                {{ budget.status }}
              </span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div v-if="budget.status === 'draft'" class="space-y-3">
          <button
            @click="activateBudget"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium"
          >
            <CheckCircleIcon class="w-5 h-5" />
            Activate Budget
          </button>
          <button
            @click="deleteBudget"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg font-medium hover:bg-red-50 dark:hover:bg-red-900/20"
          >
            <XCircleIcon class="w-5 h-5" />
            Delete Budget
          </button>
        </div>

        <div v-if="budget.status === 'active'" class="space-y-3">
          <button
            @click="closeBudget"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium"
          >
            <XCircleIcon class="w-5 h-5" />
            Close Budget
          </button>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>