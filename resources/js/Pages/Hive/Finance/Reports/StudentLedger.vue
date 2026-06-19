<script setup>
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import {
  ArrowLeftIcon,
  DocumentTextIcon,
  CurrencyDollarIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  user: { type: Object, required: true },
  invoices: { type: Array, default: () => [] },
  payments: { type: Array, default: () => [] },
  summary: { type: Object, default: () => ({}) },
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-LS', { style: 'currency', currency: 'ZAR' }).format(amount || 0);
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
  <HiveLayout :title="`Student Ledger: ${user.name}`" :description="Invoice and payment history">
    <template #header-actions>
      <Link :href="route('finance.reports.ageAnalysis')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Age Analysis
      </Link>
    </template>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-3">
          <DocumentTextIcon class="w-8 h-8 text-amber-600" />
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Invoiced</p>
            <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(summary.total_invoiced) }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-3">
          <CurrencyDollarIcon class="w-8 h-8 text-green-600" />
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Paid</p>
            <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(summary.total_paid) }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-3">
          <CurrencyDollarIcon class="w-8 h-8" :class="summary.balance > 0 ? 'text-red-600' : 'text-green-600'" />
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Balance</p>
            <p class="text-xl font-bold" :class="summary.balance > 0 ? 'text-red-600' : 'text-green-600 dark:text-green-400'">
              {{ formatCurrency(summary.balance) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Invoices -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
          <h3 class="font-semibold text-gray-900 dark:text-gray-100">Invoices</h3>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-900/50">
            <tr>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Invoice #</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Date</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Amount</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-if="invoices.length === 0">
              <td colspan="4" class="px-6 py-8 text-center text-gray-500">No invoices</td>
            </tr>
            <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
              <td class="px-6 py-3">
                <Link :href="route('finance.invoices.show', invoice.id)" class="text-amber-600 hover:text-amber-700">
                  {{ invoice.invoice_number }}
                </Link>
              </td>
              <td class="px-6 py-3 text-gray-600 dark:text-gray-400">{{ invoice.created_at?.split('T')[0] }}</td>
              <td class="px-6 py-3 font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(invoice.amount) }}</td>
              <td class="px-6 py-3">
                <span :class="statusClass(invoice.status)" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize">
                  {{ invoice.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Payments -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
          <h3 class="font-semibold text-gray-900 dark:text-gray-100">Payments</h3>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-900/50">
            <tr>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Date</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Reference</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Amount</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-if="payments.length === 0">
              <td colspan="4" class="px-6 py-8 text-center text-gray-500">No payments</td>
            </tr>
            <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
              <td class="px-6 py-3 text-gray-600 dark:text-gray-400">{{ payment.payment_date }}</td>
              <td class="px-6 py-3 text-gray-500 dark:text-gray-400">{{ payment.reference || '—' }}</td>
              <td class="px-6 py-3 font-medium text-green-600 dark:text-green-400">{{ formatCurrency(payment.amount) }}</td>
              <td class="px-6 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 capitalize">
                  {{ payment.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </HiveLayout>
</template>