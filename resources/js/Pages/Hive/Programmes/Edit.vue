<template>
  <HiveLayout :title="`Edit ${programme.name}`" description="Update programme details and manage modules.">
    <div class="max-w-2xl mx-auto">
      <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Programme Name</label>
          <input id="name" v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" />
          <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
        </div>

        <div>
          <label for="duration" class="block text-sm font-medium text-gray-700">Duration</label>
          <input id="duration" v-model="form.duration" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" />
          <div v-if="form.errors.duration" class="mt-1 text-sm text-red-600">{{ form.errors.duration }}</div>
        </div>

        <div>
          <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
          <textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
          <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
        </div>

        <div>
          <label for="delivery_mode" class="block text-sm font-medium text-gray-700">Delivery Mode</label>
          <select id="delivery_mode" v-model="form.delivery_mode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <option value="in_person">In Person</option>
            <option value="online">Online</option>
            <option value="hybrid">Hybrid</option>
          </select>
          <div v-if="form.errors.delivery_mode" class="mt-1 text-sm text-red-600">{{ form.errors.delivery_mode }}</div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label for="meeting_platform" class="block text-sm font-medium text-gray-700">Meeting Platform</label>
            <input id="meeting_platform" v-model="form.meeting_platform" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Zoom, Google Meet, Teams" />
            <div v-if="form.errors.meeting_platform" class="mt-1 text-sm text-red-600">{{ form.errors.meeting_platform }}</div>
          </div>
          <div>
            <label for="meeting_link" class="block text-sm font-medium text-gray-700">Meeting Link</label>
            <input id="meeting_link" v-model="form.meeting_link" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" placeholder="https://..." />
            <div v-if="form.errors.meeting_link" class="mt-1 text-sm text-red-600">{{ form.errors.meeting_link }}</div>
          </div>
        </div>

        <div>
          <label for="location" class="block text-sm font-medium text-gray-700">Venue / Location</label>
          <input id="location" v-model="form.location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Physical venue or classroom name" />
          <div v-if="form.errors.location" class="mt-1 text-sm text-red-600">{{ form.errors.location }}</div>
        </div>

        <div>
          <label for="modules" class="block text-sm font-medium text-gray-700 mb-1">Modules</label>
          <select id="modules" v-model="form.modules" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 h-48">
            <option v-for="module in allModules" :key="module.id" :value="module.id">
              {{ module.name }} ({{ module.code }})
            </option>
          </select>
          <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple modules.</p>
          <div v-if="form.errors.modules" class="mt-1 text-sm text-red-600">{{ form.errors.modules }}</div>
        </div>

        <div class="flex justify-end">
          <button type="submit" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-50" :disabled="form.processing">
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </HiveLayout>
</template>

<script setup>
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  programme: Object,
  allModules: Array,
});

const form = useForm({
  name: props.programme.name,
  description: props.programme.description,
  duration: props.programme.duration,
  delivery_mode: props.programme.delivery_mode ?? 'in_person',
  meeting_platform: props.programme.meeting_platform ?? '',
  meeting_link: props.programme.meeting_link ?? '',
  location: props.programme.location ?? '',
  modules: props.programme.modules.map(m => m.id),
});

const submit = () => {
  form.put(route('hive.programmes.update', { programme: props.programme.id }));
};
</script>