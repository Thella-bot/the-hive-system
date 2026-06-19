<script setup>
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import {
  CurrencyDollarIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  DocumentTextIcon,
  ChartBarIcon,
  FolderIcon,
  BanknotesIcon,
  CreditCardIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  academicYear: { type: [String, Number], default: () => new Date().getFullYear() },
  income: { type: Object, default: () => ({ total: 0, monthly: {} }) },
  expenses: { type: Object, default: () => ({ total: 0, monthly: {} }) },
  invoices: { type: Object, default: () => ({}) },
  budget: { type: Object, default: () => ({}) },
  metrics: { type: Object, default: () => ({}) },
  expensesByCategory: { type: Array, default: () => [] },
  recentPayments: { type: Array, default: () => [] },
  recentExpenses: { type: Array, default: () => [] },
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-LS', { style: 'currency', currency: 'ZAR' }).format(amount || 0);
};

const formatPercent = (value) => {
  return new Intl.NumberFormat('en-US', { style: 'percent', minimumFractionDigits: 1 }).format((value || 0) / 100);
};
</script>

<template>
  <HiveLayout title="Financial Dashboard" description="Overview of financial performance">
    <template #header-actions>
      <span class="text-sm text-gray-500 dark:text-gray-400">Academic Year: {{ academicYear }}</span>
    </template>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Net Position -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Position</p>
            <p class="text-2xl font-bold mt-1" :class="metrics.net_position >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
              {{ formatCurrency(metrics.net_position) }}
            </p>
          </div>
          <div :class="metrics.net_position >= 0 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30'" class="p-3 rounded-lg">
            <CurrencyDollarIcon :class="metrics.net_position >= 0 ? 'w-6 h-6 text-green-600' : 'w-6 h-6 text-red-600'" />
          </div>
        </div>
      </div>

      <!-- Collection Rate -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Collection Rate</p>
            <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-gray-100">{{ metrics.collection_rate }}%</p>
          </div>
          <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-lg">
            <BanknotesIcon class="w-6 h-6 text-blue-600" />
          </div>
        </div>
        <div class="mt-3">
          <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full transition-all" :style="{ width: `${Math.min(metrics.collection_rate, 100)}%` }"></div>
          </div>
        </div>
      </div>

      <!-- Total Income -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Income</p>
            <p class="text-2xl font-bold mt-1 text-green-600 dark:text-green-400">{{ formatCurrency(income.total) }}</p>
          </div>
          <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-lg">
            <ArrowUpIcon class="w-6 h-6 text-green-600" />
          </div>
        </div>
      </div>

      <!-- Total Expenses -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</p>
            <p class="text-2xl font-bold mt-1 text-red-600 dark:text-red-400">{{ formatCurrency(expenses.total) }}</p>
          </div>
          <div class="bg-red-100 dark:bg-red-900/30 p-3 rounded-lg">
            <ArrowDownIcon class="w-6 h-6 text-red-600" />
          </div>
        </div>
      </div>
    </div>

    <!-- Invoice Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center gap-3">
          <div class="bg-amber-100 dark:bg-amber-900/30 p-2 rounded-lg">
            <DocumentTextIcon class="w-5 h-5 text-amber-600" />
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Invoiced</p>
            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(invoices.total_invoiced) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center gap-3">
          <div class="bg-green-100 dark:bg-green-900/30 p-2 rounded-lg">
            <BanknotesIcon class="w-5 h-5 text-green-600" />
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Collected</p>
            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(invoices.total_collected) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center gap-3">
          <div class="bg-red-100 dark:bg-red-900/30 p-2 rounded-lg">
            <ClockIcon class="w-5 h-5 text-red-600" />
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Overdue</p>
            <p class="text-lg font-semibold text-red-600 dark:text-red-400">{{ invoices.overdue_count }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Budget Overview -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Budget Overview</h3>
        <Link :href="route('finance.budgets.index')" class="text-sm text-amber-600 hover:text-amber-700">View All</Link>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Allocated</p>
          <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(budget.total_allocated) }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Spent</p>
          <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(budget.total_spent) }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-500 dark:text-gray-400">Utilization</p>
          <div class="flex items-center gap-2">
            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ budget.utilization }}%</p>
            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 max-w-[100px]">
              <div class="bg-amber-600 h-2 rounded-full transition-all" :style="{ width: `${Math.min(budget.utilization, 100)}%` }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Payments</h3>
        <div v-if="recentPayments.length === 0" class="text-center py-8 text-gray-500">
          No recent payments
        </div>
        <div v-else class="space-y-3">
          <div v-for="payment in recentPayments" :key="payment.id" class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ payment.user?.name }}</p>
              <p class="text-xs text-gray-500">{{ payment.payment_date }}</p>
            </div>
            <p class="text-sm font-semibold text-green-600">{{ formatCurrency(payment.amount) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Expenses</h3>
        <div v-if="recentExpenses.length === 0" class="text-center py-8 text-gray-500">
          No recent expenses
        </div>
        <div v-else class="space-y-3">
          <div v-for="expense in recentExpenses" :key="expense.id" class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ expense.category?.name || 'Uncategorized' }}</p>
              <p class="text-xs text-gray-500">{{ expense.expense_date }}</p>
            </div>
            <p class="text-sm font-semibold text-red-600">{{ formatCurrency(expense.amount) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Links -->
    <div class="mt-6 flex flex-wrap gap-3">
      <Link :href="route('finance.invoices.index')" class="inline-flex items-center gap-2 bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 text-amber-800 dark:text-amber-400 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <DocumentTextIcon class="w-4 h-4" />
        Invoices
      </Link>
      <Link :href="route('finance.payments.index')" class="inline-flex items-center gap-2 bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 text-amber-800 dark:text-amber-400 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <BanknotesIcon class="w-4 h-4" />
        Payments
      </Link>
      <Link :href="route('finance.expenses.index')" class="inline-flex items-center gap-2 bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 text-amber-800 dark:text-amber-400 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <CreditCardIcon class="w-4 h-4" />
        Expenses
      </Link>
      <Link :href="route('finance.reports.income')" class="inline-flex items-center gap-2 bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 text-amber-800 dark:text-amber-400 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <ArrowDownIcon class="w-4 h-4" />
        Income Report
      </Link>
      <Link :href="route('finance.reports.expenses')" class="inline-flex items-center gap-2 bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 text-amber-800 dark:text-amber-400 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <ArrowUpIcon class="w-4 h-4" />
        Expense Report
      </Link>
      <Link :href="route('finance.reports.ageAnalysis')" class="inline-flex items-center gap-2 bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 text-amber-800 dark:text-amber-400 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <ClockIcon class="w-4 h-4" />
        Age Analysis
      </Link>
    </div>
  </HiveLayout>
</template>