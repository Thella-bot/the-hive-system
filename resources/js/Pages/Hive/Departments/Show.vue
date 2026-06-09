<template>
  <HiveLayout :title="department.name" :description="department.description ?? 'No description'">
    <template #header-actions>
      <div class="flex items-center gap-3">
        <Link :href="route('hive.departments.index')" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
          ← Departments
        </Link>
        <Link v-if="can('edit-departments')" :href="route('hive.departments.edit', { department: department.id })"
          class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
          Edit
        </Link>
      </div>
    </template>

    <div class="space-y-6">

      <!-- Overview cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
          <div class="w-10 h-10 rounded-lg flex items-center justify-center" :style="{ backgroundColor: department.color + '22' }">
            <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: department.color }"></div>
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900">{{ department.cohorts_count }}</p>
            <p class="text-xs text-gray-500">Total Cohorts</p>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
          <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
            <UsersIcon class="w-5 h-5 text-purple-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900">{{ department.staff_count }}</p>
            <p class="text-xs text-gray-500">Academic Staff</p>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
          <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
            <CheckCircleIcon class="w-5 h-5 text-green-600" />
          </div>
          <div>
            <Badge :color="department.is_active ? 'green' : 'gray'" class="text-sm">
              {{ department.is_active ? 'Active' : 'Inactive' }}
            </Badge>
            <p class="text-xs text-gray-500 mt-1">Status</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Cohorts -->
        <div class="xl:col-span-2 bg-white rounded-xl border border-gray-200">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Cohorts</h2>
            <Link v-if="can('create-cohorts')" :href="route('hive.cohorts.create')"
              class="text-sm text-amber-600 hover:text-amber-700 font-medium">+ Add Cohort</Link>
          </div>
          <div class="divide-y divide-gray-50">
            <div v-if="department.cohorts.length === 0"
              class="px-6 py-10 text-center text-sm text-gray-400">
              No cohorts in this department yet.
            </div>
            <div v-for="cohort in department.cohorts" :key="cohort.id"
              class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ cohort.name }}</p>
                <p class="text-xs text-gray-400">{{ cohort.academic_year?.name }}</p>
              </div>
              <div class="ml-auto flex items-center gap-3">
                <span class="text-sm text-gray-500">{{ cohort.students_count }} students</span>
                <Badge :color="cohort.is_active ? 'green' : 'gray'">
                  {{ cohort.is_active ? 'Closed' : 'Active' }}
                </Badge>
                <Link :href="route('hive.cohorts.show', { cohort: cohort.id })"
                  class="text-amber-600 hover:text-amber-700 text-sm font-medium">View →</Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Staff -->
        <div class="bg-white rounded-xl border border-gray-200">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Staff</h2>
          </div>
          <div class="divide-y divide-gray-50">
            <div v-if="department.staff.length === 0"
              class="px-6 py-10 text-center text-sm text-gray-400">
              No staff assigned yet.
            </div>
            <div v-for="member in department.staff" :key="member.id"
              class="flex items-center gap-3 px-6 py-4 hover:bg-gray-50 transition-colors">
              <img :src="member.user.profile_photo_url" :alt="member.user.name"
                class="w-8 h-8 rounded-full object-cover flex-shrink-0" />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ member.user.name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ member.designation ?? 'Instructor' }}</p>
              </div>
              <Link :href="route('hive.users.show', { user: member.user_id })"
                class="text-gray-400 hover:text-amber-600 transition-colors">
                <ChevronRightIcon class="w-4 h-4" />
              </Link>
            </div>
          </div>
        </div>

      </div>

      <!-- Short Courses -->
      <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Short Courses & Workshops</h2>
          <Link v-if="can('edit-departments')" :href="route('hive.short-courses.create', { departmentId: department.id })"
            class="text-sm text-amber-600 hover:text-amber-700 font-medium">+ Add Short Course</Link>
        </div>
        <div class="divide-y divide-gray-50">
          <div v-if="!department.short_courses || department.short_courses.length === 0"
            class="px-6 py-10 text-center text-sm text-gray-400">
            No short courses for this department yet.
          </div>
          <div v-for="course in department.short_courses" :key="course.id"
            class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <p class="text-sm font-medium text-gray-900">{{ course.title }}</p>
                <span class="px-2 py-0.5 text-xs rounded-full"
                  :class="{
                    'bg-blue-100 text-blue-700': course.type === 'workshop',
                    'bg-purple-100 text-purple-700': course.type === 'training',
                    'bg-orange-100 text-orange-700': course.type === 'short-course'
                  }">
                  {{ course.type?.replace('-', ' ') }}
                </span>
              </div>
              <p class="text-xs text-gray-400">{{ course.duration }}</p>
            </div>
            <div class="flex items-center gap-3">
              <span v-if="course.price > 0" class="text-sm text-gray-600">LSL {{ parseFloat(course.price).toLocaleString() }}</span>
              <Badge v-if="course.accepting_applications" color="green" class="text-xs">Open</Badge>
              <Badge v-else color="gray" class="text-xs">Closed</Badge>
            </div>
            <Link v-if="can('edit-departments')" :href="route('hive.short-courses.edit', { short_course: course.id })"
              class="text-amber-600 hover:text-amber-700 text-sm font-medium">Edit →</Link>
          </div>
        </div>
      </div>

      <!-- Head info -->
      <div v-if="department.head" class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-center gap-4">
        <img :src="department.head.profile_photo_url" :alt="department.head.name"
          class="w-12 h-12 rounded-full object-cover" />
        <div>
          <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-0.5">Department Head</p>
          <p class="font-semibold text-gray-900">{{ department.head.name }}</p>
          <p class="text-sm text-gray-500">{{ department.head.email }}</p>
        </div>
      </div>

    </div>
  </HiveLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { UsersIcon, CheckCircleIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import HiveLayout from '@/Layouts/HiveLayout.vue'
import Badge from '@/Components/Badge.vue'
import { usePermissions } from '@/composables/usePermissions'

defineProps({
  department: { type: Object, required: true },
})

const { can } = usePermissions()
</script>