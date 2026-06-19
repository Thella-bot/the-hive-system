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
  BanknotesIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  expense: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
});

const showApproveModal = ref(false);
const rejectNotes = ref('');
const approveNotes = ref('');
const showPaymentModal = ref(false);
const paymentForm = ref({ payment_method: '', reference_number: '', notes: '' });

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-LS', { style: 'currency', currency: 'ZAR' }).format(amount || 0);
};

const statusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    paid: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
  };
  return classes[status] || classes.pending;
};

const approveExpense = () => {
  router.patch(route('finance.expenses.approve', props.expense.id), { notes: approveNotes.value }, {
    onSuccess: () => { showApproveModal.value = false; }
  });
};

const rejectExpense = () => {
  if (!rejectNotes.value) return alert('Please provide a reason for rejection');
  router.patch(route('finance.expenses.reject', props.expense.id), { notes: rejectNotes.value }, {
    onSuccess: () => { showApproveModal.value = false; }
  });
};

const markAsPaid = () => {
  router.patch(route('finance.expenses.markPaid', props.expense.id), paymentForm.value, {
    onSuccess: () => { showPaymentModal.value = false; }
  });
};

const deleteExpense = () => {
  if (confirm('Are you sure you want to delete this expense?')) {
    router.delete(route('finance.expenses.destroy', props.expense.id));
  }
};
</script>

<template>
  <HiveLayout :title="`Expense ${expense.expense_number}`" :description="expense.description">
    <template #header-actions>
      <Link :href="route('finance.expenses.index')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Expenses
      </Link>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Details -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Expense Details</h3>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500 dark:text-gray-400">Expense Number</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ expense.expense_number }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Category</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ expense.category?.name || '—' }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Amount</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(expense.amount) }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Expense Date</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ expense.expense_date }}</p>
            </div>
            <div v-if="expense.budget">
              <p class="text-gray-500 dark:text-gray-400">Budget</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ expense.budget.name }}</p>
            </div>
            <div v-if="expense.vendor">
              <p class="text-gray-500 dark:text-gray-400">Vendor</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ expense.vendor.name }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Payment Method</p>
              <p class="font-medium text-gray-900 dark:text-gray-100 capitalize">{{ expense.payment_method || '—' }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Reference</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ expense.reference_number || '—' }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-gray-500 dark:text-gray-400">Description</p>
              <p class="text-gray-900 dark:text-gray-100">{{ expense.description }}</p>
            </div>
            <div v-if="expense.notes" class="col-span-2">
              <p class="text-gray-500 dark:text-gray-400">Notes</p>
              <p class="text-gray-900 dark:text-gray-100">{{ expense.notes }}</p>
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
              <span :class="statusClass(expense.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize">
                {{ expense.status }}
              </span>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Requested By</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ expense.user?.name }}</p>
            </div>
            <div v-if="expense.approver">
              <p class="text-sm text-gray-500 dark:text-gray-400">Approved By</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ expense.approver.name }}</p>
            </div>
            <div v-if="expense.approved_at">
              <p class="text-sm text-gray-500 dark:text-gray-400">Approved At</p>
              <p class="text-sm text-gray-900 dark:text-gray-100">{{ expense.approved_at }}</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div v-if="expense.status === 'pending'" class="space-y-3">
          <button
            @click="showApproveModal = true"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium"
          >
            <CheckCircleIcon class="w-5 h-5" />
            Review Expense
          </button>
          <button
            @click="deleteExpense"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg font-medium hover:bg-red-50 dark:hover:bg-red-900/20"
          >
            <TrashIcon class="w-5 h-5" />
            Delete
          </button>
        </div>

        <div v-if="expense.status === 'approved'" class="space-y-3">
          <button
            @click="showPaymentModal = true"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium"
          >
            <BanknotesIcon class="w-5 h-5" />
            Mark as Paid
          </button>
        </div>
      </div>
    </div>

    <!-- Approve/Reject Modal -->
    <div v-if="showApproveModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Review Expense</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (optional)</label>
            <textarea
              v-model="approveNotes"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              placeholder="Add any notes..."
            ></textarea>
          </div>
          <div class="flex gap-3">
            <button
              @click="approveExpense"
              class="flex-1 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium"
            >
              Approve
            </button>
            <button
              @click="rejectExpense"
              class="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium"
            >
              Reject
            </button>
          </div>
          <button
            @click="showApproveModal = false"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Record Payment</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
            <select
              v-model="paymentForm.payment_method"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
            >
              <option value="">Select method</option>
              <option value="cash">Cash</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="card">Card</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reference Number</label>
            <input
              v-model="paymentForm.reference_number"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              placeholder="e.g. cheque number"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
            <textarea
              v-model="paymentForm.notes"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              placeholder="Payment notes..."
            ></textarea>
          </div>
          <div class="flex gap-3">
            <button
              @click="markAsPaid"
              class="flex-1 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium"
            >
              Confirm Payment
            </button>
            <button
              @click="showPaymentModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>