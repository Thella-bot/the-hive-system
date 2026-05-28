<template>
  <HiveLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-4">Approve Registrations</h1>
      <table class="min-w-full">
        <thead><tr><th>Name</th><th>Email</th><th>Assign Role</th><th>Action</th></tr></thead>
        <tbody>
          <tr v-for="user in unapprovedUsers" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>
              <select v-model="selectedRoles[user.id]" class="border rounded">
                <option value="student">Student</option>
                <option value="academic_staff">Academic Staff</option>
                <option value="non_academic_staff">Non-Academic Staff</option>
                <option value="department-head">Department Head</option>
                <option value="chef-instructor">Chef Instructor</option>
              </select>
            </td>
            <td>
              <button @click="approve(user.id)" class="px-2 py-1 bg-amber-600 text-white rounded hover:bg-amber-700">Approve</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </HiveLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({ unapprovedUsers: Array });
const selectedRoles = ref({});

props.unapprovedUsers.forEach(u => selectedRoles.value[u.id] = 'student');

const approve = (userId) => {
  router.post(route('hive.admin.approve-users.approve', userId), {
    role: selectedRoles.value[userId]
  });
};
</script>