<template>
  <HiveLayout title="Edit Event">
    <div class="max-w-2xl mx-auto">
      <div class="mb-6">
        <Link :href="route('hive.events.index')" class="text-gray-500 hover:text-gray-700">&larr; Back to Events</Link>
      </div>

      <div class="bg-white shadow rounded p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Edit Event</h1>

        <form @submit.prevent="submit">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
              <input v-model="form.title" type="text" required
                     class="w-full rounded border-gray-300 border px-3 py-2 focus:ring-amber-500 focus:border-amber-500" />
              <p v-if="errors.title" class="text-red-500 text-sm mt-1">{{ errors.title }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea v-model="form.description" rows="4"
                        class="w-full rounded border-gray-300 border px-3 py-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
              <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
              <input v-model="form.location" type="text"
                     class="w-full rounded border-gray-300 border px-3 py-2 focus:ring-amber-500 focus:border-amber-500"
                     placeholder="e.g. Kitchen Lab 1, Main Hall" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <select v-model="form.category" class="w-full rounded border-gray-300 border px-3 py-2">
                <option value="event">Event</option>
                <option value="academic">Academic</option>
                <option value="module">Module</option>
                <option value="student">Student</option>
                <option value="staff">Staff</option>
                <option value="administrative">Administrative</option>
                <option value="hr">HR</option>
                <option value="financial">Financial</option>
                <option value="health_safety">Health & Safety</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Target Modules (optional)</label>
              <div class="border rounded p-2 max-h-40 overflow-y-auto">
                <label v-for="mod in modules" :key="mod.id" class="flex items-center gap-2 py-1">
                  <input type="checkbox" :value="mod.id" v-model="form.target_modules" />
                  <span>{{ mod.code }} - {{ mod.name }}</span>
                </label>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date & Time</label>
                <input v-model="form.start" type="datetime-local" required
                       class="w-full rounded border-gray-300 border px-3 py-2 focus:ring-amber-500 focus:border-amber-500" />
                <p v-if="errors.start" class="text-red-500 text-sm mt-1">{{ errors.start }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date & Time <span class="text-gray-400">(optional)</span></label>
                <input v-model="form.end" type="datetime-local"
                       class="w-full rounded border-gray-300 border px-3 py-2 focus:ring-amber-500 focus:border-amber-500" />
                <p v-if="errors.end" class="text-red-500 text-sm mt-1">{{ errors.end }}</p>
              </div>
            </div>
          </div>

          <!-- Attendee list for admins -->
          <div v-if="event.rsvps && event.rsvps.length" class="mt-6 p-4 bg-gray-50 rounded border">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Attendees ({{ event.rsvps.length }})</h3>
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
            <button type="submit" :disabled="processing"
                    class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
              {{ processing ? 'Updating...' : 'Update Event' }}
            </button>
            <a :href="route('hive.events.ical', event.id)" target="_blank"
               class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded">
              📅 Export .ics
            </a>
            <Link :href="route('hive.events.index')" class="px-4 py-2 text-gray-600 hover:text-gray-800">
              Cancel
            </Link>
          </div>
        </form>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
  event: Object,
  modules: Array,
  errors: Object,
});

const form = useForm({
  title: props.event.title,
  description: props.event.description,
  location: props.event.location || '',
  start: props.event.start,
  end: props.event.end || '',
  category: props.event.category || 'event',
  target_modules: props.event.target_modules || [],
});

const submit = () => {
  form.put(route('hive.events.update', props.event.id));
};
</script>