<template>
  <HiveLayout title="New Department" description="Create a new culinary department">
    <template #header-actions>
      <Link :href="route('departments.index')"
        class="text-sm text-gray-500 hover:text-gray-700 font-medium flex items-center gap-1">
        ← Back to Departments
      </Link>
    </template>

    <div class="max-w-2xl">
      <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">

        <!-- Basic Info -->
        <div class="p-6 space-y-5">
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Basic Information</h2>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Department Name <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text" placeholder="e.g. Pastry Arts"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              :class="{ 'border-red-400': form.errors.name }" />
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
            <textarea v-model="form.description" rows="3"
              placeholder="Brief description of this department..."
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition resize-none"
              :class="{ 'border-red-400': form.errors.description }">
            </textarea>
            <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
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
            <p v-if="form.errors.head_user_id" class="text-red-500 text-xs mt-1">{{ form.errors.head_user_id }}</p>
          </div>
        </div>

        <!-- Appearance -->
        <div class="p-6 space-y-5">
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Appearance</h2>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Department Colour</label>
            <div class="flex items-center gap-3">
              <input v-model="form.color" type="color"
                class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer p-0.5" />
              <div class="flex gap-2 flex-wrap">
                <button v-for="preset in colorPresets" :key="preset"
                  type="button"
                  @click="form.color = preset"
                  class="w-7 h-7 rounded-full border-2 transition-transform hover:scale-110"
                  :style="{ backgroundColor: preset }"
                  :class="form.color === preset ? 'border-gray-800 scale-110' : 'border-transparent'">
                </button>
              </div>
            </div>
          </div>

          <!-- Preview -->
          <div class="rounded-lg overflow-hidden border border-gray-200">
            <div class="h-1.5" :style="{ backgroundColor: form.color }"></div>
            <div class="p-4 bg-gray-50">
              <p class="text-sm font-semibold text-gray-900">{{ form.name || 'Department Name' }}</p>
              <p class="text-xs text-gray-400 mt-0.5">Preview</p>
            </div>
          </div>
        </div>

        <!-- Status -->
        <div class="p-6">
          <label class="flex items-center gap-3 cursor-pointer">
            <div class="relative">
              <input v-model="form.is_active" type="checkbox" class="sr-only" />
              <div class="w-10 h-6 rounded-full transition-colors"
                :class="form.is_active ? 'bg-amber-500' : 'bg-gray-300'">
              </div>
              <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform"
                :class="form.is_active ? 'translate-x-4' : 'translate-x-0'">
              </div>
            </div>
            <span class="text-sm font-medium text-gray-700">Active department</span>
          </label>
          <p class="text-xs text-gray-400 mt-1 ml-13">Inactive departments are hidden from most views.</p>
        </div>

        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-50 flex items-center justify-end gap-3 rounded-b-xl">
          <Link :href="route('departments.index')"
            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium rounded-lg hover:bg-gray-100 transition-colors">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-5 py-2 bg-amber-500 hover:bg-amber-600 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
            <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Create Department
          </button>
        </div>

      </form>
    </div>
  </HiveLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'

defineProps({
  eligibleHeads: { type: Array, default: () => [] },
})

const colorPresets = [
  '#f59e0b', '#ef4444', '#3b82f6', '#10b981',
  '#8b5cf6', '#f97316', '#06b6d4', '#ec4899',
]

const form = useForm({
  name:         '',
  description:  '',
  head_user_id: null,
  color:        '#f59e0b',
  is_active:    true,
})

const submit = () => form.post(route('departments.store'))
</script>