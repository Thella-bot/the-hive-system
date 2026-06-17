<template>
  <HiveLayout title="Events" description="Calendar and upcoming events">
    <template #header-actions>
      <Link v-if="canCreate" :href="route('hive.events.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        New Event
      </Link>
    </template>

    <div v-if="events.data && events.data.length === 0" class="bg-white dark:bg-gray-800 p-8 text-center rounded-xl border border-gray-200 dark:border-gray-700">
      <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
        <CalendarIcon class="w-8 h-8 text-gray-400" />
      </div>
      <p class="text-gray-500 dark:text-gray-400">No events found. Create your first event!</p>
    </div>

    <div class="space-y-4">
      <div v-for="event in events.data" :key="event.id"
           class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition">
        <div class="flex justify-between items-start gap-4">
          <div class="flex-1">
            <h2 class="font-semibold text-lg text-gray-900 dark:text-white">{{ event.title }}</h2>
            <p v-if="event.description" class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ event.description }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
              <span class="flex items-center gap-1">
                <CalendarIcon class="w-4 h-4" />
                {{ formatDate(event.start) }}<span v-if="event.end"> - {{ formatDate(event.end) }}</span>
              </span>
              <span v-if="event.location" class="flex items-center gap-1">
                <MapPinIcon class="w-4 h-4" />
                {{ event.location }}
              </span>
              <span class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ formatCategory(event.category) }}</span>
              <a :href="route('hive.events.ical', { event: event.id })" class="text-amber-600 hover:text-amber-700 dark:text-amber-400 underline text-xs" target="_blank">📅 Add to Calendar</a>
            </div>
            <!-- RSVP summary -->
            <div v-if="event.rsvps" class="mt-2 flex items-center gap-3 text-xs">
              <span class="text-green-600 dark:text-green-400">✓ {{ event.rsvps.filter(r => r.status === 'attending').length }} attending</span>
              <span v-if="event.rsvps.filter(r => r.status === 'maybe').length" class="text-yellow-600 dark:text-yellow-400">? {{ event.rsvps.filter(r => r.status === 'maybe').length }} maybe</span>
              <span v-if="event.rsvps.filter(r => r.status === 'declined').length" class="text-red-600 dark:text-red-400">✗ {{ event.rsvps.filter(r => r.status === 'declined').length }} declined</span>
            </div>
          </div>
          <!-- RSVP buttons -->
          <div class="flex flex-col items-end gap-2">
            <div class="flex gap-1">
              <button v-for="status in ['attending', 'maybe', 'declined']" :key="status"
                @click="rsvp(event.id, status)"
                :class="['px-2 py-1 text-xs rounded border', getRsvpClass(event, status)]">
                {{ status === 'attending' ? '✓ Attend' : status === 'maybe' ? '? Maybe' : '✗ Decline' }}
              </button>
            </div>
            <div v-if="canCreate || canDelete" class="flex gap-3 mt-1">
              <Link :href="route('hive.events.edit', { event: event.id })"
                    v-if="canCreate"
                    class="text-amber-600 hover:text-amber-700 dark:text-amber-400 text-sm">Edit</Link>
              <button v-if="canDelete" @click="deleteEvent(event.id)" class="text-red-600 hover:text-red-700 dark:text-red-400 text-sm">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="events.links" class="mt-6 flex justify-center gap-2">
      <template v-for="link in events.links" :key="link.label">
        <Link v-if="link.url"
              :href="link.url"
              :class="['px-3 py-1 rounded', link.active ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300']"
              v-html="link.label" />
        <span v-else
              :class="['px-3 py-1 rounded', link.active ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300']"
              v-html="link.label" />
      </template>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { CalendarIcon, MapPinIcon, PlusIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';
import { useUser } from '@/composables/useUser';

defineProps({ events: Object });

const { isAdmin, isAcademicStaff, isNonAcademicStaff } = useUser();
const canCreate = computed(() => isAdmin.value || isAcademicStaff.value || isNonAcademicStaff.value);
const canDelete = computed(() => isAdmin.value);

const formatDate = (date) => date ? dayjs(date).format('MMM D, YYYY h:mm A') : '—';
const formatCategory = (c) => c?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) ?? '';

const rsvp = (eventId, status) => {
  router.post(route('hive.events.rsvp', { event: eventId }), { status });
};

const getRsvpClass = (event, status) => {
  if (event.user_rsvp?.status === status) {
    return status === 'attending' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 border-green-300 dark:border-green-700' :
           status === 'maybe' ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300 border-yellow-300 dark:border-yellow-700' :
           'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border-red-300 dark:border-red-700';
  }
  return 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700';
};

const deleteEvent = (id) => {
  if (confirm('Delete this event?')) {
    router.delete(route('hive.events.destroy', { event: id }));
  }
};
</script>