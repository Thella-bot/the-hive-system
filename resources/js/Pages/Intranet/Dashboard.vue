<template>
  <IntranetLayout>
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
    <!-- Admin -->
    <div v-if="isAdmin" class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-white p-4 shadow rounded"><p class="text-3xl font-bold">{{ data.totalStudents }}</p><p>Students</p></div>
      <div class="bg-white p-4 shadow rounded"><p class="text-3xl font-bold">{{ data.totalInstructors }}</p><p>Instructors</p></div>
      <div class="bg-white p-4 shadow rounded"><p class="text-3xl font-bold">{{ data.totalStaff }}</p><p>Staff</p></div>
      <div class="bg-blue-50 p-4 shadow rounded"><p class="text-3xl font-bold">{{ data.pendingApprovals }}</p><p>Pending Approvals</p></div>
      <div class="bg-yellow-50 p-4 shadow rounded"><p class="text-3xl font-bold">{{ data.pendingLeaveRequests }}</p><p>Pending Leaves</p></div>
    </div>

    <!-- Instructor -->
    <div v-if="isInstructor">
      <h2 class="text-xl font-semibold mb-2">My Modules ({{ data.myModulesCount }})</h2>
      <h3 class="font-semibold mt-4">Upcoming Assignments</h3>
      <ul>
        <li v-for="a in data.upcomingAssignments" :key="a.id">
          {{ a.title }} – due {{ new Date(a.due_date).toLocaleDateString() }}
        </li>
      </ul>
      <h3 class="font-semibold mt-4">Recent Submissions to Grade</h3>
      <ul>
        <li v-for="s in data.recentSubmissions" :key="s.id">
          {{ s.student.name }} – {{ s.assignment.title }} ({{ new Date(s.submitted_at).toLocaleString() }})
        </li>
      </ul>
    </div>

    <!-- Student -->
    <div v-if="isStudent">
      <h2 class="text-xl font-semibold">Upcoming Deadlines</h2>
      <ul>
        <li v-for="a in data.upcomingDeadlines" :key="a.id">
          {{ a.title }} – due {{ new Date(a.due_date).toLocaleDateString() }}
        </li>
      </ul>
      <h2 class="text-xl font-semibold mt-4">Recent Grades</h2>
      <ul>
        <li v-for="s in data.recentGrades" :key="s.id">
          {{ s.assignment?.title }} – {{ s.grade }}% ({{ s.assignment?.module?.name }})
        </li>
      </ul>
      <h2 class="text-xl font-semibold mt-4">Announcements</h2>
      <div v-for="a in data.announcements" :key="a.id" class="bg-white p-3 shadow rounded mt-2">
        <strong>{{ a.title }}</strong>
        <p>{{ a.body.substring(0, 100) }}...</p>
      </div>
    </div>
  </IntranetLayout>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';

const props = defineProps({
    // all possible props; they will be provided according to role
    totalStudents: Number, totalInstructors: Number, totalStaff: Number,
    pendingApprovals: Number, pendingLeaveRequests: Number,
    myModulesCount: Number, upcomingAssignments: Array, recentSubmissions: Array,
    upcomingDeadlines: Array, recentGrades: Array, announcements: Array,
    user: Object,
});

const isAdmin = computed(() => props.user.roles.some(r => r.name === 'admin'));
const isInstructor = computed(() => props.user.roles.some(r => r.name === 'instructor'));
const isStudent = computed(() => props.user.roles.some(r => r.name === 'student'));

// To simplify, we can just rely on the data passed; the controller sends only relevant fields.
// We'll conditionally display sections.
</script>