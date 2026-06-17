<template>
  <HiveLayout title="Payslips" :description="canManage ? 'Manage staff payroll and generate payslips' : 'View and download your payslips'">

    <template #header-actions>
      <div class="flex items-center gap-3">
        <!-- Staff: Generate own payslip -->
        <form v-if="!canManage" @submit.prevent="generateForm.post(route('hive.payslips.generate'))" class="flex items-center gap-2">
          <select v-model="generateForm.month"
            class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-500">
            <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
          <button type="submit" :disabled="generateForm.processing"
            class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition disabled:opacity-50">
            Generate My Payslip
          </button>
        </form>

        <!-- Admin: Batch Generate -->
        <div v-if="canManage" class="flex items-center gap-2">
          <form @submit.prevent="batchGenerateForm.post(route('hive.payslips.generate.batch'))" class="flex items-center gap-2">
            <select v-model="batchGenerateForm.month"
              class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-500">
              <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>
            <button type="submit" :disabled="batchGenerateForm.processing"
              class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition disabled:opacity-50">
              Batch Generate
            </button>
          </form>
          <Link :href="route('hive.payslips.create')"
            class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <PlusIcon class="w-4 h-4" />
            Manual Payslip
          </Link>
        </div>
      </div>
    </template>

    <!-- Payslips Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <!-- Empty State -->
      <div v-if="payslips.data.length === 0" class="px-6 py-16 text-center">
        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
          <DocumentIcon class="w-8 h-8 text-gray-400" />
        </div>
        <p class="text-gray-500 dark:text-gray-400 font-medium">No payslips found.</p>
        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Generate your first payslip using the button above.</p>
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
            <tr>
              <th v-if="canManage" class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Employee</th>
              <th v-else class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Pay Period</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden sm:table-cell">Earnings</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden sm:table-cell">Deductions</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Net Pay</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Leave Days</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="payslip in payslips.data" :key="payslip.id" class="hover:bg-amber-50/50 dark:hover:bg-amber-900/20 transition-colors">
              <!-- Employee name (admin only) -->
              <td v-if="canManage" class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 bg-amber-100 dark:bg-amber-900/50 rounded-full flex items-center justify-center text-amber-800 dark:text-amber-300 font-semibold text-sm">
                    {{ payslip.user?.name?.charAt(0) || '?' }}
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ payslip.user?.name || 'Unknown' }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ formatPeriod(payslip.pay_period_start, payslip.pay_period_end) }}</p>
                  </div>
                </div>
              </td>

              <!-- Pay period (staff) -->
              <td v-else class="px-6 py-4">
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ formatMonthYear(payslip.pay_period_start) }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ formatPeriod(payslip.pay_period_start, payslip.pay_period_end) }}</p>
              </td>

              <!-- Earnings -->
              <td class="px-6 py-4 hidden sm:table-cell">
                <div class="text-emerald-700 dark:text-emerald-400 font-semibold">LSL {{ parseFloat(payslip.gross_salary).toLocaleString() }}</div>
                <div v-if="payslip.earnings" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                  <span v-if="payslip.earnings.base_salary">Base {{ parseFloat(payslip.earnings.base_salary).toLocaleString() }}</span>
                  <span v-if="payslip.earnings.housing_allowance"> · Hous. {{ parseFloat(payslip.earnings.housing_allowance).toLocaleString() }}</span>
                  <span v-if="payslip.earnings.bonus"> · Bonus {{ parseFloat(payslip.earnings.bonus).toLocaleString() }}</span>
                </div>
              </td>

              <!-- Deductions -->
              <td class="px-6 py-4 hidden sm:table-cell">
                <div class="text-red-700 dark:text-red-400 font-semibold">LSL {{ parseFloat(payslip.deductions).toLocaleString() }}</div>
                <div v-if="payslip.deductions_breakdown" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                  <span v-if="payslip.deductions_breakdown.tax">Tax {{ parseFloat(payslip.deductions_breakdown.tax).toLocaleString() }}</span>
                  <span v-if="payslip.deductions_breakdown.leave_deduction"> · Lv {{ parseFloat(payslip.deductions_breakdown.leave_deduction).toLocaleString() }}</span>
                  <span v-if="payslip.deductions_breakdown.pension"> · Pen {{ parseFloat(payslip.deductions_breakdown.pension).toLocaleString() }}</span>
                </div>
              </td>

              <!-- Net Pay -->
              <td class="px-6 py-4">
                <span class="px-2.5 py-1 bg-gray-900 dark:bg-gray-700 text-white text-sm font-bold rounded-lg">
                  LSL {{ parseFloat(payslip.net_salary).toLocaleString() }}
                </span>
              </td>

              <!-- Leave Days -->
              <td class="px-6 py-4 hidden md:table-cell">
                <span v-if="payslip.leave_days_taken > 0" class="text-xs text-orange-600 dark:text-orange-400 font-medium">
                  {{ payslip.leave_days_taken }} day{{ payslip.leave_days_taken > 1 ? 's' : '' }}
                </span>
                <span v-else class="text-xs text-gray-300 dark:text-gray-600">—</span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2">
                  <a :href="route('hive.payslips.download', { payslip: payslip.id })"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <ArrowDownTrayIcon class="w-3.5 h-3.5" />
                    PDF
                  </a>
                  <Link v-if="canManage" :href="route('hive.payslips.edit', { payslip: payslip.id })"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                    Edit
                  </Link>
                  <button v-if="canManage" @click="deletePayslip(payslip)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination v-if="payslips.data.length > 0" :links="payslips.links" :meta="payslips.meta" />
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { PlusIcon, DocumentIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import dayjs from 'dayjs';

const props = defineProps({
  payslips: Object,
  users: Array,
  canManagePayslips: Boolean,
});

const canManage = props.canManagePayslips;

const generateForm = useForm({ month: currentMonthValue() });
const batchGenerateForm = useForm({ month: currentMonthValue() });

function currentMonthValue() {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
}

const monthOptions = computed(() => {
  const options = [];
  const now = new Date();
  for (let i = 0; i < 12; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    options.push({
      value: `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`,
      label: d.toLocaleString('default', { month: 'long', year: 'numeric' }),
    });
  }
  return options;
});

const formatMonthYear = (dateStr) => {
  if (!dateStr) return '';
  return dayjs(dateStr).format('MMMM YYYY');
};

const formatPeriod = (start, end) => {
  if (!start || !end) return '';
  const s = dayjs(start);
  const e = dayjs(end);
  if (s.isSame(e, 'month')) {
    return formatMonthYear(start);
  }
  return `${formatMonthYear(start)} — ${formatMonthYear(end)}`;
};

const deletePayslip = (payslip) => {
  if (confirm(`Delete payslip for ${payslip.user?.name || 'this staff member'} for ${formatMonthYear(payslip.pay_period_start)}?`)) {
    useForm({}).delete(route('hive.payslips.destroy', { payslip: payslip.id }));
  }
};
</script>