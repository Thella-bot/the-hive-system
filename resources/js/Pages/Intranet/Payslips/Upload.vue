<template>
  <IntranetLayout>
    <h1 class="text-2xl font-bold mb-4">Upload Payslip</h1>
    <form @submit.prevent="form.post(route('intranet.payslips.store'))" class="max-w-md space-y-4">
      <div>
        <label>Employee</label>
        <select v-model="form.user_id" class="w-full border rounded p-2">
          <option v-for="user in users" :value="user.id">{{ user.name }}</option>
        </select>
      </div>
      <div>
        <label>Month (YYYY-MM)</label>
        <input type="month" v-model="form.month" class="w-full border rounded p-2" required />
      </div>
      <div>
        <label>PDF File</label>
        <input type="file" @input="form.file = $event.target.files[0]" accept="application/pdf" required class="w-full" />
      </div>
      <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Upload</button>
    </form>
  </IntranetLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';
defineProps({ users: Array });
const form = useForm({ user_id: '', month: '', file: null });
</script>