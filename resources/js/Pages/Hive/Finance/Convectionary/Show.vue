<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import InputError from '@/Components/InputError.vue';
import {
  ArrowLeftIcon,
  PencilSquareIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  income: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
});

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

const methodLabel = (method) => {
  return method?.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) || '';
};

const showEditForm = ref(false);
const editForm = ref({ ...props.income });

const saveChanges = () => {
  router.patch(route('finance.convectionary.update', props.income.id), editForm.value, {
    onSuccess: () => { showEditForm.value = false; }
  });
};

const deleteIncome = () => {
  if (confirm('Are you sure you want to delete this record?')) {
    router.delete(route('finance.convectionary.destroy', props.income.id));
  }
};
</script>

<template>
  <HiveLayout :title="income.reference" :description="`Convectionary income from ${income.source}`">
    <template #header-actions>
      <Link :href="route('finance.convectionary.index')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Convectionary
      </Link>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Details -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Income Details</h3>
            <button
              v-if="income.status !== 'cancelled'"
              @click="showEditForm = !showEditForm"
              class="text-sm text-amber-600 hover:text-amber-700"
            >
              {{ showEditForm ? 'Cancel' : 'Edit' }}
            </button>
          </div>

          <div v-if="showEditForm" class="space-y-4 mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="grid grid-cols-2 gap-4">
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Income Date</label>
                <input
                  v-model="editForm.income_date"
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
                  <option value="received">Received</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                <select
                  v-model="editForm.payment_method"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                >
                  <option value="">Select</option>
                  <option value="cash">Cash</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="mobile_money">Mobile Money</option>
                  <option value="card">Card</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
              <textarea
                v-model="editForm.description"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              ></textarea>
            </div>
            <button
              @click="saveChanges"
               class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium"
            >
              Save Changes
            </button>
          </div>

          <div v-else class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500 dark:text-gray-400">Reference</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ income.reference }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Source</p>
              <p class="font-medium text-gray-900 dark:text-gray-100 capitalize">{{ income.source }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Amount</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(income.amount) }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Income Date</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ income.income_date }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Payment Method</p>
              <p class="font-medium text-gray-900 dark:text-gray-100 capitalize">{{ methodLabel(income.payment_method) || '—' }}</p>
            </div>
            <div v-if="income.description" class="col-span-2">
              <p class="text-gray-500 dark:text-gray-400">Description</p>
              <p class="text-gray-900 dark:text-gray-100">{{ income.description }}</p>
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
              <span :class="statusClass(income.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize">
                {{ income.status }}
              </span>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Recorded By</p>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ income.recorder?.name || '—' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Recorded At</p>
              <p class="text-sm text-gray-900 dark:text-gray-100">{{ income.created_at }}</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div v-if="income.status !== 'cancelled'" class="space-y-3">
          <button
            @click="deleteIncome"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg font-medium hover:bg-red-50 dark:hover:bg-red-900/20"
          >
            <TrashIcon class="w-5 h-5" />
            Delete Record
          </button>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>