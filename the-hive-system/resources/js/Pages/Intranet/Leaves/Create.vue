<template>
  <IntranetLayout>
    <h1 class="text-2xl font-bold mb-4">Request Leave</h1>
    <form @submit.prevent="form.post(route('intranet.leaves.store'))" class="max-w-md space-y-4">
      <div>
        <label>Type</label>
        <select v-model="form.type" class="w-full border rounded p-2">
          <option value="annual">Annual</option>
          <option value="sick">Sick</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div>
        <label>Start Date</label>
        <input type="date" v-model="form.start_date" class="w-full border rounded p-2" required />
      </div>
      <div>
        <label>End Date</label>
        <input type="date" v-model="form.end_date" class="w-full border rounded p-2" required />
      </div>
      <div>
        <label>Reason</label>
        <textarea v-model="form.reason" class="w-full border rounded p-2"></textarea>
      </div>
      <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Submit Request</button>
    </form>
  </IntranetLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';
const form = useForm({ type: 'annual', start_date: '', end_date: '', reason: '' });
</script>