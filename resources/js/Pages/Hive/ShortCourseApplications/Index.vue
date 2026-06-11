<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { DocumentIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Badge from '@/Components/Badge.vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { useUser } from '@/composables/useUser';

dayjs.extend(relativeTime);

const props = defineProps({
  shortCourseApplications: { type: Object, required: true },
  filter: { type: String, default: 'pending' },
});

const { isAdmin } = useUser();

const filters = [
  { value: 'pending', label: 'Pending' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' },
  { value: 'all', label: 'All' },
];

const statusColors = {
  pending: 'yellow',
  approved: 'green',
  rejected: 'red',
};

const statusLabels = {
  pending: 'Pending',
  approved: 'Approved',
  rejected: 'Rejected',
};

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
const formatDate = (date) => date ? dayjs(date).fromNow() : '—';

const review = (app, status) => {
  router.post(
    route('hive.short-course-applications.review', { short_course_application: app.id }),
    { status },
    { preserveScroll: true }
  );
};
</script>

<template>
  <HiveLayout title="Short Course Applications" description="View and manage applications for short courses">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Short Course Applications</h1>
      <Link :href="route('hive.short-courses.index')"
        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
        title="Short Courses">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
      </Link>
    </div>

    <!-- Filter tabs -->
    <div class="flex gap-2 mb-6">
      <button v-for="f in filters" :key="f.value"
        @click="$inertia.visit(route('hive.short-course-applications.index', { filter: f.value }), { preserveScroll: true })"
        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
        :class="filter === f.value
          ? 'bg-amber-600 text-white'
          : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'">
        {{ f.label }}
      </button>
    </div>

    <!-- Empty state -->
    <div v-if="shortCourseApplications.data.length === 0"
      class="text-center py-16 bg-white rounded-xl border border-gray-200">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <DocumentIcon class="w-8 h-8 text-gray-400" />
      </div>
      <h3 class="text-gray-900 font-semibold mb-1">No applications found</h3>
      <p class="text-sm text-gray-500">
        {{ filter === 'pending' ? 'No pending applications at the moment.' : 'No applications match this filter.' }}
      </p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full whitespace-no-wrap">
        <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide bg-gray-50">
          <th class="px-6 py-4">Applicant</th>
          <th class="px-6 py-4">Short Course</th>
          <th class="px-6 py-4">Applied</th>
          <th class="px-6 py-4">Status</th>
          <th class="px-6 py-4">Actions</th>
        </tr>
        <tr v-for="app in shortCourseApplications.data" :key="app.id"
          class="border-t border-gray-100 hover:bg-gray-50">
          <td class="px-6 py-4">
            <div>
              <p class="text-sm font-medium text-gray-900">{{ app.name }}</p>
              <p class="text-xs text-gray-400">{{ app.email }}</p>
              <p v-if="app.phone" class="text-xs text-gray-400">{{ app.phone }}</p>
            </div>
          </td>
          <td class="px-6 py-4">
            <div v-if="app.short_course">
              <p class="text-sm font-medium text-gray-900">{{ app.short_course.title }}</p>
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 text-xs rounded-full"
                  :class="courseTypeColors[app.short_course.type] || 'bg-gray-100 text-gray-700'">
                  {{ formatType(app.short_course.type) }}
                </span>
                <span class="text-xs text-gray-400">{{ app.short_course.duration }}</span>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 text-sm text-gray-500">
            {{ formatDate(app.applied_at) }}
          </td>
          <td class="px-6 py-4">
            <Badge :color="statusColors[app.status] || 'gray'">
              {{ statusLabels[app.status] ?? app.status }}
            </Badge>
          </td>
          <td class="px-6 py-4">
            <div v-if="app.notes" class="text-xs text-gray-500 mb-2 max-w-xs truncate">{{ app.notes }}</div>
            <div v-if="isAdmin && app.status === 'pending'" class="flex items-center gap-2">
              <button @click="review(app, 'approved')"
                class="px-3 py-1 text-xs font-medium rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                Approve
              </button>
              <button @click="review(app, 'rejected')"
                class="px-3 py-1 text-xs font-medium rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition">
                Reject
              </button>
            </div>
          </td>
        </tr>
      </table>

      <Pagination
        v-if="shortCourseApplications.data.length > 0"
        :links="shortCourseApplications.links"
        :meta="shortCourseApplications.meta"
      />
    </div>
  </HiveLayout>
</template>
