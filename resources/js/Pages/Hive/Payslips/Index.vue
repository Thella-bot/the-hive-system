<template>
  <HiveLayout>
    <div class="flex justify-between mb-4">
      <h1 class="text-2xl font-bold">Payslips</h1>
      <Link v-if="canUpload" :href="route('hive.payslips.create')" class="bg-indigo-600 text-white px-4 py-2 rounded">Upload</Link>
    </div>
    <table class="w-full bg-white shadow rounded">
      <thead><tr><th v-if="isHR">Employee</th><th>Month</th><th></th></tr></thead>
      <tbody>
        <tr v-for="slip in payslips" :key="slip.id">
          <td v-if="isHR">{{ slip.user.name }}</td>
          <td>{{ slip.month }}</td>
          <td><a :href="route('hive.payslips.download', slip.id)" class="text-blue-600 underline">Download</a></td>
        </tr>
      </tbody>
    </table>
  </HiveLayout>
</template>
<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
defineProps({ payslips: Array });
const canUpload = computed(() => usePage().props.user.roles.some(r => ['admin','hr_staff'].includes(r.name)));
const isHR = computed(() => usePage().props.user.roles.some(r => ['admin','hr_staff'].includes(r.name)));
</script>