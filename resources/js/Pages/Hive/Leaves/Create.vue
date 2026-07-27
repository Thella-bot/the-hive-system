<template>
  <HiveLayout>
    <h1 class="text-2xl font-bold mb-6">Request Leave</h1>
    <form @submit.prevent="form.post(route('hive.leaves.store'))" class="max-w-md space-y-5">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
        <select v-model="form.type" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white">
          <option value="annual">Annual Leave</option>
          <option value="sick">Sick Leave</option>
          <option value="maternity">Maternity Leave</option>
          <option value="paternity">Paternity Leave</option>
          <option value="compassionate">Compassionate Leave</option>
          <option value="study">Study Leave</option>
          <option value="other">Other</option>
        </select>
        <InputError :message="form.errors.type" class="mt-1" />
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" v-model="form.half_day" id="half_day" class="rounded border-gray-300 text-amber-600 shadow-sm" />
        <label for="half_day" class="text-sm text-gray-700">Half-day leave (start and end date are the same)</label>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
        <input type="date" v-model="form.start_date" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" required />
        <InputError :message="form.errors.start_date" class="mt-1" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
        <input type="date" v-model="form.end_date" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" required />
        <InputError :message="form.errors.end_date" class="mt-1" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-gray-400">(optional)</span></label>
        <textarea v-model="form.reason" rows="3" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white"></textarea>
        <InputError :message="form.errors.reason" class="mt-1" />
      </div>

      <div>
        <PrimaryButton :disabled="form.processing" class="bg-amber-600 hover:bg-amber-700">
          Submit Request
        </PrimaryButton>
      </div>
    </form>
  </HiveLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
  type: 'annual',
  half_day: false,
  start_date: '',
  end_date: '',
  reason: '',
});
</script>