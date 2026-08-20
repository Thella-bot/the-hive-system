<template>
  <HiveLayout title="My Profile" description="View your complete profile information">
    <template #header-actions>
      <Link :href="route('hive.profile.edit')"
        class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        Edit Profile
      </Link>
    </template>

    <div class="space-y-6">

      <!-- Profile header card -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 flex items-center gap-6">
        <img :src="managedUser.profile?.profile_picture_path ? '/storage/' + managedUser.profile.profile_picture_path : managedUser.profile_photo_url" :alt="managedUser.name"
          class="w-24 h-24 rounded-full object-cover flex-shrink-0 ring-4 ring-amber-100" />
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold text-gray-900">{{ managedUser.name }}</h2>
          <p class="text-gray-500 text-sm mt-1">{{ managedUser.email }}</p>
          <div class="flex flex-wrap gap-2 mt-3">
            <Badge v-for="role in managedUser.roles" :key="role.id" :color="roleColor(role.name)">
              {{ formatRole(role.name) }}
            </Badge>
          </div>
        </div>
      </div>

      <!-- Basic Information -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
          <h3 class="font-semibold text-gray-900">Basic Information</h3>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <InfoRow label="Full Name" :value="managedUser.name" />
          <InfoRow label="Email Address" :value="managedUser.email" />
          <InfoRow label="Gender" :value="managedUser.gender ?? '—'" />
          <InfoRow label="National ID Number" :value="managedUser.national_id_number ?? '—'" />
          <InfoRow label="Phone Number" :value="managedUser.profile?.phone ?? '—'" />
          <InfoRow label="Date of Birth" :value="formatDate(managedUser.profile?.date_of_birth)" />
          <InfoRow label="Address" :value="managedUser.profile?.address ?? '—'" class="sm:col-span-2" />
          <InfoRow label="Email Verified" :value="managedUser.email_verified_at ? formatDate(managedUser.email_verified_at) : 'Not verified'" />
          <InfoRow label="Account Created" :value="formatDate(managedUser.created_at)" />
        </div>
      </div>

      <!-- Staff Profile -->
      <div v-if="managedUser.profile?.employee_number" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-amber-50 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-900">Staff Details</h3>
          <Badge color="amber">Staff</Badge>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <InfoRow label="Employee Number" :value="managedUser.profile.employee_number" />
          <InfoRow label="Department" :value="managedUser.profile.department?.name ?? '—'" />
          <InfoRow label="Designation" :value="managedUser.profile.designation ?? '—'" />
          <InfoRow label="Specialization" :value="managedUser.profile.specialization ?? '—'" />
          <InfoRow label="Hire Date" :value="formatDate(managedUser.profile.hire_date)" />
          <InfoRow label="Leave Balance" :value="managedUser.profile.leave_balance !== null ? `${managedUser.profile.leave_balance} days` : '—'" />
          <InfoRow label="Annual Leave" :value="managedUser.profile.annual_leave_days !== null ? `${managedUser.profile.annual_leave_days} days` : '—'" />
          <InfoRow label="Bio" :value="managedUser.profile.bio ?? '—'" />
        </div>
      </div>

      <!-- Student Profile -->
      <div v-if="managedUser.profile?.student_number" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-emerald-50 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-900">Student Details</h3>
          <div class="flex items-center gap-2">
            <Badge :color="managedUser.profile.status">
              {{ formatStatus(managedUser.profile.status) }}
            </Badge>
          </div>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <InfoRow label="Student Number" :value="managedUser.profile.student_number" />
          <InfoRow label="Programme" :value="programme?.name ?? '—'" />
          <InfoRow label="Cohort" :value="managedUser.profile.cohort?.name ?? '—'" />
          <InfoRow label="Department" :value="managedUser.profile.cohort?.department?.name ?? '—'" />
          <InfoRow label="Status" :value="formatStatus(managedUser.profile.status)" />
          <InfoRow label="Enrolment Date" :value="formatDate(managedUser.profile.enrollment_date)" />
          <InfoRow label="Expected Graduation" :value="formatDate(managedUser.profile.expected_graduation_date)" />
          <InfoRow label="Graduation Date" :value="formatDate(managedUser.profile.graduation_date)" />
        </div>

        <!-- Dietary restrictions -->
        <div v-if="managedUser.profile.dietary_restrictions?.length"
          class="px-6 py-4 border-t border-gray-100">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Dietary Restrictions</p>
          <div class="flex flex-wrap gap-2">
            <Badge v-for="r in managedUser.profile.dietary_restrictions" :key="r" color="orange">{{ r }}</Badge>
          </div>
        </div>

        <!-- Emergency contact -->
        <div v-if="managedUser.profile.emergency_contact_name"
          class="px-6 py-4 border-t border-gray-100">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Emergency Contact</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <InfoRow label="Name" :value="managedUser.profile.emergency_contact_name ?? '—'" />
            <InfoRow label="Phone" :value="managedUser.profile.emergency_contact_phone ?? '—'" />
            <InfoRow label="Relationship" :value="managedUser.profile.emergency_contact_relationship ?? '—'" />
          </div>
        </div>
      </div>

      <!-- Empty state for users with no profile -->
      <div v-if="!managedUser.profile" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
          <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <p class="text-gray-500">No profile information found.</p>
        <Link :href="route('hive.profile.edit')"
          class="inline-block mt-4 text-amber-600 hover:text-amber-700 font-medium">
          Create Profile
        </Link>
      </div>

    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'
import Badge from '@/Components/Badge.vue'
import InfoRow from '@/Components/InfoRow.vue'

const props = defineProps({
  managedUser: { type: Object, required: true },
  programme: { type: Object, default: null },
})

const formatRole = (r) => r.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
const formatStatus = (s) => s?.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) ?? '—'
const formatDate = (d) => {
  if (!d) return '—'
  const date = new Date(d)
  return isNaN(date.getTime()) ? '—' : date.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })
}

const roleColor = (r) => ({
  'super-admin': 'purple', 'it-support': 'indigo', 'academic-director': 'violet',
  'program-coordinator': 'fuchsia', 'chef-instructor': 'orange', 'pastry-instructor': 'amber',
  'sous-chef': 'yellow', 'admissions-officer': 'cyan', 'examination-cell': 'sky',
  'registrar': 'blue', 'finance': 'emerald', 'procurement-manager': 'teal',
  'storekeeper': 'lime', 'hr-manager': 'rose', 'librarian': 'pink',
  'career-services': 'red', 'events-pr-manager': 'orange', 'cafeteria-manager': 'yellow',
  'student': 'green', 'parent-guardian': 'teal', 'alumni': 'slate',
  'applicant': 'cyan',
}[r] ?? 'gray')
</script>
