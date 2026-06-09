<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Badge from '@/Components/Badge.vue';
import { useUser } from '@/composables/useUser';

const props = defineProps({
  shortCourses: { type: Object, required: true },
  departmentId: { type: Number, default: null },
});

const { isAdmin } = useUser();

const courseTypeColors = {
  workshop: 'bg-blue-100 text-blue-700',
  training: 'bg-purple-100 text-purple-700',
  'short-course': 'bg-orange-100 text-orange-700',
};

const typeLabels = {
  workshop: 'Workshop',
  training: 'Training',
  'short-course': 'Short Course',
};

const formatType = (type) => typeLabels[type] ?? type?.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) ?? '';
</script>

<template>
  <HiveLayout title="Short Courses" description="Manage workshops and short courses">
    <template #header-actions>
      <Link v-if="isAdmin" :href="route('hive.short-courses.create', departmentId ? { departmentId } : {})"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Short Course
      </Link>
    </template>

    <!-- Empty state -->
    <div v-if="shortCourses.data.length === 0"
      class="text-center py-16 bg-white rounded-xl border border-gray-200">
      <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <h3 class="text-gray-900 font-semibold mb-1">No short courses yet</h3>
      <p class="text-sm text-gray-500 mb-4">Create your first short course or workshop.</p>
      <Link v-if="isAdmin" :href="route('hive.short-courses.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        Create Short Course
      </Link>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full whitespace-no-wrap">
        <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide bg-gray-50">
          <th class="px-6 py-4">Title</th>
          <th class="px-6 py-4">Type</th>
          <th class="px-6 py-4">Duration</th>
          <th class="px-6 py-4">Price</th>
          <th class="px-6 py-4">Owner</th>
          <th class="px-6 py-4">Status</th>
          <th class="px-6 py-4">Actions</th>
        </tr>
        <tr v-for="course in shortCourses.data" :key="course.id"
          class="border-t border-gray-100 hover:bg-gray-50">
          <td class="px-6 py-4">
            <div>
              <p class="text-sm font-medium text-gray-900">{{ course.title }}</p>
              <p v-if="course.description" class="text-xs text-gray-400 line-clamp-1">{{ course.description }}</p>
            </div>
          </td>
          <td class="px-6 py-4">
            <span class="px-2 py-1 text-xs font-semibold rounded-full"
              :class="courseTypeColors[course.type] || 'bg-gray-100 text-gray-700'">
              {{ formatType(course.type) }}
            </span>
          </td>
          <td class="px-6 py-4 text-sm text-gray-600">{{ course.duration }}</td>
          <td class="px-6 py-4 text-sm text-gray-600">
            <span v-if="course.price > 0">LSL {{ parseFloat(course.price).toLocaleString() }}</span>
            <span v-else class="text-gray-400">Free</span>
          </td>
          <td class="px-6 py-4 text-sm">
            <span v-if="course.courseable">{{ course.courseable.name }}</span>
            <span v-else class="text-gray-400">—</span>
          </td>
          <td class="px-6 py-4">
            <div class="flex flex-col gap-1">
              <Badge :color="course.is_active ? 'green' : 'gray'" class="text-xs">
                {{ course.is_active ? 'Active' : 'Inactive' }}
              </Badge>
              <Badge v-if="course.accepting_applications" color="blue" class="text-xs">
                Open
              </Badge>
            </div>
          </td>
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <Link v-if="isAdmin" :href="route('hive.short-courses.edit', { short_course: course.id })"
                class="text-sm text-amber-600 hover:text-amber-700 font-medium">
                Edit
              </Link>
              <Link v-if="isAdmin" :href="route('hive.short-courses.destroy', { short_course: course.id })"
                method="delete" as="button"
                class="text-sm text-red-600 hover:text-red-700 font-medium">
                Delete
              </Link>
            </div>
          </td>
        </tr>
      </table>

      <Pagination
        v-if="shortCourses.data.length > 0"
        :links="shortCourses.links"
        :meta="shortCourses.meta"
      />
    </div>
  </HiveLayout>
</template>
