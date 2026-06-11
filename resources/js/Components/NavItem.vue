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

const props = defineProps({
  href:   { type: String, required: true },
  active: { type: [Boolean, String], default: false },
  target: { type: String, default: null },
  // Audience types: module_students, student_only, staff_only, all_users, everyone
  audience: { type: String, default: 'all_users' },
})

const { currentUser } = useUser()

const isActive = computed(() => {
  if (typeof props.active === 'boolean') {
    return props.active
  }
  return props.active ? route().current(props.active) : false
})

// Check if current user can see this nav item based on audience
const isVisible = computed(() => {
  const audience = props.audience

  // Everyone can see public items
  if (audience === 'everyone') return true

  // Must be logged in for all other audiences
  if (!currentUser.value) return audience === 'everyone'

  const roles = currentUser.value.roles?.map(r => r.name) || []
  const isStudent = roles.includes('student')
  const isStaff = roles.some(r => ['academic_staff', 'non_academic_staff', 'department-head', 'chef-instructor', 'school-admin', 'super-admin'].includes(r))

  switch (audience) {
    case 'everyone':
      return true
    case 'all_users':
      return true // All authenticated users
    case 'staff_only':
      return isStaff
    case 'student_only':
      return isStudent || isStaff // Students and staff can both see "student_only"
    case 'module_students':
      return isStudent || isStaff
    default:
      return true
  }
})
</script>
