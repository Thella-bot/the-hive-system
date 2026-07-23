<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import InputError from '@/Components/InputError.vue';
import {
  ArrowLeftIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  categories: { type: Array, default: () => [] },
  budgets: { type: Array, default: () => [] },
  errors: { type: Object, default: () => ({}) },
});

const form = ref({
  expense_category_id: '',
  vendor_id: '',
  budget_id: '',
  description: '',
  amount: '',
  expense_date: new Date().toISOString().split('T')[0],
  payment_method: '',
  reference_number: '',
  notes: '',
});

const isSubmitting = ref(false);

const submit = () => {
  isSubmitting.value = true;
  router.post(route('finance.expenses.store'), form.value, {
    onFinish: () => {
      isSubmitting.value = false;
    },
  });
};
</script>

<template>
  <HiveLayout title="New Expense" description="Create a new expense record">
    <template #header-actions>
      <Link :href="route('finance.expenses.index')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Expenses
      </Link>
    </template>

    <form @submit.prevent="submit" class="max-w-2xl">
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
        <!-- Basic Info -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Expense Details</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Description *
              </label>
              <input
                v-model="form.description"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                placeholder="Enter expense description"
              />
              <InputError :message="errors.description" class="mt-1" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Amount *
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">R</span>
                <input
                  v-model="form.amount"
                  type="number"
                  step="0.01"
                  min="0.01"
                  required
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                  placeholder="0.00"
                />
              </div>
              <InputError :message="errors.amount" class="mt-1" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Expense Date
              </label>
              <input
                v-model="form.expense_date"
                type="date"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
              />
            </div>
          </div>
        </div>

        <!-- Category & Budget -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Category & Budget</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Category
              </label>
              <select
                v-model="form.expense_category_id"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
              >
                <option value="">Select category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Budget
              </label>
              <select
                v-model="form.budget_id"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
              >
                <option value="">Select budget</option>
                <option v-for="budget in budgets" :key="budget.id" :value="budget.id">
                  {{ budget.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Payment Info -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Payment Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Payment Method
              </label>
              <select
                v-model="form.payment_method"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
              >
                <option value="">Select method</option>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="card">Card</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Reference Number
              </label>
              <input
                v-model="form.reference_number"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                placeholder="e.g. receipt number"
              />
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Notes
          </label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
            placeholder="Additional notes..."
          ></textarea>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3 pt-4">
          <Link
            :href="route('finance.expenses.index')"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50"
          >
            <span v-if="isSubmitting">Creating...</span>
            <span v-else>Create Expense</span>
          </button>
        </div>
      </div>
    </form>
  </HiveLayout>
</template>