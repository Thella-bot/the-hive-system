<script setup>
import { ref, computed } from 'vue';
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
  items: props.payment.items?.length ? props.payment.items : [{ description: '', qty: 1, unit_price: '', total: '' }],
  payment_method: props.payment.payment_method || 'cash',
  payment_date: props.payment.payment_date || '',
  notes: props.payment.notes || '',
  status: props.payment.status || 'pending',
});

const errors = ref({});
const isSubmitting = ref(false);

const totalAmount = computed(() => {
  return form.value.items.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
});

const addItem = () => {
  form.value.items.push({ description: '', qty: 1, unit_price: '', total: '' });
};

const removeItem = (index) => {
  if (form.value.items.length > 1) {
    form.value.items.splice(index, 1);
  }
};

const updateItemTotal = (index) => {
  const item = form.value.items[index];
  const qty = parseFloat(item.qty) || 0;
  const unitPrice = parseFloat(item.unit_price) || 0;
  item.total = (qty * unitPrice).toFixed(2);
};

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

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
            <select v-model="form.status" required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
              <option v-for="s in statuses" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
            </select>
            <span v-if="errors.status" class="text-red-500 text-xs">{{ errors.status }}</span>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Line Items</h2>
          <button type="button" @click="addItem"
            class="inline-flex items-center gap-1 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg">
            + Add Item
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
              <tr>
                <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Description</th>
                <th class="text-center px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide w-20">Qty</th>
                <th class="text-right px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide w-28">Unit Price (M)</th>
                <th class="text-right px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide w-28">Amount (M)</th>
                <th class="px-3 py-2 w-10"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="(item, index) in form.items" :key="index">
                <td class="px-3 py-2">
                  <input v-model="item.description" type="text" placeholder="e.g. Tuition Fee" required
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm" />
                </td>
                <td class="px-3 py-2 text-center">
                  <input v-model.number="item.qty" type="number" min="1" required
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm text-center"
                    @input="updateItemTotal(index)" />
                </td>
                <td class="px-3 py-2 text-right">
                  <input v-model="item.unit_price" type="number" step="0.01" min="0" required
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm text-right"
                    @input="updateItemTotal(index)" />
                </td>
                <td class="px-3 py-2 text-right">
                  <input v-model="item.total" type="number" step="0.01" min="0" required
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm text-right" />
                </td>
                <td class="px-3 py-2 text-center">
                  <button type="button" @click="removeItem(index)" v-if="form.items.length > 1"
                    class="text-red-500 hover:text-red-700 text-sm">
                    &times;
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-4 flex justify-end">
          <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Total: M {{ totalAmount.toFixed(2) }}
          </p>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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