<template>
  <IntranetLayout>
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">Announcements</h1>
      <Link v-if="canCreate" :href="route('intranet.announcements.create')"
            class="bg-indigo-600 text-white px-4 py-2 rounded">New Post</Link>
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
          <Link :href="route('intranet.announcements.edit', ann.id)" class="text-indigo-600 text-sm">Edit</Link>
          <button @click="deleteAnn(ann.id)" class="text-red-600 text-sm">Delete</button>
        </div>
      </div>
    </div>
  </IntranetLayout>
</template>
<script setup>
import { computed } from 'vue';
import { Link, usePage, Inertia } from '@inertiajs/vue3'; // Inertia for manual delete
import IntranetLayout from '@/Layouts/IntranetLayout.vue';

const props = defineProps({ announcements: Array });
const canCreate = computed(() => usePage().props.user.roles.some(r => ['admin','instructor','hr_staff'].includes(r.name)));
const canEdit = (ann) => {
  const user = usePage().props.user;
  return user.roles.some(r => r.name === 'admin') || user.id === ann.created_by;
};
const deleteAnn = (id) => {
  if (confirm('Delete this announcement?')) {
    Inertia.delete(route('intranet.announcements.destroy', id));
  }
};
</script>