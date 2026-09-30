<template>
  <Link
    v-if="isVisible"
    :href="href"
    :target="target"
    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
    :class="isActive
      ? 'bg-amber-600 text-white'
      : 'text-gray-300 hover:bg-gray-800 hover:text-white'"
  >
    <span class="flex-shrink-0">
      <slot name="icon" />
    </span>
    <slot />
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useUser } from '@/composables/useUser'
import { usePermissions } from '@/composables/usePermissions'

const props = defineProps({
  href:   { type: String, required: true },
  active: { type: [Boolean, String], default: false },
  target: { type: String, default: null },
  // Audience types: module_students, student_only, staff_only, all_users, everyone
  audience: { type: String, default: 'all_users' },
  // Optional permission gate, any-of when an array is given.
  permission: { type: [String, Array], default: null },
})

const { currentUser, isStudent, isStaff } = useUser()
const { canAny, isUnseeded } = usePermissions()

const isActive = computed(() => {
  if (typeof props.active === 'boolean') {
    return props.active
  }
  return props.active ? route().current(props.active) : false
})

// Check if current user can see this nav item based on audience
const isVisible = computed(() => {
  // Nothing has been seeded yet, so defer to audience alone.
  if (!isUnseeded.value && props.permission) {
    if (!canAny(props.permission)) return false
  }

  const audience = props.audience

  // Everyone can see public items
  if (audience === 'everyone') return true

  // Must be logged in for all other audiences
  if (!currentUser.value) return false

  switch (audience) {
    case 'everyone':
      return true
    case 'all_users':
      return true // All authenticated users
    case 'staff_only':
      return isStaff.value
    case 'student_only':
      return isStudent.value || isStaff.value // Students and staff can both see "student_only"
    case 'module_students':
      return isStudent.value || isStaff.value
    default:
      return true
  }
})
</script>
