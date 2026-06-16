<template>
  <HiveLayout :title="`Edit: ${department.name}`" description="Update department details">
    <template #header-actions>
      <Link :href="route('hive.departments.show', { department: department.id })"
        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
        title="Back to Department">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
      </Link>
    </template>

    <div class="max-w-2xl">
      <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">

        <div class="p-6 space-y-5">
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Basic Information</h2>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Department Name <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              :class="{ 'border-red-400': form.errors.name }" />
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
            <textarea v-model="form.description" rows="3"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition resize-none">
            </textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Department Head</label>
            <select v-model="form.head_user_id"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white">
              <option :value="null">— None assigned —</option>
              <option v-for="user in eligibleHeads" :key="user.id" :value="user.id">
                {{ user.name }} ({{ user.email }})
              </option>
            </select>
          </div>
        </div>

        <div class="p-6 space-y-5">
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Appearance</h2>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Department Colour</label>
            <div class="flex items-center gap-3">
              <input v-model="form.color" type="color"
                class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer p-0.5" />
              <div class="flex gap-2 flex-wrap">
                <button v-for="preset in colorPresets" :key="preset" type="button"
                  @click="form.color = preset"
                  class="w-7 h-7 rounded-full border-2 transition-transform hover:scale-110"
                  :style="{ backgroundColor: preset }"
                  :class="form.color === preset ? 'border-gray-800 scale-110' : 'border-transparent'">
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="p-6">
          <label class="flex items-center gap-3 cursor-pointer">
            <div class="relative">
              <input v-model="form.is_active" type="checkbox" class="sr-only" />
              <div class="w-10 h-6 rounded-full transition-colors" :class="form.is_active ? 'bg-amber-500' : 'bg-gray-300'"></div>
              <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform"
                :class="form.is_active ? 'translate-x-4' : 'translate-x-0'"></div>
            </div>
            <span class="text-sm font-medium text-gray-700">Active department</span>
          </label>
        </div>

        <div class="px-6 py-4 bg-gray-50 flex items-center justify-between rounded-b-xl">
          <!-- Delete -->
          <button type="button" @click="confirmDelete = true"
            class="text-sm text-red-500 hover:text-red-700 font-medium">
            Delete department
          </button>
          <div class="flex gap-3">
            <Link :href="route('hive.departments.show', { department: department.id })"
              class="px-4 py-2 text-sm text-gray-600 font-medium rounded-lg hover:bg-gray-100 transition-colors">
              Cancel
            </Link>
            <button type="submit" :disabled="form.processing"
              class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
              Save Changes
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Delete confirmation modal -->
    <div v-if="confirmDelete" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Department?</h3>
        <p class="text-sm text-gray-500 mb-6">
          This will soft-delete <strong>{{ department.name }}</strong>. Cohorts and staff assigned to it will remain but lose their department link.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="confirmDelete = false"
            class="px-4 py-2 text-sm text-gray-600 font-medium rounded-lg hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <Link :href="route('hive.departments.destroy', { department: department.id })" method="delete" as="button"
            class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition-colors">
            Yes, Delete
          </Link>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'

const props = defineProps({
  department:    { type: Object, required: true },
  eligibleHeads: { type: Array, default: () => [] },
  users:         { type: Array, default: () => [] },
})

const colorPresets = ['#f59e0b', '#ef4444', '#3b82f6', '#10b981', '#8b5cf6', '#f97316', '#06b6d4', '#ec4899']
const confirmDelete = ref(false)

const staff = ref(props.department.staff.map(s => s.id));
// Staff = users who are NOT students, parent-guardians, or alumni
const staffUsers = ref(props.users.filter(user => {
  const roleNames = user.roles.map(r => r.name);
  return !roleNames.includes('student') && !roleNames.includes('parent-guardian') && !roleNames.includes('alumni');
}));

const form = useForm({
  name:         props.department.name,
  description:  props.department.description ?? '',
  head_user_id: props.department.head_user_id,
  color:        props.department.color,
  is_active:    props.department.is_active,
})

const submit = () => form.put(route('hive.departments.update', { department: props.department.id }))
</script>