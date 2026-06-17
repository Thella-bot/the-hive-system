<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { PlusIcon, ClockIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';
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
  workshop: 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',
  training: 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300',
  'short-course': 'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300',
};

const typeLabels = {
  workshop: 'Workshop',
  training: 'Training',
  'short-course': 'Short Course',
};

const formatType = (type) => typeLabels[type] ?? type?.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) ?? '';
</script>

<template>
  <HiveLayout title="Short Courses" description="Workshops and short courses">
    <template #header-actions>
      <Link v-if="isAdmin" :href="route('hive.short-courses.create', departmentId ? { departmentId } : {})"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        New Short Course
      </Link>
    </template>

    <!-- Empty state -->
    <div v-if="shortCourses.data.length === 0"
      class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
      <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
        <ClockIcon class="w-8 h-8 text-orange-500" />
      </div>
      <h3 class="text-gray-900 dark:text-white font-semibold mb-1">No short courses yet</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Create your first short course or workshop.</p>
      <Link v-if="isAdmin" :href="route('hive.short-courses.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        Create Short Course
      </Link>
    </div>

    <!-- Table -->
    <div v-else class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Title</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Type</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Duration</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Price</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Owner</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="course in shortCourses.data" :key="course.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
            <td class="px-6 py-4">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ course.title }}</p>
                <p v-if="course.description" class="text-xs text-gray-400 dark:text-gray-500 line-clamp-1">{{ course.description }}</p>
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="courseTypeColors[course.type] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'">
                {{ formatType(course.type) }}
              </span>
            </td>
            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden md:table-cell">{{ course.duration }}</td>
            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden lg:table-cell">
              <span v-if="course.price > 0">LSL {{ parseFloat(course.price).toLocaleString() }}</span>
              <span v-else class="text-gray-400">Free</span>
            </td>
            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden lg:table-cell">
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
              <div class="flex items-center justify-end gap-2">
                <Link v-if="isAdmin" :href="route('hive.short-courses.edit', { short_course: course.id })"
                  class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 transition" title="Edit">
                  <PencilSquareIcon class="w-4 h-4" />
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination
        v-if="shortCourses.data.length > 0"
        :links="shortCourses.links"
        :meta="shortCourses.meta"
      />
    </div>
  </HiveLayout>
</template>
