<template>
  <HiveLayout :title="`Edit: ${managedUser.name}`" description="Update user account and profile">
    <template #header-actions>
      <Link :href="route('hive.users.show', { user: managedUser.id })"
        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
        title="Back to Profile">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
      </Link>
    </template>

    <div class="max-w-2xl">
      <form @submit.prevent="submit" class="space-y-5">

        <!-- Account -->
        <div class="bg-white rounded-xl border border-gray-200">
          <div class="p-6 space-y-5">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Account</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                <input v-model="form.name" type="text"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                  :class="{ 'border-red-400': form.errors.name }" />
                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3.5 py-2.5">
                  {{ form.email }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Role</label>
                <div v-if="isAdmin" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white">
                  <select v-model="form.role"
                    class="w-full border-0 p-0 text-sm focus:ring-0 bg-transparent">
                    <option v-for="role in roles" :key="role.id" :value="role.name">{{ formatRole(role.name) }}</option>
                  </select>
                </div>
                <div v-else class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3.5 py-2.5">
                  {{ formatRole(form.role) }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password <span class="text-gray-400 font-normal">(leave blank to keep)</span></label>
                <input v-model="form.password" type="password"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
                <input v-model="form.password_confirmation" type="password"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>
            </div>
          </div>
        </div>

        <!-- Staff profile -->
        <div v-if="isStaffRole" class="bg-white rounded-xl border border-gray-200">
          <div class="p-6 space-y-5">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Staff Details</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Employee Number</label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3.5 py-2.5">
                    {{ form.employee_number ?? '— Not assigned —' }}
                </div>
              </div>
              <div v-if="isAdmin">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                <select v-model="form.department_id"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white">
                  <option :value="null">— None —</option>
                  <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
              </div>
              <div v-if="!isAdmin">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3.5 py-2.5">
                  {{ form.department_id ? departments.find(d => d.id === form.department_id)?.name : '—' }}
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Designation</label>
                <input v-model="form.designation" type="text"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Specialization</label>
                <input v-model="form.specialization" type="text"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>
            </div>
          </div>
        </div>

        <!-- Student profile -->
        <div v-if="isStudentRole" class="bg-white rounded-xl border border-gray-200">
          <div class="p-6 space-y-5">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Student Details</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Student Number</label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3.5 py-2.5">
                    {{ form.student_number ?? '— Not assigned —' }}
                </div>
              </div>
              <div v-if="isAdmin">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Cohort</label>
                <select v-model="form.cohort_id"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white">
                  <option :value="null">— None —</option>
                  <option v-for="c in cohorts" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div v-if="!isAdmin">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Cohort</label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3.5 py-2.5">
                  {{ form.cohort_id ? cohorts.find(c => c.id === form.cohort_id)?.name : '—' }}
                </div>
              </div>
              <div v-if="isAdmin">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select v-model="form.status"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white">
                  <option value="active">Active</option>
                  <option value="graduated">Graduated</option>
                  <option value="on_leave">On Leave</option>
                  <option value="suspended">Suspended</option>
                  <option value="withdrawn">Withdrawn</option>
                </select>
              </div>
              <div v-if="!isAdmin">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3.5 py-2.5">
                  {{ formatStatus(form.status) }}
                </div>
              </div>
              <div v-if="isAdmin">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Expected Graduation</label>
                <input v-model="form.expected_graduation_date" type="date"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>
              <div v-if="!isAdmin">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Expected Graduation</label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3.5 py-2.5">
                  {{ formatDate(form.expected_graduation_date) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
          <button v-if="can('delete-users') && !isSelf" type="button" @click="confirmDelete = true"
            class="text-sm text-red-500 hover:text-red-700 font-medium">Delete user</button>
          <span v-else></span>
          <div class="flex gap-3">
            <Link :href="route('hive.users.show', { user: managedUser.id })"
              class="px-4 py-2 text-sm text-gray-600 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
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

    <!-- Delete confirmation -->
    <div v-if="confirmDelete" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete User?</h3>
        <p class="text-sm text-gray-500 mb-6">
          This will permanently delete <strong>{{ managedUser.name }}</strong>'s account and all associated profile data.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="confirmDelete = false"
            class="px-4 py-2 text-sm text-gray-600 font-medium rounded-lg hover:bg-gray-100">Cancel</button>
          <Link :href="route('hive.users.destroy', { user: managedUser.id })" method="delete" as="button"
            class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg">Delete</Link>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'
import { usePermissions } from '@/composables/usePermissions'
import { useUser } from '@/composables/useUser'

const props = defineProps({
  managedUser: { type: Object, required: true },
  roles:       { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  cohorts:     { type: Array, default: () => [] },
  isAdmin:     { type: Boolean, default: false },
})

const { can } = usePermissions()
const { currentUser } = useUser()
const isSelf = computed(() => currentUser.value?.id === props.managedUser.id)
const confirmDelete = ref(false)

const staffRoles = ['super-admin', 'school-admin', 'department-head', 'chef-instructor', 'academic-staff', 'non-academic-staff']
const p = props.managedUser.profile

const form = useForm({
  name: props.managedUser.name, email: props.managedUser.email,
  password: '', password_confirmation: '',
  role: props.managedUser.roles?.[0]?.name ?? '',
  // Staff
  employee_number: p?.employee_number ?? '', department_id: p?.department_id ?? null,
  designation: p?.designation ?? '', specialization: p?.specialization ?? '',
  phone: p?.phone ?? '', hire_date: p?.hire_date ?? '',
  // Student
  student_number: p?.student_number ?? '', cohort_id: p?.cohort_id ?? null,
  enrollment_date: p?.enrollment_date ?? '', expected_graduation_date: p?.expected_graduation_date ?? '',
  status: p?.status ?? 'active',
  emergency_contact_name: p?.emergency_contact_name ?? '',
  emergency_contact_phone: p?.emergency_contact_phone ?? '',
  emergency_contact_relationship: p?.emergency_contact_relationship ?? '',
})

const isStaffRole = computed(() => staffRoles.includes(form.role))
const isStudentRole = computed(() => form.role === 'student')

const formatRole = (r) => r.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
const formatStatus = (s) => s?.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) ?? '—'
const formatDate = (d) => {
  if (!d) return '—'
  const date = new Date(d)
  return isNaN(date.getTime()) ? '—' : date.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })
}

const submit = () => form.put(route('hive.users.update', { user: props.managedUser.id }))
</script>