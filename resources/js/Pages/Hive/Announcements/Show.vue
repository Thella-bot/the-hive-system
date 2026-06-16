<template>
  <HiveLayout title="Announcement" description="View announcement details.">
    <div class="max-w-3xl mx-auto">
      <div class="mb-4">
        <Link :href="route('hive.announcements.index')"
          class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
          title="Announcements">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
        </Link>
      </div>

      <div class="bg-white p-6 shadow rounded">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h1 class="text-2xl font-bold">
              <span v-if="announcement.is_pinned" class="text-red-500 mr-2">📌</span>{{ announcement.title }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
              Posted {{ formatDate(announcement.created_at) }}
              <span v-if="announcement.expires_at"> · Expires {{ formatDate(announcement.expires_at) }}</span>
            </p>
          </div>
          <span class="bg-gray-100 px-3 py-1 rounded text-sm">{{ formatCategory(announcement.category) }}</span>
        </div>

        <!-- Priority badge -->
        <div v-if="announcement.priority && announcement.priority !== 'normal'" class="mb-4">
          <span
            :class="{
              'bg-red-100 text-red-800': announcement.priority === 'emergency',
              'bg-amber-100 text-amber-800': announcement.priority === 'urgent',
            }"
            class="px-3 py-1 rounded text-sm font-medium"
          >
            {{ announcement.priority.toUpperCase() }}
          </span>
        </div>

        <!-- Target modules -->
        <div v-if="announcement.target_modules && announcement.target_modules.length" class="mb-4 flex flex-wrap gap-2">
          <span class="text-xs text-gray-500 mr-1">Targeted to:</span>
          <span
            v-for="mod in announcement.target_modules"
            :key="mod.id"
            class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded text-xs border border-amber-200"
          >
            {{ mod.name }}
          </span>
        </div>

        <!-- Body -->
        <div class="mt-4">
          <div v-if="announcement.body_html" class="text-sm text-gray-700 prose max-w-none" v-html="announcement.body_html"></div>
          <p v-else class="text-sm text-gray-700 whitespace-pre-wrap">{{ announcement.body }}</p>
        </div>

        <!-- Attachments -->
        <div v-if="announcement.attachments && announcement.attachments.length" class="mt-6">
          <h3 class="font-semibold text-sm text-gray-700 mb-2">Attachments</h3>
          <div class="flex flex-wrap gap-2">
            <a
              v-for="att in announcement.attachments"
              :key="att.id"
              :href="route('hive.announcements.attachments.download', { announcement: announcement.id, attachment: att.id })"
              class="inline-flex items-center gap-2 text-sm text-amber-600 hover:text-amber-800 bg-amber-50 px-3 py-2 rounded border border-amber-200"
              target="_blank"
            >
              <ArrowDownTrayIcon class="w-4 h-4" />
              {{ att.name }}
            </a>
          </div>
        </div>

        <!-- Edit/Delete actions -->
        <div v-if="canEdit(announcement)" class="mt-6 pt-4 border-t flex gap-4">
          <Link :href="route('hive.announcements.edit', { announcement: announcement.id })" class="text-amber-600 hover:text-amber-700">Edit</Link>
          <button @click="deleteAnn(announcement.id)" class="text-red-600 hover:text-red-700">Delete</button>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';
import { useUser } from '@/composables/useUser';

const props = defineProps({ announcement: Object });
const { currentUser, isAdmin } = useUser();
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