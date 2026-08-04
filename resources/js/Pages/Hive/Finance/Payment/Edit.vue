<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
  payment: { type: Object, required: true },
  invoices: { type: Array, default: () => [] },
  methods: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
});

const form = ref({
  invoice_id: props.payment.invoice?.id || '',
  amount: props.payment.amount || '',
  payment_method: props.payment.payment_method || 'cash',
  payment_date: props.payment.payment_date || '',
  notes: props.payment.notes || '',
  status: props.payment.status || 'pending',
});

const errors = ref({});
const isSubmitting = ref(false);

const submit = () => {
  isSubmitting.value = true;
  router.patch(route('hive.finance.payments.update', props.payment.id), form.value, {
    onError: (errs) => { errors.value = errs; },
    onFinish: () => { isSubmitting.value = false; },
    onSuccess: () => { errors.value = {}; },
  });
};
</script>

<template>
  <Head title="Edit Payment" />
  <HiveLayout title="Edit Payment" description="Edit payment details">
    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Payment Details</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice</label>
            <select v-model="form.invoice_id" required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
              <option value="">Select an invoice</option>
              <option v-for="inv in invoices" :key="inv.id" :value="inv.id">
                {{ inv.invoice_number }} — {{ inv.user?.name || '—' }}
              </option>
            </select>
            <span v-if="errors.invoice_id" class="text-red-500 text-xs">{{ errors.invoice_id }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
            <input v-model="form.amount" type="number" step="0.01" min="0" required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
            <span v-if="errors.amount" class="text-red-500 text-xs">{{ errors.amount }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
            <select v-model="form.payment_method" required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
              <option v-for="m in methods" :key="m" :value="m">{{ m.replace('_', ' ').replace(/\b\w/, l => l.toUpperCase()) }}</option>
            </select>
            <span v-if="errors.payment_method" class="text-red-500 text-xs">{{ errors.payment_method }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Date</label>
            <input v-model="form.payment_date" type="date"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
            <span v-if="errors.payment_date" class="text-red-500 text-xs">{{ errors.payment_date }}</span>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
            <textarea v-model="form.notes" rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              placeholder="Optional notes..."></textarea>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-4">
        <Link :href="route('hive.finance.payments.show', props.payment.id)"
          class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
          Cancel
        </Link>
        <button type="submit" :disabled="isSubmitting"
          class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg disabled:opacity-50">
          {{ isSubmitting ? 'Saving...' : 'Update Payment' }}
        </button>
      </div>
    </form>
  </HiveLayout>
</template>
