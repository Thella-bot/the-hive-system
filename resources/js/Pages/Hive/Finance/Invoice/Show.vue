<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import InputError from '@/Components/InputError.vue';
import {
  ArrowLeftIcon,
  PencilSquareIcon,
  TrashIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  invoice: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
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

const showEditForm = ref(false);
const editForm = ref({ ...props.invoice });

const saveChanges = () => {
  router.patch(route('finance.invoices.update', props.invoice.id), editForm.value, {
    onSuccess: () => { showEditForm.value = false; },
  });
};

const cancelInvoice = () => {
  if (confirm('Are you sure you want to cancel this invoice?')) {
    router.patch(route('finance.invoices.update', props.invoice.id), { status: 'cancelled' });
  }
};

const markAsPaid = () => {
  if (confirm('Mark this invoice as paid?')) {
    router.patch(route('finance.invoices.update', props.invoice.id), { status: 'paid' });
  }
};
</script>

<template>
  <HiveLayout :title="`Invoice ${invoice.invoice_number}`" :description="`Invoice for ${invoice.user?.name}`">
    <template #header-actions>
      <Link :href="route('finance.invoices.index')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Invoices
      </Link>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Details -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Invoice Details</h3>
            <button
              v-if="invoice.status !== 'cancelled' && invoice.status !== 'paid'"
              @click="showEditForm = !showEditForm"
              class="text-sm text-amber-600 hover:text-amber-700"
            >
              {{ showEditForm ? 'Cancel' : 'Edit' }}
            </button>
          </div>

          <div v-if="showEditForm" class="space-y-4 mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                <select
                  v-model="editForm.type"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                >
                  <option value="registration">Registration</option>
                  <option value="tuition">Tuition</option>
                  <option value="uniform">Uniform</option>
                  <option value="tools">Tools</option>
                  <option value="resource">Resource</option>
                  <option value="examination">Examination</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                <input
                  v-model="editForm.amount"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                <input
                  v-model="editForm.due_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select
                  v-model="editForm.status"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                >
                  <option value="pending">Pending</option>
                  <option value="partial">Partial</option>
                  <option value="paid">Paid</option>
                  <option value="overdue">Overdue</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
            </div>
            <InputError :message="errors.amount" class="mt-1" />
            <button
              @click="saveChanges"
              class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium"
            >
              Save Changes
            </button>
          </div>

          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500 dark:text-gray-400">Invoice Number</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ invoice.invoice_number }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Type</p>
              <p class="font-medium text-gray-900 dark:text-gray-100 capitalize">{{ invoice.type }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Academic Year</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ invoice.academic_year }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Semester</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ invoice.semester }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Amount</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(invoice.amount) }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Due Date</p>
              <p :class="invoice.is_overdue ? 'text-red-600' : 'text-gray-900 dark:text-gray-100'">
                {{ invoice.due_date || 'Not set' }}
              </p>
            </div>
            <div class="col-span-2">
              <p class="text-gray-500 dark:text-gray-400">Description</p>
              <p class="text-gray-900 dark:text-gray-100">{{ invoice.description || '—' }}</p>
            </div>
            <div v-if="invoice.notes" class="col-span-2">
              <p class="text-gray-500 dark:text-gray-400">Notes</p>
              <p class="text-gray-900 dark:text-gray-100">{{ invoice.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Payments -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Payment History</h3>
          <div v-if="invoice.payments?.length === 0" class="text-center py-8 text-gray-500">
            No payments recorded
          </div>
          <div v-else class="space-y-3">
            <div v-for="payment in invoice.payments" :key="payment.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
              <div>
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(payment.amount) }}</p>
                <p class="text-xs text-gray-500">{{ payment.payment_date }} &middot; {{ payment.payment_method }}</p>
              </div>
              <span :class="payment.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                class="px-2 py-1 rounded-full text-xs font-medium capitalize">
                {{ payment.status }}
              </span>
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
              <span :class="statusClass(invoice.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize">
                {{ invoice.status }}
              </span>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Total Paid</p>
              <p class="text-lg font-semibold text-green-600 dark:text-green-400">{{ formatCurrency(invoice.total_paid) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Balance Due</p>
              <p class="text-lg font-semibold" :class="invoice.balance > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600'">
                {{ formatCurrency(invoice.balance) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Student Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Student</h3>
          <p class="font-medium text-gray-900 dark:text-gray-100">{{ invoice.user?.name }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ invoice.user?.email }}</p>
          <p v-if="invoice.programme" class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            {{ invoice.programme?.name }}
          </p>
        </div>

        <!-- Actions -->
        <div v-if="invoice.status !== 'cancelled' && invoice.status !== 'paid'" class="space-y-3">
          <button
            @click="markAsPaid"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium"
          >
            <CheckCircleIcon class="w-5 h-5" />
            Mark as Paid
          </button>
          <button
            @click="cancelInvoice"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg font-medium hover:bg-red-50 dark:hover:bg-red-900/20"
          >
            <TrashIcon class="w-5 h-5" />
            Cancel Invoice
          </button>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>