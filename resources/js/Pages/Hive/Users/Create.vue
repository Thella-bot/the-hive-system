<template>
  <HiveLayout title="New User" description="Create a staff member or student account">
    <template #header-actions>
      <Link :href="route('users.index')"
        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
        title="Users">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
      </Link>
    </template>

    <div class="max-w-2xl">
      <form @submit.prevent="submit" class="space-y-5">

        <!-- Account -->
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
          <div class="p-6 space-y-5">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Account</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                <input v-model="form.name" type="text"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                  :class="{ 'border-red-400': form.errors.name }" />
                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                <input v-model="form.email" type="email"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                  :class="{ 'border-red-400': form.errors.email }" />
                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Role <span class="text-red-500">*</span></label>
                <select v-model="form.role"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
                  :class="{ 'border-red-400': form.errors.role }">
                  <option value="">— Select Role —</option>
                  <option v-for="role in roles" :key="role.id" :value="role.name">{{ formatRole(role.name) }}</option>
                </select>
                <p v-if="form.errors.role" class="text-red-500 text-xs mt-1">{{ form.errors.role }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                <input v-model="form.password" type="password"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                  :class="{ 'border-red-400': form.errors.password }" />
                <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                <input v-model="form.password_confirmation" type="password"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>
            </div>
          </div>
        </div>

        <!-- Staff profile fields -->
        <div v-if="isStaffRole" class="bg-white rounded-xl border border-gray-200">
          <div class="p-6 space-y-5">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Staff Details</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Employee Number <span class="text-red-500">*</span></label>
                <input v-model="form.employee_number" type="text" placeholder="EMP-00001"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                  :class="{ 'border-red-400': form.errors.employee_number }" />
                <p v-if="form.errors.employee_number" class="text-red-500 text-xs mt-1">{{ form.errors.employee_number }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                <select v-model="form.department_id"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white">
                  <option :value="null">— None —</option>
                  <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Designation</label>
                <input v-model="form.designation" type="text" placeholder="e.g. Head Chef"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Specialization</label>
                <input v-model="form.specialization" type="text" placeholder="e.g. French Cuisine"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                <input v-model="form.phone" type="text"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Hire Date</label>
                <input v-model="form.hire_date" type="date"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>
            </div>
          </div>
        </div>

        <!-- Student profile fields -->
        <div v-if="isStudentRole" class="bg-white rounded-xl border border-gray-200">
          <div class="p-6 space-y-5">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Student Details</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Student Number <span class="text-red-500">*</span></label>
                <input v-model="form.student_number" type="text" placeholder="STU-000001"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                  :class="{ 'border-red-400': form.errors.student_number }" />
                <p v-if="form.errors.student_number" class="text-red-500 text-xs mt-1">{{ form.errors.student_number }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Cohort</label>
                <select v-model="form.cohort_id"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white">
                  <option :value="null">— None —</option>
                  <option v-for="c in cohorts" :key="c.id" :value="c.id">
                    {{ c.name }} ({{ c.department?.name }})
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Enrolment Date</label>
                <input v-model="form.enrollment_date" type="date"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Expected Graduation</label>
                <input v-model="form.expected_graduation_date" type="date"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>
            </div>

            <!-- Emergency Contact -->
            <div class="pt-2 border-t border-gray-100">
              <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Emergency Contact</h3>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
                  <input v-model="form.emergency_contact_name" type="text"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                  <input v-model="form.emergency_contact_phone" type="text"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1.5">Relationship</label>
                  <input v-model="form.emergency_contact_relationship" type="text" placeholder="e.g. Parent"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
          <Link :href="route('users.index')"
            class="px-4 py-2 text-sm text-gray-600 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
            <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Create User
          </button>
        </div>

      </form>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'

defineProps({
  roles:       { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  cohorts:     { type: Array, default: () => [] },
})

const staffRoles = ['super-admin', 'school-admin', 'department-head', 'chef-instructor', 'academic_staff', 'non_academic_staff']

const form = useForm({
  name: '', email: '', password: '', password_confirmation: '', role: '',
  // Staff
  employee_number: '', department_id: null, designation: '', specialization: '', phone: '', hire_date: '',
  // Student
  student_number: '', cohort_id: null, enrollment_date: '', expected_graduation_date: '',
  emergency_contact_name: '', emergency_contact_phone: '', emergency_contact_relationship: '',
})

const isStaffRole = computed(() => staffRoles.includes(form.role))
const isStudentRole = computed(() => form.role === 'student')

const formatRole = (r) => r.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())

const submit = () => form.post(route('hive.users.store'))
</script>