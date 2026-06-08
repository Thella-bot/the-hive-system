<template>
  <HiveLayout title="Announcements" description="Institute notices, reminders, and pinned updates.">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Announcements</h1>
      <Link v-if="canCreate" :href="route('hive.announcements.create')"
            class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition">New Post</Link>
    </div>
    <div class="space-y-4">
      <div v-for="ann in announcementsList" :key="ann.id" class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start gap-4">
          <h2 class="font-semibold text-lg text-gray-900 dark:text-gray-100">
            <Link :href="route('hive.announcements.show', { announcement: ann.id })">
            <span v-if="ann.is_pinned" class="text-red-500 mr-1">📌</span>{{ ann.title }}
            </Link>
          </h2>
          <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ formatDate(ann.created_at) }}</span>
        </div>

        <!-- Announcement body: render HTML if available, otherwise plain text -->
        <div v-if="ann.body_html" class="mt-3 text-sm text-gray-700 dark:text-gray-300 prose max-w-none" v-html="ann.body_html"></div>
        <p v-else class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ (ann.body || '').substring(0, 200) }}{{ (ann.body || '').length > 200 ? '...' : '' }}</p>

        <!-- Attachments -->
        <div v-if="ann.attachments && ann.attachments.length" class="mt-3 flex flex-wrap gap-2">
          <a
            v-for="att in ann.attachments"
            :key="att.id"
            :href="route('hive.announcements.attachments.download', { announcement: ann.id, attachment: att.id })"
            class="inline-flex items-center gap-1 text-xs text-amber-600 hover:text-amber-800 bg-amber-50 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-1 rounded border border-amber-200 dark:border-amber-800"
            target="_blank"
          >
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            {{ att.name }}
          </a>
        </div>

        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 flex flex-wrap gap-2 items-center">
          <span class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ formatCategory(ann.category) }}</span>
          <span v-if="ann.expires_at">Expires: {{ formatDate(ann.expires_at) }}</span>
        </div>
        <div v-if="canEdit(ann)" class="mt-3 flex flex-wrap gap-3">
          <Link :href="route('hive.announcements.edit', { announcement: ann.id })" class="text-amber-600 text-sm hover:text-amber-700 dark:text-amber-400">Edit</Link>
          <button @click="deleteAnn(ann.id)" class="text-red-600 text-sm hover:text-red-900 dark:text-red-400">Delete</button>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({ announcements: [Array, Object] });
const announcementsList = computed(() => {
  if (!props.announcements) return [];
  if (Array.isArray(props.announcements)) return props.announcements;
  return props.announcements.data || [];
});
const roles = computed(() => usePage().props.auth.user?.roles || []);
const canCreate = computed(() => roles.value.some(r => ['super-admin','school-admin', 'academic_staff', 'non_academic_staff'].includes(r)));
const canEdit = (ann) => {
  const user = usePage().props.auth.user;
  return roles.value.some(r => ['super-admin', 'school-admin'].includes(r)) || user?.id === ann.created_by;
};
const deleteAnn = (id) => {
  if (confirm('Delete this announcement?')) {
    router.delete(route('hive.announcements.destroy', { announcement: id }));
  }
};
const formatDate = (d) => d ? dayjs(d).format('MMM D, YYYY') : '—';
const formatCategory = (c) => c?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) ?? '';
</script>