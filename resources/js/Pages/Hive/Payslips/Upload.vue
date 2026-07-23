<template>
  <HiveLayout>
    <h1 class="text-2xl font-bold mb-4">Upload Payslip</h1>
    <form @submit.prevent="form.post(route('hive.payslips.store'))" class="max-w-md space-y-4">
      <div>
        <label>Employee</label>
        <select v-model="form.user_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white">
          <option v-for="user in users" :value="user.id">{{ user.name }}</option>
        </select>
      </div>
      <div>
        <label>Month (YYYY-MM)</label>
        <input type="month" v-model="form.month" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" required />
      </div>
      <div>
        <label>PDF File</label>
        <input type="file" @input="form.file = $event.target.files[0]" accept="application/pdf" required class="w-full" />
      </div>
      <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition-colors">Upload</button>
    </form>
  </HiveLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
defineProps({ users: Array });
const form = useForm({ user_id: '', month: '', file: null });
</script>