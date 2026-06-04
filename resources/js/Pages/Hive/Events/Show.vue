<template>
  <HiveLayout title="Event">
    <div class="max-w-2xl mx-auto">
      <div class="mb-6">
        <Link :href="route('hive.events.index')" class="text-gray-500 hover:text-gray-700">&larr; Back to Events</Link>
      </div>

      <div class="bg-white shadow rounded p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ event.title }}</h1>
            <p v-if="event.category" class="mt-1 text-sm text-gray-500 capitalize">{{ event.category }}</p>
          </div>
          <span v-if="event.is_pinned" class="px-2 py-1 text-xs bg-amber-100 text-amber-800 rounded">📌 Pinned</span>
        </div>

        <dl class="space-y-3 text-sm">
          <div v-if="event.location" class="flex gap-3">
            <dt class="w-32 font-medium text-gray-500">Location</dt>
            <dd class="text-gray-900">{{ event.location }}</dd>
          </div>
          <div class="flex gap-3">
            <dt class="w-32 font-medium text-gray-500">Start</dt>
            <dd class="text-gray-900">{{ formatDate(event.start) }}</dd>
          </div>
          <div v-if="event.end" class="flex gap-3">
            <dt class="w-32 font-medium text-gray-500">End</dt>
            <dd class="text-gray-900">{{ formatDate(event.end) }}</dd>
          </div>
          <div v-if="event.target_modules && event.target_modules.length" class="flex gap-3">
            <dt class="w-32 font-medium text-gray-500">Modules</dt>
            <dd class="flex flex-wrap gap-1">
              <span v-for="mod in event.target_modules" :key="mod.id"
                    class="px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded">
                {{ mod.code }} - {{ mod.name }}
              </span>
            </dd>
          </div>
        </dl>

        <div v-if="event.description" class="mt-6">
          <h3 class="text-sm font-semibold text-gray-700 mb-2">Description</h3>
          <p class="text-gray-700 whitespace-pre-line">{{ event.description }}</p>
        </div>

        <!-- RSVP form for guests and students -->
        <div v-if="canRsvp" class="mt-8 border-t pt-6">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Your RSVP</h3>
          <div class="flex gap-3">
            <button v-for="status in ['attending', 'maybe', 'declined']" :key="status"
                    @click="submitRsvp(status)"
                    :class="[
                      'px-4 py-2 rounded border text-sm font-medium transition',
                      userRsvp?.status === status
                        ? status === 'attending' ? 'bg-green-600 text-white border-green-600'
                        : status === 'maybe' ? 'bg-yellow-500 text-white border-yellow-500'
                        : 'bg-red-600 text-white border-red-600'
                        : 'border-gray-300 text-gray-700 hover:bg-gray-50'
                    ]">
              {{ status === 'attending' ? '✅ Attending' : status === 'maybe' ? '🤔 Maybe' : '❌ Declined' }}
            </button>
          </div>
          <p v-if="rsvpSuccess" class="mt-2 text-sm text-green-600">RSVP updated.</p>
        </div>

        <!-- Attendee list for admins -->
        <div v-if="event.rsvps && event.rsvps.length" class="mt-8 border-t pt-6">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Attendees ({{ event.rsvps.length }})</h3>
          <div class="flex flex-wrap gap-2">
            <span v-for="rsvp in event.rsvps" :key="rsvp.id"
                  :class="['px-2 py-0.5 text-xs rounded-full',
                    rsvp.status === 'attending' ? 'bg-green-100 text-green-800' :
                    rsvp.status === 'maybe' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-red-100 text-red-800']">
              {{ rsvp.user?.name }} ({{ rsvp.status }})
            </span>
          </div>
        </div>

        <div class="mt-6 flex gap-3">
          <a :href="route('hive.events.ical', { event: event.id })" target="_blank"
             class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded">
            📅 Export .ics
          </a>
          <a :href="route('hive.events.qr', { event: event.id })" target="_blank"
             class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded">
            📱 QR Code
          </a>
          <Link v-if="canEdit" :href="route('hive.events.edit', { event: event.id })"
                class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded">
            Edit Event
          </Link>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
  event: Object,
  modules: Array,
  userRsvp: Object,
});

const rsvpSuccess = ref(false);
const canEdit = false;
const canRsvp = true;

const formatDate = (iso) => {
  if (!iso) return '';
  return new Date(iso).toLocaleString('en-US', {
    dateStyle: 'full', timeStyle: 'short',
  });
};

const submitRsvp = (status) => {
  router.post(route('hive.events.rsvp', { event: props.event.id }), { status }, {
    onSuccess: () => { rsvpSuccess.value = true; setTimeout(() => rsvpSuccess.value = false, 3000); },
  });
};
</script>
