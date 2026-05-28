<template>
  <HiveLayout title="Announcements" description="Institute notices, reminders, and pinned updates.">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">Announcements</h1>
      <Link v-if="canCreate" :href="route('hive.announcements.create')"
            class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">New Post</Link>
    </div>
    <div class="space-y-4">
      <div v-for="ann in announcements" :key="ann.id" class="bg-white p-4 shadow rounded">
        <div class="flex justify-between">
          <h2 class="font-semibold text-lg">
            <span v-if="ann.is_pinned" class="text-red-500 mr-1">📌</span>{{ ann.title }}
          </h2>
          <span class="text-xs text-gray-500">{{ new Date(ann.created_at).toLocaleDateString() }}</span>
        </div>
        <p class="text-sm text-gray-700 mt-1">{{ ann.body.substring(0, 200) }}...</p>
        <div class="mt-2 text-xs text-gray-500">
          <span class="bg-gray-100 px-2 py-0.5 rounded">{{ ann.category }}</span>
          <span v-if="ann.expires_at">Expires: {{ new Date(ann.expires_at).toLocaleDateString() }}</span>
        </div>
        <div v-if="canEdit(ann)" class="mt-2 space-x-2">
          <Link :href="route('hive.announcements.edit', ann.id)" class="text-amber-600 text-sm hover:text-amber-700">Edit</Link>
          <button @click="deleteAnn(ann.id)" class="text-red-600 text-sm">Delete</button>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>
<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({ announcements: Array });
const roles = computed(() => usePage().props.auth.user?.roles || []);
const canCreate = computed(() => roles.value.some(r => ['super-admin','school-admin', 'academic_staff', 'non_academic_staff'].includes(r)));
const canEdit = (ann) => {
  const user = usePage().props.auth.user;
  return roles.value.some(r => ['super-admin', 'school-admin'].includes(r)) || user?.id === ann.created_by;
};
const deleteAnn = (id) => {
  if (confirm('Delete this announcement?')) {
    router.delete(route('hive.announcements.destroy', id));
  }
};
</script>
