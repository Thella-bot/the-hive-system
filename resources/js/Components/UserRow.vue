<template>
  <tr :class="['hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors', className]">
    <td class="px-6 py-4">
      <div class="flex items-center gap-3">
        <img :src="user.profile_photo_url" :alt="user.name"
          class="w-10 h-10 rounded-full object-cover flex-shrink-0 ring-2 ring-amber-100 dark:ring-amber-900" />
        <div>
          <p class="font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
        </div>
      </div>
    </td>
    <td class="px-6 py-4">
      <slot name="role" :user="user">
        <div class="flex flex-wrap gap-1">
          <Badge v-for="role in user.roles" :key="role.id" :color="roleColor(role.name)">
            {{ formatRole(role.name) }}
          </Badge>
        </div>
      </slot>
    </td>
    <td v-if="$slots.affiliation" class="px-6 py-4 text-gray-500 dark:text-gray-400">
      <slot name="affiliation" :user="user">
        <span v-if="user.profile?.department">
          {{ user.profile.department.name }}
        </span>
        <span v-else-if="user.profile?.cohort">
          {{ user.profile.cohort.name }}
        </span>
        <span v-else class="text-gray-300 dark:text-gray-600">—</span>
      </slot>
    </td>
    <td class="px-6 py-4">
      <div class="flex items-center justify-end gap-1">
        <slot name="actions" :user="user" />
      </div>
    </td>
  </tr>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  className: {
    type: String,
    default: ''
  }
})

const formatRole = (r) => r.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())

const roleColor = (r) => ({
  'super-admin': 'purple',
  'it-support': 'indigo',
  'academic-director': 'violet',
  'program-coordinator': 'fuchsia',
  'chef-instructor': 'orange',
  'pastry-instructor': 'amber',
  'sous-chef': 'yellow',
  'admissions-officer': 'cyan',
  'examination-cell': 'sky',
  'registrar': 'blue',
  'finance': 'emerald',
  'procurement-manager': 'teal',
  'storekeeper': 'lime',
  'hr-manager': 'rose',
  'librarian': 'pink',
  'career-services': 'red',
  'events-pr-manager': 'orange',
  'cafeteria-manager': 'yellow',
  'student': 'green',
  'parent-guardian': 'teal',
  'alumni': 'slate',
}[r] ?? 'gray')
</script>
