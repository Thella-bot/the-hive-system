<template>
  <HiveLayout>
    <h1 class="text-2xl font-bold mb-4">My Profile</h1>
    <form @submit.prevent="form.post(route('hive.profile.update'))" class="max-w-md space-y-4">
      <div>
        <label>First Name</label>
        <input v-model="form.first_name" class="w-full border rounded p-2" />
      </div>
      <div>
        <label>Last Name</label>
        <input v-model="form.last_name" class="w-full border rounded p-2" />
      </div>
      <div>
        <label>Date of Birth</label>
        <input type="date" v-model="form.date_of_birth" class="w-full border rounded p-2" />
      </div>
      <div>
        <label>Phone</label>
        <input v-model="form.phone" class="w-full border rounded p-2" />
      </div>
      <div>
        <label>Address</label>
        <textarea v-model="form.address" class="w-full border rounded p-2"></textarea>
      </div>
      <div>
        <label>Emergency Contact Name</label>
        <input v-model="form.emergency_contact_name" class="w-full border rounded p-2" />
      </div>
      <div>
        <label>Emergency Contact Phone</label>
        <input v-model="form.emergency_contact_phone" class="w-full border rounded p-2" />
      </div>

      <!-- Student-specific fields -->
      <div v-if="isStudent">
        <label>Emergency Contact Relationship</label>
        <input v-model="form.emergency_contact_relationship" class="w-full border rounded p-2" />
      </div>
      <div v-if="isStudent">
        <label>Dietary Restrictions</label>
        <textarea v-model="form.dietary_restrictions" class="w-full border rounded p-2"></textarea>
      </div>

      <!-- Staff Fields -->
      <div v-if="isStaff" class="space-y-4">
        <h2 class="text-xl font-semibold mt-6 mb-2 border-t pt-4">Staff Information</h2>
        <div>
          <label>Employee Number</label>
          <input v-model="form.employee_number" class="w-full border rounded p-2" />
        </div>
        <div>
          <label>Department</label>
          <select v-model="form.department_id" class="w-full border rounded p-2">
            <option :value="null">Select Department</option>
            <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
          </select>
        </div>
        <div>
          <label>Designation</label>
          <input v-model="form.designation" class="w-full border rounded p-2" />
        </div>
        <div>
          <label>Specialization</label>
          <input v-model="form.specialization" class="w-full border rounded p-2" />
        </div>
        <div>
          <label>Bio</label>
          <textarea v-model="form.bio" class="w-full border rounded p-2"></textarea>
        </div>
        <div>
          <label>Hire Date</label>
          <input type="date" v-model="form.hire_date" class="w-full border rounded p-2" />
        </div>
        <div>
          <label>Annual Leave Days</label>
          <input type="number" v-model="form.annual_leave_days" class="w-full border rounded p-2" />
        </div>
        <div>
          <label>Leave Balance</label>
          <input type="number" v-model="form.leave_balance" class="w-full border rounded p-2" />
        </div>
      </div>

      <!-- Student Fields -->
      <div v-if="isStudent" class="space-y-4">
        <h2 class="text-xl font-semibold mt-6 mb-2 border-t pt-4">Student Information</h2>
        <div>
          <label>Student Number</label>
          <input v-model="form.student_number" class="w-full border rounded p-2" />
        </div>
        <div>
          <label>Cohort</label>
          <select v-model="form.cohort_id" class="w-full border rounded p-2">
            <option :value="null">Select Cohort</option>
            <option v-for="cohort in cohorts" :key="cohort.id" :value="cohort.id">{{ cohort.name }}</option>
          </select>
        </div>
        <div>
          <label>Enrollment Date</label>
          <input type="date" v-model="form.enrollment_date" class="w-full border rounded p-2" />
        </div>
        <div>
          <label>Expected Graduation Date</label>
          <input type="date" v-model="form.expected_graduation_date" class="w-full border rounded p-2" />
        </div>
        <div>
          <label>Graduation Date</label>
          <input type="date" v-model="form.graduation_date" class="w-full border rounded p-2" />
        </div>
        <div>
          <label>Status</label>
          <select v-model="form.status" class="w-full border rounded p-2">
            <option value="active">Active</option>
            <option value="graduated">Graduated</option>
            <option value="withdrawn">Withdrawn</option>
            <option value="deferred">Deferred</option>
          </select>
        </div>
      </div>

      <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Save</button>
    </form>
  </HiveLayout>
</template>
<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { computed } from 'vue';

const props = defineProps({
  profile: Object,
  departments: Array,
  cohorts: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const isStaff = computed(() => user.value.roles.some(role => role.name === 'academic_staff' || role.name === 'non_academic_staff'));
const isStudent = computed(() => user.value.roles.some(role => role.name === 'student'));

const form = useForm({
  first_name: props.profile?.first_name ?? '',
  last_name: props.profile?.last_name ?? '',
  date_of_birth: props.profile?.date_of_birth ?? '',
  phone: props.profile?.phone ?? '',
  address: props.profile?.address ?? '',
  emergency_contact_name: props.profile?.emergency_contact_name ?? '',
  emergency_contact_phone: props.profile?.emergency_contact_phone ?? '',
  emergency_contact_relationship: props.profile?.emergency_contact_relationship ?? '',
  dietary_restrictions: props.profile?.dietary_restrictions ?? '',

  // Staff fields
  employee_number: props.profile?.employee_number ?? '',
  department_id: props.profile?.department_id ?? null,
  designation: props.profile?.designation ?? '',
  specialization: props.profile?.specialization ?? '',
  bio: props.profile?.bio ?? '',
  hire_date: props.profile?.hire_date ?? '',
  annual_leave_days: props.profile?.annual_leave_days ?? 0,
  leave_balance: props.profile?.leave_balance ?? 0,

  // Student fields
  student_number: props.profile?.student_number ?? '',
  cohort_id: props.profile?.cohort_id ?? null,
  enrollment_date: props.profile?.enrollment_date ?? '',
  expected_graduation_date: props.profile?.expected_graduation_date ?? '',
  graduation_date: props.profile?.graduation_date ?? '',
  status: props.profile?.status ?? 'active',
});
</script>