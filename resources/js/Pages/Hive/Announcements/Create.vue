<template>
  <HiveLayout>
    <h1 class="text-2xl font-bold mb-4">New Announcement</h1>
    <form @submit.prevent="form.post(route('hive.announcements.store'))" class="max-w-lg space-y-4">
      <div>
        <label class="block text-sm">Title</label>
        <input v-model="form.title" class="w-full border rounded p-2" required />
      </div>
      <div>
        <label class="block text-sm">Body</label>
        <textarea v-model="form.body" rows="5" class="w-full border rounded p-2" required></textarea>
      </div>
      <div>
        <label class="block text-sm">Category</label>
        <select v-model="form.category" class="w-full border rounded p-2">
          <option value="general">General</option>
          <option value="academic">Academic</option>
          <option value="event">Event</option>
          <option value="module">Module</option>
          <option value="staff">Staff</option>
          <option value="hr">HR</option>
          <option value="administrative">Administrative</option>
          <option value="financial">Financial</option>
          <option value="health_safety">Health & Safety</option>
        </select>
      </div>
      <div>
        <label class="block text-sm">Target Roles (leave empty for all)</label>
        <div class="flex gap-2">
          <label v-for="role in ['student','academic_staff','non_academic_staff','school-admin','super-admin']" :key="role">
            <input type="checkbox" :value="role" v-model="form.target_roles" /> {{ role }}
          </label>
        </div>
      </div>
      <div>
        <label class="block text-sm">Target Modules (optional)</label>
        <div class="border rounded p-2 max-h-40 overflow-y-auto">
          <label v-for="mod in modules" :key="mod.id" class="flex items-center gap-2 py-1">
            <input type="checkbox" :value="mod.id" v-model="form.target_modules" />
            <span>{{ mod.code }} - {{ mod.name }}</span>
          </label>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <input type="checkbox" v-model="form.is_pinned" /> <label>Pin this announcement</label>
      </div>
      <div>
        <label class="block text-sm">Expires at (optional)</label>
        <input type="datetime-local" v-model="form.expires_at" class="w-full border rounded p-2" />
      </div>
      <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Publish</button>
    </form>
  </HiveLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
  modules: Array,
});

const form = useForm({
  title: '',
  body: '',
  category: 'general',
  target_roles: [],
  target_modules: [],
  is_pinned: false,
  expires_at: '',
});
</script>