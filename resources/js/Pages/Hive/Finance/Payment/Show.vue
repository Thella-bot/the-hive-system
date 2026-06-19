<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import InputError from '@/Components/InputError.vue';
import {
  ArrowLeftIcon,
  CheckCircleIcon,
  XCircleIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  payment: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-LS', { style: 'currency', currency: 'ZAR' }).format(amount || 0);
};

const statusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    failed: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    refunded: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
  };
  return classes[status] || classes.pending;
};

const methodLabel = (method) => {
  return method?.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) || '';
};

const verifyPayment = () => {
  if (confirm('Verify this payment as completed?')) {
    router.patch(route('finance.payments.verify', props.payment.id));
  }
};

const deletePayment = () => {
  if (confirm('Are you sure you want to delete this payment?')) {
    router.delete(route('finance.payments.destroy', props.payment.id));
  }
};
</script>

<template>
  <HiveLayout :title="`Payment ${payment.payment_number || payment.id}`" :description="`Payment for ${payment.user?.name}`">
    <template #header-actions>
      <Link :href="route('finance.payments.index')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Payments
      </Link>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Details -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Payment Details</h3>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500 dark:text-gray-400">Payment Number</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ payment.payment_number || '—' }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Amount</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(payment.amount) }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Payment Method</p>
              <p class="font-medium text-gray-900 dark:text-gray-100 capitalize">{{ methodLabel(payment.payment_method) }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Payment Date</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ payment.payment_date }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-gray-500 dark:text-gray-400">Reference</p>
              <p class="text-gray-900 dark:text-gray-100">{{ payment.reference_number || '—' }}</p>
            </div>
            <div v-if="payment.notes" class="col-span-2">
              <p class="text-gray-500 dark:text-gray-400">Notes</p>
              <p class="text-gray-900 dark:text-gray-100">{{ payment.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Invoice Info -->
        <div v-if="payment.invoice" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Invoice Details</h3>
          <Link :href="route('finance.invoices.show', payment.invoice.id)" class="block hover:bg-gray-50 dark:hover:bg-gray-700 -m-2 p-2 rounded-lg">
            <p class="font-medium text-gray-900 dark:text-gray-100">{{ payment.invoice.invoice_number }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatCurrency(payment.invoice.amount) }} &middot; {{ payment.invoice.status }}</p>
          </Link>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Status Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Status</h3>
          <div class="space-y-4">
            <div>
              <span :class="statusClass(payment.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize">
                {{ payment.status }}
              </span>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Recorded By</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ payment.recorder?.name || '—' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Recorded At</p>
              <p class="text-sm text-gray-900 dark:text-gray-100">{{ payment.created_at }}</p>
            </div>
          </div>
        </div>

        <!-- Student Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Student</h3>
          <p class="font-medium text-gray-900 dark:text-gray-100">{{ payment.user?.name }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ payment.user?.email }}</p>
        </div>

        <!-- Actions -->
        <div v-if="payment.status === 'pending'" class="space-y-3">
          <button
            @click="verifyPayment"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium"
          >
            <CheckCircleIcon class="w-5 h-5" />
            Verify Payment
          </button>
          <button
            @click="deletePayment"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg font-medium hover:bg-red-50 dark:hover:bg-red-900/20"
          >
            <TrashIcon class="w-5 h-5" />
            Delete Payment
          </button>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>