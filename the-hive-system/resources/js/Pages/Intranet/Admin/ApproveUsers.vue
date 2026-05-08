<template>
  <IntranetLayout>
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
                <option value="instructor">Instructor</option>
                <option value="hr_staff">HR Staff</option>
              </select>
            </td>
            <td>
              <button @click="approve(user.id)" class="px-2 py-1 bg-green-500 text-white rounded">Approve</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </IntranetLayout>
</template>

<script setup>
import { Inertia } from '@inertiajs/inertia';
import { ref } from 'vue';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';

const props = defineProps({ unapprovedUsers: Array });
const selectedRoles = ref({});

props.unapprovedUsers.forEach(u => selectedRoles.value[u.id] = 'student');

const approve = (userId) => {
  Inertia.post(route('admin.approve-user', userId), {
    role: selectedRoles.value[userId]
  });
};
</script>