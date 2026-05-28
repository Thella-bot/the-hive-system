<template>
  <HiveLayout :title="isEditing ? 'Edit Payslip' : 'Create Payslip'" :description="isEditing ? `Editing payslip for ${payslip?.user?.name}` : 'Enter salary details for a staff member'">
    <div class="max-w-4xl mx-auto">
      <!-- Staff Selector (Create mode only) -->
      <div v-if="!isEditing" class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Select Staff Member *</label>
        <select v-model="form.user_id"
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
          <option value="" disabled>Choose a staff member...</option>
          <option v-for="s in staff" :key="s.id" :value="s.id">
            {{ s.name }}
            <span v-if="s.salaryProfile" class="text-gray-400"> — LSL {{ parseFloat(s.salaryProfile.base_salary).toLocaleString() }}/mo</span>
            <span v-else class="text-red-400"> (no salary profile)</span>
          </option>
        </select>
        <p v-if="form.errors.user_id" class="text-red-600 text-xs mt-1">{{ form.errors.user_id }}</p>
        <!-- Pre-fill hint -->
        <div v-if="selectedStaffProfile" class="mt-4 p-4 bg-amber-50 rounded-lg border border-amber-200">
          <p class="text-sm font-semibold text-amber-800 mb-2">Salary Profile Found</p>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div>
              <span class="text-gray-500">Base Salary</span>
              <p class="font-medium text-gray-900">LSL {{ parseFloat(selectedStaffProfile.base_salary).toLocaleString() }}</p>
            </div>
            <div>
              <span class="text-gray-500">Housing</span>
              <p class="font-medium text-gray-900">LSL {{ parseFloat(selectedStaffProfile.housing_allowance).toLocaleString() }}</p>
            </div>
            <div>
              <span class="text-gray-500">Transport</span>
              <p class="font-medium text-gray-900">LSL {{ parseFloat(selectedStaffProfile.transport_allowance).toLocaleString() }}</p>
            </div>
            <div>
              <span class="text-gray-500">Medical</span>
              <p class="font-medium text-gray-900">LSL {{ parseFloat(selectedStaffProfile.medical_allowance).toLocaleString() }}</p>
            </div>
          </div>
          <button type="button" @click="prefillFromProfile" class="mt-3 text-xs text-amber-700 font-semibold hover:underline">
            Pre-fill earnings from salary profile
          </button>
        </div>
      </div>

      <!-- Payslip Form -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
          <h2 class="text-base font-semibold text-gray-900">{{ isEditing ? 'Edit Payslip Details' : 'Pay Period & Earnings' }}</h2>
        </div>
        <form @submit.prevent="submit" class="p-6 space-y-6">
          <!-- Pay Period -->
          <div class="grid sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Period Start *</label>
              <input type="date" v-model="form.pay_period_start" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                :class="form.errors.pay_period_start ? 'border-red-400' : ''" />
              <p v-if="form.errors.pay_period_start" class="text-red-600 text-xs mt-1">{{ form.errors.pay_period_start }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Period End *</label>
              <input type="date" v-model="form.pay_period_end" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                :class="form.errors.pay_period_end ? 'border-red-400' : ''" />
              <p v-if="form.errors.pay_period_end" class="text-red-600 text-xs mt-1">{{ form.errors.pay_period_end }}</p>
            </div>
          </div>

          <!-- Earnings -->
          <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
              <span class="w-6 h-6 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-xs font-bold">+</span>
              Earnings
            </h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Base Salary (LSL) *</label>
                <input type="number" v-model.number="form.base_salary" min="0" step="0.01" required
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="0.00" />
                <p v-if="form.errors.base_salary" class="text-red-600 text-xs mt-1">{{ form.errors.base_salary }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Housing Allowance (LSL)</label>
                <input type="number" v-model.number="form.housing_allowance" min="0" step="0.01"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="0.00" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Transport Allowance (LSL)</label>
                <input type="number" v-model.number="form.transport_allowance" min="0" step="0.01"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="0.00" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Medical Allowance (LSL)</label>
                <input type="number" v-model.number="form.medical_allowance" min="0" step="0.01"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="0.00" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Overtime (LSL)</label>
                <input type="number" v-model.number="form.overtime" min="0" step="0.01"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="0.00" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Bonus (LSL)</label>
                <input type="number" v-model.number="form.bonus" min="0" step="0.01"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="0.00" />
              </div>
            </div>
            <!-- Gross salary preview -->
            <div class="mt-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
              <span class="text-xs font-semibold text-emerald-700">Gross Earnings: </span>
              <span class="text-sm font-bold text-emerald-800">LSL {{ grossEarnings.toLocaleString() }}</span>
            </div>
          </div>

          <!-- Deductions -->
          <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
              <span class="w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-xs font-bold">−</span>
              Deductions
            </h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">PAYE / Tax (LSL)</label>
                <input type="number" v-model.number="form.tax_deduction" min="0" step="0.01"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="0.00" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Pension (LSL)</label>
                <input type="number" v-model.number="form.pension_deduction" min="0" step="0.01"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="0.00" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Leave Deduction (LSL)</label>
                <input type="number" v-model.number="form.leave_deducted" min="0" step="0.01"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="0.00" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Other Deduction (LSL)</label>
                <input type="number" v-model.number="form.other_deduction" min="0" step="0.01"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="0.00" />
              </div>
            </div>
            <!-- Leave days taken -->
            <div class="mt-4">
              <label class="block text-xs font-medium text-gray-500 mb-1">Leave Days Taken</label>
              <input type="number" v-model.number="form.leave_days_taken" min="0"
                class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500" />
            </div>
            <!-- Total deductions preview -->
            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
              <span class="text-xs font-semibold text-red-700">Total Deductions: </span>
              <span class="text-sm font-bold text-red-800">LSL {{ totalDeductions.toLocaleString() }}</span>
            </div>
          </div>

          <!-- Net Salary Preview -->
          <div class="p-4 bg-gray-900 border border-gray-700 rounded-xl">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-gray-300">Net Pay</span>
              <span class="text-2xl font-bold text-white">LSL {{ netSalary.toLocaleString() }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-4 pt-2">
            <button type="submit" :disabled="form.processing"
              class="px-6 py-2.5 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition disabled:opacity-50">
              {{ isEditing ? 'Update Payslip' : 'Create Payslip' }}
            </button>
            <Link :href="route('hive.payslips.index')" class="px-6 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
              Cancel
            </Link>
          </div>
        </form>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
  staff: Array,
  payslip: Object,
  isEditing: Boolean,
});

const form = useForm({
  user_id: props.payslip?.user_id || '',
  pay_period_start: props.payslip?.pay_period_start?.slice(0, 10) || '',
  pay_period_end: props.payslip?.pay_period_end?.slice(0, 10) || '',
  base_salary: props.payslip?.earnings?.base_salary ?? '',
  housing_allowance: props.payslip?.earnings?.housing_allowance ?? '',
  transport_allowance: props.payslip?.earnings?.transport_allowance ?? '',
  medical_allowance: props.payslip?.earnings?.medical_allowance ?? '',
  overtime: props.payslip?.earnings?.overtime ?? '',
  bonus: props.payslip?.earnings?.bonus ?? '',
  tax_deduction: props.payslip?.deductions_breakdown?.tax ?? '',
  pension_deduction: props.payslip?.deductions_breakdown?.pension ?? '',
  leave_deducted: props.payslip?.deductions_breakdown?.leave_deduction ?? '',
  leave_days_taken: props.payslip?.leave_days_taken || '',
  other_deduction: props.payslip?.deductions_breakdown?.other ?? '',
});

const submit = () => {
  if (props.isEditing) {
    form.patch(route('hive.payslips.update', props.payslip.id));
  } else {
    form.post(route('hive.payslips.store'));
  }
};

// Auto-calculate from selected staff's salary profile
const selectedStaffProfile = computed(() => {
  if (!form.user_id || props.isEditing) return null;
  const s = props.staff?.find(x => x.id === form.user_id);
  return s?.salaryProfile || null;
});

const prefillFromProfile = () => {
  if (!selectedStaffProfile.value) return;
  const p = selectedStaffProfile.value;
  form.base_salary = parseFloat(p.base_salary) || 0;
  form.housing_allowance = parseFloat(p.housing_allowance) || 0;
  form.transport_allowance = parseFloat(p.transport_allowance) || 0;
  form.medical_allowance = parseFloat(p.medical_allowance) || 0;
};

// Pre-fill default pay period to current month
if (!props.isEditing) {
  const now = new Date();
  form.pay_period_start = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10);
  form.pay_period_end = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().slice(0, 10);
}

// Derived totals
const grossEarnings = computed(() => {
  return (
    (form.base_salary || 0) +
    (form.housing_allowance || 0) +
    (form.transport_allowance || 0) +
    (form.medical_allowance || 0) +
    (form.overtime || 0) +
    (form.bonus || 0)
  );
});

const totalDeductions = computed(() => {
  return (
    (form.tax_deduction || 0) +
    (form.pension_deduction || 0) +
    (form.leave_deducted || 0) +
    (form.other_deduction || 0)
  );
});

const netSalary = computed(() => Math.max(0, grossEarnings.value - totalDeductions.value));
</script>