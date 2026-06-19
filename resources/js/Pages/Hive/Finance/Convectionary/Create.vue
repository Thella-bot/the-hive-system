<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import InputError from '@/Components/InputError.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  sources: { type: Object, required: true },
  methods: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
});

const form = ref({
  source: '',
  amount: '',
  income_date: new Date().toISOString().split('T')[0],
  description: '',
  payment_method: '',
  status: 'received',
});

const isSubmitting = ref(false);

const submit = () => {
  isSubmitting.value = true;
  router.post(route('finance.convectionary.store'), form.value, {
    onFinish: () => { isSubmitting.value = false; },
  });
};
</script>

<template>
  <HiveLayout title="Record Convectionary Income" description="Add new convectionary income">
    <template #header-actions>
      <Link :href="route('finance.convectionary.index')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Convectionary
      </Link>
    </template>

    <form @submit.prevent="submit" class="max-w-2xl">
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
        <!-- Source -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Income Details</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Source *</label>
              <select
                v-model="form.source"
                required
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
              >
                <option value="">Select source</option>
                <option v-for="(label, key) in sources" :key="key" :value="key">{{ label }}</option>
              </select>
              <InputError :message="errors.source" class="mt-1" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount *</label>
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
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Income Date</label>
              <input
                v-model="form.income_date"
                type="date"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
              <select
                v-model="form.payment_method"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
              >
                <option value="">Select method</option>
                <option v-for="(label, key) in methods" :key="key" :value="key">{{ label }}</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
              <select
                v-model="form.status"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
              >
                <option value="pending">Pending</option>
                <option value="received">Received</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
            placeholder="Describe the income source..."
          ></textarea>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3 pt-4">
          <Link
            :href="route('finance.convectionary.index')"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium transition-colors disabled:opacity-50"
          >
            <span v-if="isSubmitting">Saving...</span>
            <span v-else>Record Income</span>
          </button>
        </div>
      </div>
    </form>
  </HiveLayout>
</template>