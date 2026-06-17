<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import { useUser } from '@/composables/useUser';
import {
  MagnifyingGlassIcon,
  PencilSquareIcon,
  EyeIcon,
  UserPlusIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  students: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');

const applyFilters = () => router.get(route('hive.students.index'),
  { search: search.value },
  { preserveState: true, replace: true }
);

const { isAdmin } = useUser();

const formatCohort = (student) => student.profile?.cohort?.name ?? '—';
</script>

<template>
  <HiveLayout title="Students" description="All registered students">
    <template #header-actions>
      <Link :href="route('hive.students.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <UserPlusIcon class="w-4 h-4" />
        Add Student
      </Link>
    </template>

    <div class="mb-5 max-w-sm">
      <SearchInput v-model="search" @search="applyFilters" placeholder="Search students..." />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Student</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Cohort</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Enrolled</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="students.data.length === 0">
            <td colspan="4" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center">
                <div class="w-16 h-16 mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                  <UserPlusIcon class="w-8 h-8 text-gray-400" />
                </div>
                <p class="text-gray-500 dark:text-gray-400">{{ search ? 'No students match your search' : 'No students found' }}</p>
                <p v-if="!search" class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                  <Link :href="route('hive.students.create')" class="text-amber-600 hover:text-amber-700">Add a student</Link> to get started
                </p>
              </div>
            </td>
          </tr>
          <tr v-for="student in students.data" :key="student.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <img :src="student.profile_photo_url" :alt="student.name"
                  class="w-10 h-10 rounded-full object-cover flex-shrink-0 ring-2 ring-amber-100 dark:ring-amber-900" />
                <div>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ student.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ student.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden md:table-cell">
              {{ formatCohort(student) }}
            </td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 hidden lg:table-cell">
              {{ student.created_at ? new Date(student.created_at).toLocaleDateString() : '—' }}
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center justify-end gap-1">
                <Link :href="route('hive.users.show', { user: student.id })"
                  class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 transition" title="View">
                  <EyeIcon class="w-4 h-4" />
                </Link>
                <Link :href="route('hive.students.edit', { student: student.id })"
                  class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 transition" title="Edit">
                  <PencilSquareIcon class="w-4 h-4" />
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination v-if="students.data.length > 0" :links="students.links" :meta="students" />
  </HiveLayout>
</template>