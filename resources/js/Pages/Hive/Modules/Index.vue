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
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Modules</h1>
      <Link v-if="canManage" :href="route('hive.modules.create')" class="btn-primary">
        Create Module
      </Link>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-x-auto">
      <table class="w-full whitespace-no-wrap">
        <tr class="text-left font-bold text-xs uppercase tracking-wide text-gray-500">
          <th class="px-6 pt-6 pb-4">Name</th>
          <th class="px-6 pt-6 pb-4">Code</th>
          <th class="px-6 pt-6 pb-4">Department</th>
          <th class="px-6 pt-6 pb-4">Programme</th>
          <th class="px-6 pt-6 pb-4">Actions</th>
        </tr>
        <tr v-for="module in modules" :key="module.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
          <td class="border-t border-gray-100 dark:border-gray-700 px-6 py-4">
            <span class="text-gray-900 dark:text-gray-100">{{ module.name }}</span>
          </td>
          <td class="border-t border-gray-100 dark:border-gray-700 px-6 py-4 text-gray-700 dark:text-gray-300">{{ module.code }}</td>
          <td class="border-t border-gray-100 dark:border-gray-700 px-6 py-4 text-gray-600 dark:text-gray-400">{{ module.department?.name }}</td>
          <td class="border-t border-gray-100 dark:border-gray-700 px-6 py-4">
            <span v-if="module.programmes?.length" class="flex flex-wrap gap-1">
              <span v-for="p in module.programmes" :key="p.id" class="inline-flex items-center px-2 py-0.5 bg-amber-50 text-amber-700 text-xs rounded dark:bg-amber-900/40 dark:text-amber-300">
                {{ p.name }}
              </span>
            </span>
            <span v-else class="text-gray-400 dark:text-gray-500">—</span>
          </td>
          <td class="border-t border-gray-100 dark:border-gray-700 px-6 py-4">
            <Link v-if="canManage" :href="route('hive.modules.edit', { module: module.id })" class="text-amber-600 hover:text-amber-700 dark:text-amber-400">
              Edit
            </Link>
            <Link v-if="isStudent" :href="route('hive.enrollment.destroy', { module: module.id })" method="delete" as="button" class="text-red-600 hover:text-red-900 ml-4 dark:text-red-400">
              Deregister
            </Link>
          </td>
        </tr>
        <tr v-if="modules.length === 0">
          <td class="border-t border-gray-100 dark:border-gray-700 px-6 py-4 text-center text-gray-500 dark:text-gray-400" colspan="5">No modules found.</td>
        </tr>
      </table>
    </div>
  </HiveLayout>
</template>