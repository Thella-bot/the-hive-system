<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { DocumentIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
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
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Short Course Applications</h1>
      <Link :href="route('hive.short-courses.index')"
        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
        title="Short Courses">
        <ArrowLeftIcon class="w-5 h-5" />
      </Link>
    </div>

    <!-- Filter tabs -->
    <div class="flex gap-2 mb-6 flex-wrap">
      <button v-for="f in filters" :key="f.value"
        @click="$inertia.visit(route('hive.short-course-applications.index', { filter: f.value }), { preserveScroll: true })"
        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
        :class="filter === f.value
          ? 'bg-amber-600 text-white'
          : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'">
        {{ f.label }}
      </button>
    </div>

    <!-- Empty state -->
    <div v-if="shortCourseApplications.data.length === 0"
      class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
      <DocumentIcon class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" />
      <h3 class="text-gray-900 dark:text-gray-100 font-semibold mb-1">No applications found</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ filter === 'pending' ? 'No pending applications at the moment.' : 'No applications match this filter.' }}
      </p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full whitespace-nowrap">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Applicant</th>
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Short Course</th>
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Applied</th>
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="app in shortCourseApplications.data" :key="app.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <td class="px-6 py-4">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ app.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ app.email }}</p>
                <p v-if="app.phone" class="text-xs text-gray-500 dark:text-gray-400">{{ app.phone }}</p>
              </div>
            </td>
            <td class="px-6 py-4">
              <div v-if="app.short_course">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ app.short_course.title }}</p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
                    {{ app.short_course.type === 'workshop' ? 'Workshop' : app.short_course.type === 'training' ? 'Training' : 'Short Course' }}
                  </span>
                  <span class="text-xs text-gray-400 dark:text-gray-500">{{ app.short_course.duration }}</span>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
              {{ formatDate(app.applied_at) }}
            </td>
            <td class="px-6 py-4">
              <Badge :color="statusColors[app.status] || 'gray'">
                {{ statusLabels[app.status] ?? app.status }}
              </Badge>
            </td>
            <td class="px-6 py-4">
              <div v-if="app.notes" class="text-xs text-gray-500 dark:text-gray-400 mb-2 max-w-xs truncate">{{ app.notes }}</div>
              <div v-if="isAdmin && app.status === 'pending'" class="flex items-center gap-2">
                <button @click="review(app, 'approved')"
                  class="px-3 py-1 text-xs font-medium rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition">
                  Approve
                </button>
                <button @click="review(app, 'rejected')"
                  class="px-3 py-1 text-xs font-medium rounded-lg bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 transition">
                  Reject
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination
        v-if="shortCourseApplications.data.length > 0"
        :links="shortCourseApplications.links"
        :meta="shortCourseApplications.meta"
      />
    </div>
  </HiveLayout>
</template>