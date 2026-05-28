<template>
  <HiveLayout title="Payslips" :description="canManage ? 'Manage staff payroll and generate payslips' : 'View and download your payslips'">

    <template #header-actions>
      <div class="flex items-center gap-3">
        <!-- Staff: Generate own payslip -->
        <form v-if="!canManage" @submit.prevent="generateForm.post(route('hive.payslips.generate'))" class="flex items-center gap-2">
          <select v-model="generateForm.month"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500">
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
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500">
              <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>
            <button type="submit" :disabled="batchGenerateForm.processing"
              class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
              Batch Generate
            </button>
          </form>
          <Link :href="route('hive.payslips.create')"
            class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Manual Payslip
          </Link>
        </div>
      </div>
    </template>

    <!-- Payslips Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <!-- Empty State -->
      <div v-if="payslips.data.length === 0" class="px-6 py-16 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <p class="text-gray-500 font-medium">No payslips found.</p>
        <p class="text-sm text-gray-400 mt-1">Generate your first payslip using the button above.</p>
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th v-if="canManage" class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Employee</th>
              <th v-else class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pay Period</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden sm:table-cell">Earnings</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden sm:table-cell">Deductions</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Net Pay</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Leave Days</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="payslip in payslips.data" :key="payslip.id" class="hover:bg-amber-50/50 transition-colors">
              <!-- Employee name (admin only) -->
              <td v-if="canManage" class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 bg-amber-100 rounded-full flex items-center justify-center text-amber-800 font-semibold text-sm">
                    {{ payslip.user?.name?.charAt(0) || '?' }}
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">{{ payslip.user?.name || 'Unknown' }}</p>
                    <p class="text-xs text-gray-400">{{ formatPeriod(payslip.pay_period_start, payslip.pay_period_end) }}</p>
                  </div>
                </div>
              </td>

              <!-- Pay period (staff) -->
              <td v-else class="px-6 py-4">
                <p class="font-medium text-gray-900">{{ formatMonthYear(payslip.pay_period_start) }}</p>
                <p class="text-xs text-gray-400">{{ payslip.pay_period_start }} → {{ payslip.pay_period_end }}</p>
              </td>

              <!-- Earnings -->
              <td class="px-6 py-4 hidden sm:table-cell">
                <div class="text-emerald-700 font-semibold">LSL {{ parseFloat(payslip.gross_salary).toLocaleString() }}</div>
                <div v-if="payslip.earnings" class="text-xs text-gray-400 mt-0.5">
                  <span v-if="payslip.earnings.base_salary">Base {{ parseFloat(payslip.earnings.base_salary).toLocaleString() }}</span>
                  <span v-if="payslip.earnings.housing_allowance"> · Hous. {{ parseFloat(payslip.earnings.housing_allowance).toLocaleString() }}</span>
                  <span v-if="payslip.earnings.bonus"> · Bonus {{ parseFloat(payslip.earnings.bonus).toLocaleString() }}</span>
                </div>
              </td>

              <!-- Deductions -->
              <td class="px-6 py-4 hidden sm:table-cell">
                <div class="text-red-700 font-semibold">LSL {{ parseFloat(payslip.deductions).toLocaleString() }}</div>
                <div v-if="payslip.deductions_breakdown" class="text-xs text-gray-400 mt-0.5">
                  <span v-if="payslip.deductions_breakdown.tax">Tax {{ parseFloat(payslip.deductions_breakdown.tax).toLocaleString() }}</span>
                  <span v-if="payslip.deductions_breakdown.leave_deduction"> · Lv {{ parseFloat(payslip.deductions_breakdown.leave_deduction).toLocaleString() }}</span>
                  <span v-if="payslip.deductions_breakdown.pension"> · Pen {{ parseFloat(payslip.deductions_breakdown.pension).toLocaleString() }}</span>
                </div>
              </td>

              <!-- Net Pay -->
              <td class="px-6 py-4">
                <span class="px-2.5 py-1 bg-gray-900 text-white text-sm font-bold rounded-lg">
                  LSL {{ parseFloat(payslip.net_salary).toLocaleString() }}
                </span>
              </td>

              <!-- Leave Days -->
              <td class="px-6 py-4 hidden md:table-cell">
                <span v-if="payslip.leave_days_taken > 0" class="text-xs text-orange-600 font-medium">
                  {{ payslip.leave_days_taken }} day{{ payslip.leave_days_taken > 1 ? 's' : '' }}
                </span>
                <span v-else class="text-xs text-gray-300">—</span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2">
                  <a :href="route('hive.payslips.download', payslip.id)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    PDF
                  </a>
                  <Link v-if="canManage" :href="route('hive.payslips.edit', payslip.id)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-600 bg-amber-50 rounded-lg hover:bg-amber-100 transition">
                    Edit
                  </Link>
                  <button v-if="canManage" @click="deletePayslip(payslip)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition">
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
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';

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
  const d = new Date(dateStr);
  return d.toLocaleString('default', { month: 'long', year: 'numeric' });
};

const formatPeriod = (start, end) => {
  if (!start || !end) return '';
  const s = new Date(start);
  const e = new Date(end);
  if (s.getFullYear() === e.getFullYear() && s.getMonth() === e.getMonth()) {
    return formatMonthYear(start);
  }
  return `${formatMonthYear(start)} — ${formatMonthYear(end)}`;
};

const deletePayslip = (payslip) => {
  if (confirm(`Delete payslip for ${payslip.user?.name || 'this staff member'} for ${formatMonthYear(payslip.pay_period_start)}?`)) {
    useForm({}).delete(route('hive.payslips.destroy', payslip.id));
  }
};
</script>