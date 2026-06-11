<template>
  <HiveLayout title="Edit Document">
    <div class="max-w-2xl mx-auto">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Title *</label>
            <input v-model="form.title" required
              class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Description</label>
            <textarea v-model="form.description" rows="3"
              class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Module *</label>
            <select v-model="form.module_id" required
              class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
              <option value="" disabled>Select module</option>
              <option v-for="mod in modules" :key="mod.id" :value="mod.id">{{ mod.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Category *</label>
            <select v-model="form.category" required
              class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
              <option value="" disabled>Select category</option>
              <option value="Book">Book</option>
              <option value="Notes">Notes</option>
              <option value="Schedule">Schedule</option>
              <option value="Recipes">Recipes</option>
              <option value="Memos">Memos</option>
              <option value="Others">Others</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Audience *</label>
            <select v-model="form.audience" required
              class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
              <option value="module_students">Module Students (enrolled in the module)</option>
              <option value="student_only">All Students</option>
              <option value="staff_only">Staff Only</option>
              <option value="all_users">All Users (authenticated)</option>
              <option value="everyone">Everyone (Public)</option>
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Who should be able to view this document</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Visible to Roles</label>
            <div class="flex flex-wrap gap-4">
              <label v-for="role in ['student','academic_staff','non_academic_staff','school-admin', 'super-admin']" :key="role" class="inline-flex items-center">
                <input type="checkbox" :value="role" v-model="form.visible_to_roles"
                  class="mr-1 rounded border-gray-300 dark:border-gray-600 text-amber-600 focus:ring-amber-500 dark:bg-gray-700" />
                <span class="text-sm text-gray-600 dark:text-gray-300">{{ role }}</span>
              </label>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave all unchecked to make visible to everyone</p>
          </div>

          <div v-if="form.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <p class="text-sm text-red-600 dark:text-red-400">{{ form.error }}</p>
          </div>

          <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <Link :href="route('hive.documents.show', { document: document.id })"
              class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 transition">
              Cancel
            </Link>
            <button type="submit" :disabled="form.processing"
              class="inline-flex items-center px-6 py-2.5 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 active:bg-amber-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-50 transition">
              {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </button>
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
  document: Object,
  modules: Array,
});

const form = useForm({
  title: props.document.title,
  description: props.document.description || '',
  module_id: props.document.module_id,
  category: props.document.category,
  audience: props.document.audience || 'module_students',
  visible_to_roles: props.document.visible_to_roles || [],
  error: '',
});

const submit = () => {
  form.put(route('hive.documents.update', { document: props.document.id }), {
    preserveScroll: true,
    onError: (errors) => {
      if (Object.keys(errors).length > 0) {
        form.error = Object.values(errors)[0];
      }
    },
  });
};
</script>