<template>
  <HiveLayout :title="`Edit: ${managedStudent.name}`" description="Update student profile">
    <template #header-actions>
      <Link :href="route('hive.students.show', { student: managedStudent.id })"
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

        <!-- Student Details -->
        <div class="bg-white rounded-xl border border-gray-200">
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
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Programme</label>
                <select v-model="form.programme_id"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white">
                  <option :value="null">— None —</option>
                  <option v-for="p in programmes" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </div>
              <div v-if="!isAdmin">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Programme</label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3.5 py-2.5">
                  {{ form.programme_id ? programmes.find(p => p.id === form.programme_id)?.name : '—' }}
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
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Enrollment Date</label>
                <input v-model="form.enrollment_date" type="date"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>
              <div v-if="!isAdmin">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Enrollment Date</label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3.5 py-2.5">
                  {{ formatDate(form.enrollment_date) }}
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

        <!-- Emergency Contact -->
        <div class="bg-white rounded-xl border border-gray-200">
          <div class="p-6 space-y-5">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Emergency Contact</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Name</label>
                <input v-model="form.emergency_contact_name" type="text"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                <input v-model="form.emergency_contact_phone" type="tel"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>

              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Relationship</label>
                <input v-model="form.emergency_contact_relationship" type="text"
                  class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <Link :href="route('hive.students.show', { student: managedStudent.id })"
            class="px-4 py-2 text-sm text-gray-600 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
            Save Changes
          </button>
        </div>

      </form>
    </div>
  </HiveLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'

const props = defineProps({
  managedStudent: { type: Object, required: true },
  programmes: { type: Array, default: () => [] },
  cohorts: { type: Array, default: () => [] },
  isAdmin: { type: Boolean, default: false },
})

const p = props.managedStudent.profile

const form = useForm({
  name: props.managedStudent.name,
  email: props.managedStudent.email,
  password: '',
  password_confirmation: '',
  // Student profile
  student_number: p?.student_number ?? '',
  programme_id: props.managedStudent.programme?.id ?? null,
  cohort_id: p?.cohort_id ?? null,
  enrollment_date: p?.enrollment_date ?? '',
  expected_graduation_date: p?.expected_graduation_date ?? '',
  status: p?.status ?? 'active',
  emergency_contact_name: p?.emergency_contact_name ?? '',
  emergency_contact_phone: p?.emergency_contact_phone ?? '',
  emergency_contact_relationship: p?.emergency_contact_relationship ?? '',
})

const formatStatus = (s) => s?.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) ?? '—'
const formatDate = (d) => {
  if (!d) return '—'
  const date = new Date(d)
  return isNaN(date.getTime()) ? '—' : date.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })
}

const submit = () => form.put(route('hive.students.update', { student: props.managedStudent.id }))
</script>