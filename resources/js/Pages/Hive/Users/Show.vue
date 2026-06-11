<template>
  <HiveLayout :title="managedUser.name" :description="primaryRole">
    <template #header-actions>
      <div class="flex items-center gap-3">
        <Link :href="route('hive.users.index')"
          class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
          title="Users">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
        </Link>
        <Link v-if="can('edit-users')" :href="route('hive.users.edit', { user: managedUser.id })"
          class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
          Edit
        </Link>
      </div>
    </template>

    <div class="space-y-6">

      <!-- Profile header card -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 flex items-center gap-6">
        <img :src="managedUser.profile_photo_url" :alt="managedUser.name"
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
        <!-- Quick stats -->
        <div class="flex gap-6 text-center">
          <div v-if="applicationCount > 0" class="px-4">
            <p class="text-2xl font-bold text-amber-600">{{ applicationCount }}</p>
            <p class="text-xs text-gray-500 uppercase">Applications</p>
          </div>
          <div v-if="enrollmentCount > 0" class="px-4">
            <p class="text-2xl font-bold text-emerald-600">{{ enrollmentCount }}</p>
            <p class="text-xs text-gray-500 uppercase">Enrollments</p>
          </div>
          <div v-if="certificationCount > 0" class="px-4">
            <p class="text-2xl font-bold text-blue-600">{{ certificationCount }}</p>
            <p class="text-xs text-gray-500 uppercase">Certifications</p>
          </div>
          <div v-if="documentCount > 0" class="px-4">
            <p class="text-2xl font-bold text-purple-600">{{ documentCount }}</p>
            <p class="text-xs text-gray-500 uppercase">Documents</p>
          </div>
        </div>
      </div>

      <!-- Basic Information -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-900">Basic Information</h3>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <InfoRow label="Full Name" :value="managedUser.name" />
          <InfoRow label="Email Address" :value="managedUser.email" />
          <InfoRow label="Phone Number" :value="managedUser.profile?.phone ?? '—'" />
          <InfoRow label="Date of Birth" :value="formatDate(managedUser.profile?.date_of_birth)" />
          <InfoRow label="Address" :value="managedUser.profile?.address ?? '—'" class="sm:col-span-2" />
          <InfoRow label="Email Verified" :value="managedUser.email_verified_at ? formatDate(managedUser.email_verified_at) : 'Not verified'" />
          <InfoRow label="Account Created" :value="formatDate(managedUser.created_at)" />
          <InfoRow label="Last Login" :value="managedUser.last_login_at ? formatDate(managedUser.last_login_at) : 'Never'" />
          <InfoRow v-if="programme" label="Programme" :value="programme.name" />
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
        <!-- Social links -->
        <div v-if="managedUser.profile?.twitter_handle || managedUser.profile?.linkedin_profile"
          class="px-6 pb-6 pt-4 border-t border-gray-100 flex gap-4">
          <a v-if="managedUser.profile.twitter_handle" :href="`https://twitter.com/${managedUser.profile.twitter_handle}`"
            target="_blank" class="text-sm text-blue-500 hover:underline">
            @{{ managedUser.profile.twitter_handle }}
          </a>
          <a v-if="managedUser.profile.linkedin_profile" :href="managedUser.profile.linkedin_profile"
            target="_blank" class="text-sm text-blue-600 hover:underline">
            LinkedIn Profile
          </a>
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
          <InfoRow label="Cohort" :value="managedUser.profile.cohort?.name ?? '—'" />
          <InfoRow label="Department" :value="managedUser.profile.cohort?.department?.name ?? '—'" />
          <InfoRow label="Status" :value="formatStatus(managedUser.profile.status)" />
          <InfoRow label="Enrolment Date" :value="formatDate(managedUser.profile.enrollment_date)" />
          <InfoRow label="Expected Graduation" :value="formatDate(managedUser.profile.expected_graduation_date)" />
          <InfoRow label="Graduation Date" :value="formatDate(managedUser.profile.graduation_date) " />
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

      <!-- Applications -->
      <div v-if="applications?.length" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-blue-50 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-900">Applications</h3>
          <span class="text-xs text-gray-500">{{ applications.length }} total</span>
        </div>
        <div class="divide-y divide-gray-100">
          <Link v-for="app in applications" :key="app.id"
            :href="route('hive.applications.show', app.id)"
            class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
            <div class="flex-1">
              <p class="font-medium text-gray-900">{{ app.programme?.name ?? 'Unknown Programme' }}</p>
              <p class="text-sm text-gray-500">
                {{ app.variant?.label ?? 'Standard' }} &bull; Applied {{ formatDate(app.created_at) }}
              </p>
            </div>
            <div class="flex items-center gap-3">
              <Badge :color="app.status === 'approved' ? 'green' : app.status === 'rejected' ? 'red' : 'gray'">
                {{ app.status }}
              </Badge>
              <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </Link>
        </div>
      </div>

      <!-- Enrollments -->
      <div v-if="enrollments?.length" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-purple-50 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-900">Module Enrollments</h3>
          <span class="text-xs text-gray-500">{{ enrollments.length }} modules</span>
        </div>
        <div class="divide-y divide-gray-100">
          <div v-for="enrollment in enrollments" :key="enrollment.id"
            class="px-6 py-4 flex items-center justify-between">
            <div class="flex-1">
              <p class="font-medium text-gray-900">{{ enrollment.module?.name ?? 'Unknown Module' }}</p>
              <p class="text-sm text-gray-500">{{ enrollment.cohort?.name ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-4">
              <span v-if="enrollment.grade" class="text-sm font-semibold"
                :class="enrollment.grade >= 50 ? 'text-emerald-600' : 'text-red-600'">
                {{ enrollment.grade }}%
              </span>
              <Badge :color="enrollment.status === 'active' ? 'green' : enrollment.status === 'completed' ? 'blue' : 'gray'">
                {{ enrollment.status }}
              </Badge>
            </div>
          </div>
        </div>
      </div>

      <!-- Certifications -->
      <div v-if="certifications?.length" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-amber-50 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-900">Certifications</h3>
          <span class="text-xs text-gray-500">{{ certifications.length }} earned</span>
        </div>
        <div class="divide-y divide-gray-100">
          <div v-for="cert in certifications" :key="cert.id"
            class="px-6 py-4 flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">{{ cert.name }}</p>
              <p class="text-sm text-gray-500">{{ cert.module?.name ?? '—' }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-500">Awarded {{ formatDate(cert.awarded_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state for users with no data -->
      <div v-if="!managedUser.profile && !applications?.length && !enrollments?.length && !certifications?.length"
        class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
          <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <p class="text-gray-500">No profile information or records found for this user.</p>
        <Link v-if="can('edit-users')" :href="route('hive.users.edit', { user: managedUser.id })"
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
import { usePermissions } from '@/composables/usePermissions'

const props = defineProps({
  managedUser: { type: Object, required: true },
  applications: { type: Array, default: () => [] },
  enrollments: { type: Array, default: () => [] },
  certifications: { type: Array, default: () => [] },
  documentCount: { type: Number, default: 0 },
  programme: { type: Object, default: null },
})

const { can } = usePermissions()

const primaryRole = computed(() =>
  props.managedUser.roles?.[0]?.name
    ? props.managedUser.roles[0].name.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
    : 'No role assigned'
)

const applicationCount = computed(() => props.applications?.length ?? 0)
const enrollmentCount = computed(() => props.enrollments?.length ?? 0)
const certificationCount = computed(() => props.certifications?.length ?? 0)

const formatRole = (r) => r.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
const formatStatus = (s) => s?.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) ?? '—'
const formatDate = (d) => {
  if (!d) return '—'
  const date = new Date(d)
  return isNaN(date.getTime()) ? '—' : date.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })
}

const roleColor = (r) => ({
  'super-admin': 'purple', 'school-admin': 'blue',
  'department-head': 'amber', 'chef-instructor': 'orange', 'student': 'green',
  'applicant': 'cyan', 'non_academic_staff': 'gray',
}[r] ?? 'gray')
</script>