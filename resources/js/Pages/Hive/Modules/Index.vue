<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
  modules: Array,
});

const page = usePage();
const canManage = computed(() => {
  const roles = page.props.auth?.user?.roles || [];
  return roles.includes('super-admin') || roles.includes('school-admin');
});

const isStudent = computed(() => {
  const roles = page.props.auth?.user?.roles || [];
  return roles.includes('student');
});
</script>

<template>
  <HiveLayout title="Modules" description="A list of all the modules in the system.">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Modules</h1>
      <Link v-if="canManage" :href="route('hive.modules.create')" class="btn-primary">
        Create Module
      </Link>
    </div>
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="w-full whitespace-no-wrap">
        <tr class="text-left font-bold">
          <th class="px-6 pt-6 pb-4">Name</th>
          <th class="px-6 pt-6 pb-4">Code</th>
          <th class="px-6 pt-6 pb-4">Department</th>
          <th class="px-6 pt-6 pb-4">Programme</th>
          <th class="px-6 pt-6 pb-4">Actions</th>
        </tr>
        <tr v-for="module in modules" :key="module.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t px-6 py-4">
            <span class="text-gray-900">{{ module.name }}</span>
          </td>
          <td class="border-t px-6 py-4">{{ module.code }}</td>
          <td class="border-t px-6 py-4">{{ module.department?.name }}</td>
          <td class="border-t px-6 py-4">{{ module.programme?.name }}</td>
          <td class="border-t px-6 py-4">
            <Link v-if="canManage" :href="route('hive.modules.edit', module.id)" class="text-indigo-600 hover:text-indigo-900">
              Edit
            </Link>
            <Link v-if="isStudent" :href="route('hive.enrollment.destroy', module.id)" method="delete" as="button" class="text-red-600 hover:text-red-900 ml-4">
              Deregister
            </Link>
          </td>
        </tr>
        <tr v-if="modules.length === 0">
          <td class="border-t px-6 py-4" colspan="5">No modules found.</td>
        </tr>
      </table>
    </div>
  </HiveLayout>
</template>