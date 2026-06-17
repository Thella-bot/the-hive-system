<template>
  <HiveLayout title="Announcements" description="Institute notices, reminders, and pinned updates.">
    <template #header-actions>
      <Link v-if="canCreate" :href="route('hive.announcements.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        New Post
      </Link>
    </template>
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
            <ArrowDownTrayIcon class="w-3 h-3" />
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
import { Link, router } from '@inertiajs/vue3';
import { ArrowDownTrayIcon, PlusIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';
import { useUser } from '@/composables/useUser';

const props = defineProps({ announcements: [Array, Object] });
const { userRoles, currentUser, isStaff, isAdmin } = useUser();
const announcementsList = computed(() => {
  if (!props.announcements) return [];
  if (Array.isArray(props.announcements)) return props.announcements;
  return props.announcements.data || [];
});
const canCreate = computed(() => isStaff.value || isAdmin.value);
const canEdit = (ann) => {
  return isAdmin.value || currentUser.value?.id === ann.created_by;
};
const deleteAnn = (id) => {
  if (confirm('Delete this announcement?')) {
    router.delete(route('hive.announcements.destroy', { announcement: id }));
  }
};
const formatDate = (d) => d ? dayjs(d).format('MMM D, YYYY') : '—';
const formatCategory = (c) => c?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) ?? '';
</script>