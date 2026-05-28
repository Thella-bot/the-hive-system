<template>
  <HiveLayout title="Events">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold text-gray-800">Calendar Events</h1>
      <Link v-if="canCreate"
            :href="route('hive.events.create')"
            class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
        New Event
      </Link>
    </div>

    <div v-if="events.data && events.data.length === 0" class="bg-white p-8 text-center rounded shadow">
      <p class="text-gray-500">No events found. Create your first event!</p>
    </div>

    <div class="space-y-4">
      <div v-for="event in events.data" :key="event.id"
           class="bg-white p-4 shadow rounded hover:shadow-md transition">
        <div class="flex justify-between items-start">
          <div>
            <h2 class="font-semibold text-lg text-gray-800">{{ event.title }}</h2>
            <p class="text-sm text-gray-600 mt-1">{{ event.description }}</p>
            <div class="mt-2 flex items-center gap-4 text-sm text-gray-500">
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ formatDate(event.start) }} - {{ formatDate(event.end) }}
              </span>
              <span class="bg-gray-100 px-2 py-0.5 rounded">{{ event.category }}</span>
            </div>
          </div>
          <div v-if="canCreate || canDelete" class="flex gap-2">
            <Link :href="route('hive.events.edit', event.id)"
                  v-if="canCreate"
                  class="text-amber-600 hover:text-amber-700 text-sm">Edit</Link>
            <button v-if="canDelete" @click="deleteEvent(event.id)" class="text-red-600 hover:text-red-700 text-sm">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="events.links" class="mt-6 flex justify-center gap-2">
      <template v-for="link in events.links" :key="link.label">
        <Link v-if="link.url"
              :href="link.url"
              :class="['px-3 py-1 rounded', link.active ? 'bg-amber-600 text-white' : 'bg-white text-gray-700']"
              v-html="link.label" />
        <span v-else
              :class="['px-3 py-1 rounded', link.active ? 'bg-amber-600 text-white' : 'bg-white text-gray-700']"
              v-html="link.label" />
      </template>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
  events: Object,
});

const roles = computed(() => usePage().props.auth.user?.roles || []);
const canCreate = computed(() => roles.value.some(role => ['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff'].includes(role)));
const canDelete = computed(() => roles.value.some(role => ['super-admin', 'school-admin'].includes(role)));

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const deleteEvent = (id) => {
  if (confirm('Delete this event?')) {
    router.delete(route('hive.events.destroy', id));
  }
};
</script>
