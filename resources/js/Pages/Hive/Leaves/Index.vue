<template>
  <HiveLayout>
    <div class="flex justify-between mb-4">
      <h1 class="text-2xl font-bold">Leave Requests <span class="text-sm">(Balance: {{ balance }} days)</span></h1>
      <Link :href="route('hive.leaves.create')" class="bg-indigo-600 text-white px-4 py-2 rounded">Request Leave</Link>
    </div>
    <table class="w-full bg-white shadow rounded">
      <thead><tr><th>Start</th><th>End</th><th>Type</th><th>Days</th><th>Status</th><th v-if="isHR">Action</th></tr></thead>
      <tbody>
        <tr v-for="leave in leaves" :key="leave.id">
          <td>{{ leave.start_date }}</td>
          <td>{{ leave.end_date }}</td>
          <td>{{ leave.type }}</td>
          <td>{{ leave.days }}</td>
          <td>{{ leave.status }}</td>
          <td v-if="isHR && leave.status === 'pending'">
            <button @click="update(leave.id, 'approved')" class="text-green-600 mr-2">Approve</button>
            <button @click="update(leave.id, 'rejected')" class="text-red-600">Reject</button>
          </td>
        </tr>
      </tbody>
    </table>
  </HiveLayout>
</template>
<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({ leaves: Array, balance: Number });
const isHR = computed(() => usePage().props.user.roles.some(r => ['admin','hr_staff'].includes(r.name)));

const update = (leaveId, status) => {
  router.post(route('hive.leaves.update', leaveId), { status });
};
</script>
