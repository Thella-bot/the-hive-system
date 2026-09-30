<script setup>
import { ref, watch, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import {
  EyeIcon,
  UserPlusIcon,
  IdentificationIcon,
  PencilSquareIcon,
  ArrowDownTrayIcon,
  FunnelIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  students: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  canExport: { type: Boolean, default: false },
  exportUrl: { type: String, default: '' },
  filterOptions: {
    type: Object,
    default: () => ({ programmes: [], cohorts: [], departments: [], statuses: [] }),
  },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const programmeId = ref(props.filters.programme_id ?? '');
const cohortId = ref(props.filters.cohort_id ?? '');
const departmentId = ref(props.filters.department_id ?? '');
const showFilters = ref(
  Boolean(status.value || programmeId.value || cohortId.value || departmentId.value)
);

const activeFilterCount = computed(
  () => [status.value, programmeId.value, cohortId.value, departmentId.value].filter(Boolean).length
);

const hasFilters = computed(() => Boolean(search.value) || activeFilterCount.value > 0);

const currentFilters = () => ({
  ...(search.value ? { search: search.value } : {}),
  ...(status.value ? { status: status.value } : {}),
  ...(programmeId.value ? { programme_id: programmeId.value } : {}),
  ...(cohortId.value ? { cohort_id: cohortId.value } : {}),
  ...(departmentId.value ? { department_id: departmentId.value } : {}),
});

const applyFilters = () =>
  router.get(route('hive.students.index'), currentFilters(), {
    preserveState: true,
    replace: true,
  });

// Inertia's search component already debounces typing, but changing a dropdown
// should apply immediately rather than waiting for a second interaction.
watch([status, programmeId, cohortId, departmentId], applyFilters);

const clearFilters = () => {
  search.value = '';
  status.value = '';
  programmeId.value = '';
  cohortId.value = '';
  departmentId.value = '';
  applyFilters();
};

// The export link carries the active filters, so the file matches the view.
const exportHref = computed(() => {
  const params = new URLSearchParams(currentFilters()).toString();
  return params ? `${props.exportUrl}?${params}` : props.exportUrl;
});

const formatCohort = (student) => student.profile?.cohort?.name ?? '—';
const formatDate = (value) => (value ? new Date(value).toLocaleDateString() : '—');
</script>

<template>
  <HiveLayout title="Students" description="All registered students">
    <template #header-actions>
      <a
        v-if="canExport"
        :href="exportHref"
        class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 transition-colors"
        title="Download the student register as CSV, using the filters currently applied"
      >
        <ArrowDownTrayIcon class="w-4 h-4" />
        Export CSV
      </a>
      <Link :href="route('hive.students.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <UserPlusIcon class="w-4 h-4" />
        Add Student
      </Link>
    </template>

    <div class="mb-5 flex flex-wrap items-center gap-3">
      <div class="max-w-sm flex-1">
        <SearchInput v-model="search" @search="applyFilters" placeholder="Search name, email or number..." />
      </div>

      <button
        type="button"
        @click="showFilters = !showFilters"
        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
      >
        <FunnelIcon class="w-4 h-4" />
        Filters
        <span
          v-if="activeFilterCount"
          class="ml-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-amber-600 text-white text-xs"
        >{{ activeFilterCount }}</span>
      </button>

      <button
        v-if="hasFilters"
        type="button"
        @click="clearFilters"
        class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-500 hover:text-amber-700 dark:text-gray-400 dark:hover:text-amber-400 transition-colors"
      >
        <XMarkIcon class="w-4 h-4" />
        Clear
      </button>
    </div>

    <div v-if="showFilters" class="mb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
      <div>
        <label for="filter-status" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Status</label>
        <select id="filter-status" v-model="status"
          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
          <option value="">All statuses</option>
          <option v-for="option in filterOptions.statuses" :key="option" :value="option">{{ option }}</option>
        </select>
      </div>

      <div>
        <label for="filter-programme" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Programme</label>
        <select id="filter-programme" v-model="programmeId"
          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
          <option value="">All programmes</option>
          <option v-for="option in filterOptions.programmes" :key="option.value" :value="option.value">{{ option.label }}</option>
        </select>
      </div>

      <div>
        <label for="filter-department" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Department</label>
        <select id="filter-department" v-model="departmentId"
          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
          <option value="">All departments</option>
          <option v-for="option in filterOptions.departments" :key="option.value" :value="option.value">{{ option.label }}</option>
        </select>
      </div>

      <div>
        <label for="filter-cohort" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Cohort</label>
        <select id="filter-cohort" v-model="cohortId"
          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
          <option value="">All cohorts</option>
          <option v-for="option in filterOptions.cohorts" :key="option.value" :value="option.value">{{ option.label }}</option>
        </select>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Student</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden xl:table-cell">Number</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Programme</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Cohort</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Enrolled</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="students.data.length === 0">
            <td colspan="6" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center">
                <div class="w-16 h-16 mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                  <UserPlusIcon class="w-8 h-8 text-gray-400" />
                </div>
                <p class="text-gray-500 dark:text-gray-400">{{ hasFilters ? 'No students match these filters' : 'No students found' }}</p>
                <p v-if="hasFilters" class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                  <button type="button" @click="clearFilters" class="text-amber-600 hover:text-amber-700">Clear the filters</button>
                  to see everyone
                </p>
                <p v-else class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                  <Link :href="route('hive.students.create')" class="text-amber-600 hover:text-amber-700">Add a student</Link> to get started
                </p>
              </div>
            </td>
          </tr>
          <tr v-for="student in students.data" :key="student.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <img :src="student.profile_photo_url" :alt="student.name"
                  loading="lazy"
                  class="w-10 h-10 rounded-full object-cover flex-shrink-0 ring-2 ring-amber-100 dark:ring-amber-900" />
                <div>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ student.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ student.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 font-mono text-xs hidden xl:table-cell">
              {{ student.student_number ?? student.profile?.student_number ?? '—' }}
            </td>
            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden lg:table-cell">
              {{ student.programme?.name ?? '—' }}
            </td>
            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden md:table-cell">
              {{ formatCohort(student) }}
            </td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 hidden lg:table-cell">
              {{ formatDate(student.profile?.enrollment_date ?? student.created_at) }}
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center justify-end gap-1">
                <Link :href="route('hive.users.show', { user: student.id })"
                  class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 transition" title="View">
                  <EyeIcon class="w-4 h-4" />
                </Link>
                <Link :href="route('hive.students.id-card', { student: student.id })"
                  class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 transition" title="Print ID Card">
                  <IdentificationIcon class="w-4 h-4" />
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

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
      <p v-if="canExport" class="text-xs text-gray-500 dark:text-gray-400">
        Exporting downloads the {{ students.total ?? 0 }} student{{ (students.total ?? 0) === 1 ? '' : 's' }} matching these filters.
      </p>
      <Pagination v-if="students.data.length > 0" :links="students.links" :meta="students" />
    </div>
  </HiveLayout>
</template>
