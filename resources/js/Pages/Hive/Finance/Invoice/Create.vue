<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
  users: { type: Array, default: () => [] },
  programmes: { type: Array, default: () => [] },
  types: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
});

const form = ref({
  user_id: '',
  programme_id: '',
  type: 'registration',
  amount: '',
  description: '',
  due_date: '',
  academic_year: '',
  semester: 1,
  notes: '',
  status: 'pending',
});

const search = ref('');

const filteredUsers = computed(() => {
  if (!search.value) return props.users;
  const q = search.value.toLowerCase();
  return props.users.filter(u =>
    u.name.toLowerCase().includes(q) ||
    u.email.toLowerCase().includes(q) ||
    (u.student_number && u.student_number.toLowerCase().includes(q))
  );
});

const errors = ref({});
const isSubmitting = ref(false);

const submit = () => {
  isSubmitting.value = true;
  router.post(route('hive.finance.invoices.store'), form.value, {
    onError: (errs) => { errors.value = errs; },
    onFinish: () => { isSubmitting.value = false; },
    onSuccess: () => { errors.value = {}; },
  });
};
</script>

<template>
  <Head title="Create Invoice" />
  <HiveLayout title="Create Invoice" description="Create a new invoice for a student">
    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Invoice Details</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div>
             <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student</label>
             <input v-model="search" type="text" placeholder="Search by name, email, or student number..."
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 mb-1" />
             <select v-model="form.user_id" required
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
               <option value="">Select a student</option>
               <option v-for="u in filteredUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.student_number ?? 'N/A' }}) — {{ u.email }}</option>
             </select>
             <span v-if="errors.user_id" class="text-red-500 text-xs">{{ errors.user_id }}</span>
           </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Programme</label>
            <select v-model="form.programme_id" required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
              <option value="">Select a programme</option>
              <option v-for="p in programmes" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
            <span v-if="errors.programme_id" class="text-red-500 text-xs">{{ errors.programme_id }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
            <select v-model="form.type" required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
              <option v-for="t in types" :key="t" :value="t">{{ t.charAt(0).toUpperCase() + t.slice(1) }}</option>
            </select>
            <span v-if="errors.type" class="text-red-500 text-xs">{{ errors.type }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
            <input v-model="form.amount" type="number" step="0.01" min="0" required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
            <span v-if="errors.amount" class="text-red-500 text-xs">{{ errors.amount }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Academic Year</label>
            <input v-model="form.academic_year" type="text" placeholder="e.g. 2025/2026" required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
            <span v-if="errors.academic_year" class="text-red-500 text-xs">{{ errors.academic_year }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Semester</label>
            <select v-model.number="form.semester" required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
              <option :value="1">Semester 1</option>
              <option :value="2">Semester 2</option>
              <option :value="3">Semester 3</option>
            </select>
            <span v-if="errors.semester" class="text-red-500 text-xs">{{ errors.semester }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
            <input v-model="form.due_date" type="date"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
            <span v-if="errors.due_date" class="text-red-500 text-xs">{{ errors.due_date }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
            <select v-model="form.status"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
              <option v-for="s in statuses" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
            </select>
            <span v-if="errors.status" class="text-red-500 text-xs">{{ errors.status }}</span>
          </div>
        </div>

        <div class="mt-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description / Notes</label>
          <textarea v-model="form.description" rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
            placeholder="Optional description..."></textarea>
        </div>
      </div>

      <div class="flex justify-end gap-4">
        <Link :href="route('hive.finance.invoices.index')"
          class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
          Cancel
        </Link>
        <button type="submit" :disabled="isSubmitting"
          class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg disabled:opacity-50">
          {{ isSubmitting ? 'Saving...' : 'Create Invoice' }}
        </button>
      </div>
    </form>
  </HiveLayout>
</template>
