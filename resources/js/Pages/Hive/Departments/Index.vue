<script setup>
import { Link } from '@inertiajs/vue3'
import { PlusIcon, BuildingOffice2Icon, UsersIcon } from '@heroicons/vue/24/outline'
import HiveLayout from '@/Layouts/HiveLayout.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'
import { useUser } from '@/composables/useUser';

defineProps({
  departments: { type: Object, required: true },
})

const { isAdmin } = useUser();
</script>

<template>
  <HiveLayout title="Departments" description="Manage culinary departments and their heads">
    <template #header-actions>
      <Link v-if="isAdmin" :href="route('hive.departments.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        New Department
      </Link>
    </template>

    <!-- Empty state -->
    <div v-if="departments.data.length === 0"
      class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
      <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/50 rounded-full flex items-center justify-center mx-auto mb-4">
        <BuildingOffice2Icon class="w-8 h-8 text-amber-500 dark:text-amber-400" />
      </div>
      <h3 class="text-gray-900 dark:text-gray-100 font-semibold mb-1">No departments yet</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Create your first department to get started.</p>
      <Link v-if="isAdmin" :href="route('hive.departments.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        Create Department
      </Link>
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <div v-for="dept in departments.data" :key="dept.id"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-md dark:hover:shadow-gray-900/30 transition-shadow overflow-hidden">

        <!-- Color bar -->
        <div class="h-1.5" :style="{ backgroundColor: dept.color }"></div>

        <div class="p-5">
          <div class="flex items-start justify-between gap-3 mb-3">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ dept.name }}</h3>
              <p v-if="dept.head" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Head: {{ dept.head.name }}</p>
            </div>
            <Badge :color="dept.is_active ? 'green' : 'gray'">
              {{ dept.is_active ? 'Active' : 'Inactive' }}
            </Badge>
          </div>

          <p v-if="dept.description" class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-4">
            {{ dept.description }}
          </p>

          <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-4">
            <span class="flex items-center gap-1">
              <UsersIcon class="w-4 h-4" />
              {{ dept.active_cohort_count }} cohorts
            </span>
            <span class="flex items-center gap-1">
              <UsersIcon class="w-4 h-4" />
              {{ dept.staff_count }} Academic Staff
            </span>
          </div>

          <div class="flex items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
            <Link :href="route('hive.departments.show', { department: dept.id })"
              class="flex-1 text-center text-sm text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 font-medium py-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
              View
            </Link>
            <Link v-if="isAdmin" :href="route('hive.departments.edit', { department: dept.id })"
              class="flex-1 text-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 font-medium py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
              Edit
            </Link>
          </div>
        </div>
      </div>
    </div>

    <Pagination
      v-if="departments.data.length > 0"
      :links="departments.links"
      :meta="departments.meta"
    />
  </HiveLayout>
</template>