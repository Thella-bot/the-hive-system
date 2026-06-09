<script setup>
import { Link } from '@inertiajs/vue3'
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
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Department
      </Link>
    </template>

    <!-- Empty state -->
    <div v-if="departments.data.length === 0"
      class="text-center py-16 bg-white rounded-xl border border-gray-200">
      <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
        </svg>
      </div>
      <h3 class="text-gray-900 font-semibold mb-1">No departments yet</h3>
      <p class="text-sm text-gray-500 mb-4">Create your first department to get started.</p>
      <Link v-if="isAdmin" :href="route('hive.departments.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        Create Department
      </Link>
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <div v-for="dept in departments.data" :key="dept.id"
        class="bg-white rounded-xl border border-gray-200 hover:shadow-md transition-shadow overflow-hidden">

        <!-- Color bar -->
        <div class="h-1.5" :style="{ backgroundColor: dept.color }"></div>

        <div class="p-5">
          <div class="flex items-start justify-between gap-3 mb-3">
            <div>
              <h3 class="font-semibold text-gray-900">{{ dept.name }}</h3>
              <p v-if="dept.head" class="text-xs text-gray-500 mt-0.5">Head: {{ dept.head.name }}</p>
            </div>
            <Badge :color="dept.is_active ? 'green' : 'gray'">
              {{ dept.is_active ? 'Active' : 'Inactive' }}
            </Badge>
          </div>

          <p v-if="dept.description" class="text-sm text-gray-500 line-clamp-2 mb-4">
            {{ dept.description }}
          </p>

          <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
            <span class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              {{ dept.active_cohort_count }} cohorts
            </span>
            <span class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              {{ dept.staff_count }} Academic Staff
            </span>
          </div>

          <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
            <Link :href="route('hive.departments.show', { department: dept.id })"
              class="flex-1 text-center text-sm text-amber-600 hover:text-amber-700 font-medium py-1.5 rounded-lg hover:bg-amber-50 transition-colors">
              View
            </Link>
            <Link v-if="isAdmin" :href="route('hive.departments.edit', { department: dept.id })"
              class="flex-1 text-center text-sm text-gray-600 hover:text-gray-700 font-medium py-1.5 rounded-lg hover:bg-gray-50 transition-colors">
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