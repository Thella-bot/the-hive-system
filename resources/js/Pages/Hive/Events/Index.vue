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
          <div class="flex-1">
            <h2 class="font-semibold text-lg text-gray-800">{{ event.title }}</h2>
            <p v-if="event.description" class="text-sm text-gray-600 mt-1">{{ event.description }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-500">
              <span class="flex items-center gap-1">
                <CalendarIcon class="w-4 h-4" />
                {{ formatDate(event.start) }}<span v-if="event.end"> - {{ formatDate(event.end) }}</span>
              </span>
              <span v-if="event.location" class="flex items-center gap-1">
                <MapPinIcon class="w-4 h-4" />
                {{ event.location }}
              </span>
              <span class="bg-gray-100 px-2 py-0.5 rounded">{{ formatCategory(event.category) }}</span>
              <a :href="route('hive.events.ical', { event: event.id })" class="text-amber-600 hover:text-amber-800 underline text-xs" target="_blank">📅 Add to Calendar</a>
            </div>
            <!-- RSVP summary -->
            <div v-if="event.rsvps" class="mt-2 flex items-center gap-3 text-xs text-gray-500">
              <span class="text-green-600">✓ {{ event.rsvps.filter(r => r.status === 'attending').length }} attending</span>
              <span v-if="event.rsvps.filter(r => r.status === 'maybe').length" class="text-yellow-600">? {{ event.rsvps.filter(r => r.status === 'maybe').length }} maybe</span>
              <span v-if="event.rsvps.filter(r => r.status === 'declined').length" class="text-red-600">✗ {{ event.rsvps.filter(r => r.status === 'declined').length }} declined</span>
            </div>
          </div>
          <!-- RSVP buttons -->
          <div class="flex flex-col items-end gap-1">
            <div class="flex gap-1">
              <button v-for="status in ['attending', 'maybe', 'declined']" :key="status"
                @click="rsvp(event.id, status)"
                :class="['px-2 py-1 text-xs rounded border', getRsvpClass(event, status)]">
                {{ status === 'attending' ? '✓ Attend' : status === 'maybe' ? '? Maybe' : '✗ Decline' }}
              </button>
            </div>
            <div v-if="canCreate || canDelete" class="flex gap-2 mt-1">
              <Link :href="route('hive.events.edit', { event: event.id })"
                    v-if="canCreate"
                    class="text-amber-600 hover:text-amber-700 text-sm">Edit</Link>
              <button v-if="canDelete" @click="deleteEvent(event.id)" class="text-red-600 hover:text-red-700 text-sm">Delete</button>
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
import { Link, router } from '@inertiajs/vue3';
import { CalendarIcon, MapPinIcon } from '@heroicons/vue/24/outline';
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
    return status === 'attending' ? 'bg-green-100 text-green-800 border-green-300' :
           status === 'maybe' ? 'bg-yellow-100 text-yellow-800 border-yellow-300' :
           'bg-red-100 text-red-800 border-red-300';
  }
  return 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50';
};

const deleteEvent = (id) => {
  if (confirm('Delete this event?')) {
    router.delete(route('hive.events.destroy', { event: id }));
  }
};
</script>