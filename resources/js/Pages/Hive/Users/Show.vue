<template>
  <HiveLayout :title="managedUser.name" :description="primaryRole">
    <template #header-actions>
      <div class="flex items-center gap-3">
        <Link :href="route('hive.users.index')" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
          ← Users
        </Link>
        <Link v-if="can('edit-users')" :href="route('hive.users.edit', { user: managedUser.id })"
          class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
          Edit
        </Link>
      </div>
    </template>

    <div class="space-y-6 max-w-3xl">

      <!-- Profile header -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 flex items-center gap-6">
        <img :src="managedUser.profile_photo_url" :alt="managedUser.name"
          class="w-20 h-20 rounded-full object-cover flex-shrink-0 ring-4 ring-amber-100" />
        <div class="flex-1 min-w-0">
          <h2 class="text-xl font-bold text-gray-900">{{ managedUser.name }}</h2>
          <p class="text-gray-500 text-sm">{{ managedUser.email }}</p>
          <div class="flex flex-wrap gap-1.5 mt-2">
            <Badge v-for="role in managedUser.roles" :key="role.id" :color="roleColor(role.name)">
              {{ formatRole(role.name) }}
            </Badge>
          </div>
        </div>
      </div>

      <!-- Staff Profile -->
      <div v-if="managedUser.staff_profile" class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-900">Staff Details</h3>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
          <InfoRow label="Employee Number" :value="managedUser.staff_profile.employee_number" />
          <InfoRow label="Department" :value="managedUser.staff_profile.department?.name ?? '—'" />
          <InfoRow label="Designation" :value="managedUser.staff_profile.designation ?? '—'" />
          <InfoRow label="Specialization" :value="managedUser.staff_profile.specialization ?? '—'" />
          <InfoRow label="Phone" :value="managedUser.staff_profile.phone ?? '—'" />
          <InfoRow label="Hire Date" :value="formatDate(managedUser.staff_profile.hire_date)" />
        </div>
      </div>

      <!-- Student Profile -->
      <div v-if="managedUser.student_profile" class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-900">Student Details</h3>
          <Badge :color="managedUser.student_profile.status_color">
            {{ formatStatus(managedUser.student_profile.status) }}
          </Badge>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
          <InfoRow label="Student Number" :value="managedUser.student_profile.student_number" />
          <InfoRow label="Cohort" :value="managedUser.student_profile.cohort?.name ?? '—'" />
          <InfoRow label="Department" :value="managedUser.student_profile.cohort?.department?.name ?? '—'" />
          <InfoRow label="Enrolment Date" :value="formatDate(managedUser.student_profile.enrollment_date)" />
          <InfoRow label="Expected Graduation" :value="formatDate(managedUser.student_profile.expected_graduation_date)" />
          <InfoRow label="Graduation Date" :value="formatDate(managedUser.student_profile.graduation_date)" />
        </div>

        <!-- Dietary restrictions -->
        <div v-if="managedUser.student_profile.dietary_restrictions?.length"
          class="px-6 pb-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Dietary Restrictions</p>
          <div class="flex flex-wrap gap-1.5">
            <Badge v-for="r in managedUser.student_profile.dietary_restrictions" :key="r" color="orange">{{ r }}</Badge>
          </div>
        </div>

        <!-- Emergency contact -->
        <div v-if="managedUser.student_profile.emergency_contact_name"
          class="px-6 pb-6 pt-2 border-t border-gray-100 mt-2">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Emergency Contact</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <InfoRow label="Name" :value="managedUser.student_profile.emergency_contact_name ?? '—'" />
            <InfoRow label="Phone" :value="managedUser.student_profile.emergency_contact_phone ?? '—'" />
            <InfoRow label="Relationship" :value="managedUser.student_profile.emergency_contact_relationship ?? '—'" />
          </div>
        </div>
      </div>

    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'
import Badge from '@/Components/Badge.vue'
import { usePermissions } from '@/composables/usePermissions'

const InfoRow = {
  props: { label: String, value: String },
  template: `
    <div>
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-0.5">{{ label }}</p>
      <p class="text-sm text-gray-900 font-medium">{{ value || '—' }}</p>
    </div>
  `
}

const props = defineProps({
  managedUser: { type: Object, required: true },
})

const { can } = usePermissions()

const primaryRole = computed(() =>
  props.managedUser.roles?.[0]?.name
    ? props.managedUser.roles[0].name.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
    : 'No role assigned'
)

const formatRole = (r) => r.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
const formatStatus = (s) => s?.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) ?? '—'
const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) : '—'

const roleColor = (r) => ({
  'super-admin': 'purple', 'school-admin': 'blue',
  'department-head': 'amber', 'chef-instructor': 'orange', 'student': 'green',
}[r] ?? 'gray')
</script>