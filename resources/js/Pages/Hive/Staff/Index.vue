<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { useUser } from '@/composables/useUser';
import {
  MagnifyingGlassIcon,
  PlusIcon,
  PencilSquareIcon,
  EyeIcon,
  UserGroupIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  staff: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const { isAdmin } = useUser();
const search = ref(props.filters.search ?? '');

const applyFilters = () => router.get(route('hive.staff.index'),
  { search: search.value },
  { preserveState: true, replace: true }
);

const getRoleName = (roles) => {
  if (!roles || roles.length === 0) return 'N/A';
  return roles[0].name.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};
</script>

<template>
  <HiveLayout title="Staff" description="All staff members">
    <template #header-actions>
      <Link v-if="isAdmin" :href="route('hive.staff.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        Add Staff
      </Link>
    </template>

    <div class="mb-5 max-w-sm">
      <div class="relative">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
        <input v-model="search" @input="applyFilters" type="text" placeholder="Search staff..."
          class="w-full pl-9 pr-3.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" />
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Staff Member</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Role</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="staff.data.length === 0">
            <td colspan="3" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center">
                <div class="w-16 h-16 mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                  <UserGroupIcon class="w-8 h-8 text-gray-400" />
                </div>
                <p class="text-gray-500 dark:text-gray-400">{{ search ? 'No staff match your search' : 'No staff found' }}</p>
              </div>
            </td>
          </tr>
          <tr v-for="staffMember in staff.data" :key="staffMember.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <img :src="staffMember.profile_photo_url" :alt="staffMember.name"
                  class="w-10 h-10 rounded-full object-cover flex-shrink-0 ring-2 ring-amber-100 dark:ring-amber-900" />
                <div>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ staffMember.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ staffMember.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden md:table-cell">
              {{ getRoleName(staffMember.roles) }}
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center justify-end gap-1">
                <Link :href="route('hive.users.show', { user: staffMember.id })"
                  class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 transition" title="View">
                  <EyeIcon class="w-4 h-4" />
                </Link>
                <Link :href="route('hive.staff.edit', { staff: staffMember.id })"
                  class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 transition" title="Edit">
                  <PencilSquareIcon class="w-4 h-4" />
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination v-if="staff.data.length > 0" :links="staff.links" :meta="staff" />
  </HiveLayout>
</template>