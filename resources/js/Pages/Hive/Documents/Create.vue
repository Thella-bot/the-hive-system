<template>
  <HiveLayout title="Upload Document">
    <h1 class="text-2xl font-bold mb-4">Upload Document</h1>
    <form @submit.prevent="form.post(route('hive.documents.store'))" class="max-w-lg space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
        <input v-model="form.title" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500" required />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea v-model="form.description" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Module (Optional)</label>
        <select v-model="form.module_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500">
          <option value="">No specific module</option>
          <option v-for="mod in modules" :key="mod.id" :value="mod.id">{{ mod.name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <select v-model="form.category" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500" required>
          <option value="" disabled>Select a category</option>
          <option value="Book">Book</option>
          <option value="Notes">Notes</option>
          <option value="Schedule">Schedule</option>
          <option value="Recipes">Recipes</option>
          <option value="Memos">Memos</option>
          <option value="Others">Others</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Audience</label>
        <select v-model="form.audience" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500" required>
          <option value="module_students">Module Students (enrolled in the module)</option>
          <option value="student_only">All Students</option>
          <option value="staff_only">Staff Only</option>
          <option value="all_users">All Users (authenticated)</option>
          <option value="everyone">Everyone (Public)</option>
        </select>
        <p class="text-xs text-gray-500 mt-1">Who should be able to view this document</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Visible to Roles (leave empty for all)</label>
        <div class="flex flex-wrap gap-4">
          <label v-for="role in ['student','parent-guardian','alumni','super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef', 'admissions-officer', 'examination-cell', 'registrar', 'finance', 'procurement-manager', 'storekeeper', 'hr-manager', 'librarian', 'career-services', 'events-pr-manager', 'cafeteria-manager']" :key="role" class="inline-flex items-center">
            <input type="checkbox" :value="role" v-model="form.visible_to_roles" class="mr-1" /> {{ role }}
          </label>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">File (max 20 MB)</label>
        <input type="file" @input="form.file = $event.target.files[0]" class="w-full border border-gray-300 rounded-md p-2" required />
      </div>
      <div class="flex gap-4">
        <Link :href="route('hive.documents.index')" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-400 focus:ring ring-gray-300 disabled:opacity-25 transition">
          Cancel
        </Link>
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 active:bg-amber-700 focus:outline-none focus:border-amber-700 focus:ring ring-amber-300 disabled:opacity-25 transition">
          Upload
        </button>
      </div>
    </form>
  </HiveLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
  modules: {
    type: Array,
    default: () => [],
  },
});

const form = useForm({
  title: '',
  description: '',
  module_id: '',
  category: '',
  audience: 'module_students',
  visible_to_roles: [],
  file: null,
});
</script>