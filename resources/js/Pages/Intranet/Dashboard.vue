<template>
  <IntranetLayout>
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
    <!-- Admin Dashboard -->
    <div v-if="isAdmin" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
      <div class="bg-white p-6 shadow-lg rounded-lg">
        <p class="text-4xl font-bold text-orange-600">{{ totalStudents }}</p>
        <p class="text-gray-500 mt-2">Students</p>
        <a href="#" class="text-orange-500 hover:underline mt-4 inline-block">View</a>
      </div>
      <div class="bg-white p-6 shadow-lg rounded-lg">
        <p class="text-4xl font-bold text-orange-600">{{ totalInstructors }}</p>
        <p class="text-gray-500 mt-2">Instructors</p>
        <a href="#" class="text-orange-500 hover:underline mt-4 inline-block">View</a>
      </div>
      <div class="bg-white p-6 shadow-lg rounded-lg">
        <p class="text-4xl font-bold text-orange-600">{{ totalStaff }}</p>
        <p class="text-gray-500 mt-2">Staff</p>
        <a href="#" class="text-orange-500 hover:underline mt-4 inline-block">View</a>
      </div>
      <div class="bg-blue-100 p-6 shadow-lg rounded-lg">
        <p class="text-4xl font-bold text-blue-600">{{ pendingApprovals }}</p>
        <p class="text-gray-500 mt-2">Pending Approvals</p>
        <a :href="route('hive.admin.approve-users')" class="text-blue-500 hover:underline mt-4 inline-block">Manage</a>
      </div>
      <div class="bg-yellow-100 p-6 shadow-lg rounded-lg">
        <p class="text-4xl font-bold text-yellow-600">{{ pendingLeaveRequests }}</p>
        <p class="text-gray-500 mt-2">Pending Leaves</p>
        <a href="#" class="text-yellow-500 hover:underline mt-4 inline-block">Manage</a>
      </div>
    </div>

    <!-- Instructor Dashboard -->
    <div v-if="isInstructor" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Left Column -->
      <div class="md:col-span-2 space-y-6">
        <!-- Upcoming Assignments -->
        <div class="bg-white p-6 shadow-lg rounded-lg">
          <h3 class="text-xl font-semibold text-gray-700">Upcoming Assignments</h3>
          <ul v-if="upcomingAssignments.length" class="mt-4 space-y-2">
            <li v-for="a in upcomingAssignments" :key="a.id" class="flex justify-between items-center">
              <span>{{ a.title }}</span>
              <span class="text-sm text-gray-500">Due: {{ new Date(a.due_date).toLocaleDateString() }}</span>
            </li>
          </ul>
          <p v-else class="text-gray-500 mt-4">No upcoming assignments.</p>
        </div>

        <!-- Recent Submissions to Grade -->
        <div class="bg-white p-6 shadow-lg rounded-lg">
          <h3 class="text-xl font-semibold text-gray-700">Recent Submissions to Grade</h3>
          <ul v-if="recentSubmissions.length" class="mt-4 space-y-2">
            <li v-for="s in recentSubmissions" :key="s.id" class="flex justify-between items-center">
              <div>
                <p>{{ s.student.name }} - {{ s.assignment.title }}</p>
                <p class="text-sm text-gray-500">Submitted: {{ new Date(s.submitted_at).toLocaleString() }}</p>
              </div>
              <a href="#" class="text-orange-500 hover:underline">Grade</a>
            </li>
          </ul>
          <p v-else class="text-gray-500 mt-4">No recent submissions to grade.</p>
        </div>
      </div>

      <!-- Right Column -->
      <div class="space-y-6">
        <!-- My Modules -->
        <div class="bg-white p-6 shadow-lg rounded-lg text-center">
          <p class="text-4xl font-bold text-orange-600">{{ myModulesCount }}</p>
          <p class="text-gray-500 mt-2">My Modules</p>
          <a href="#" class="text-orange-500 hover:underline mt-4 inline-block">View All</a>
        </div>
      </div>
    </div>

    <!-- Student Dashboard -->
    <div v-if="isStudent" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Left Column -->
      <div class="md:col-span-2 space-y-6">
        <!-- Upcoming Deadlines -->
        <div class="bg-white p-6 shadow-lg rounded-lg">
          <h3 class="text-xl font-semibold text-gray-700">Upcoming Deadlines</h3>
          <ul v-if="upcomingDeadlines.length" class="mt-4 space-y-2">
            <li v-for="a in upcomingDeadlines" :key="a.id" class="flex justify-between items-center">
              <span>{{ a.title }}</span>
              <span class="text-sm text-gray-500">Due: {{ new Date(a.due_date).toLocaleDateString() }}</span>
            </li>
          </ul>
          <p v-else class="text-gray-500 mt-4">No upcoming deadlines.</p>
        </div>

        <!-- Recent Grades -->
        <div class="bg-white p-6 shadow-lg rounded-lg">
          <h3 class="text-xl font-semibold text-gray-700">Recent Grades</h3>
          <ul v-if="recentGrades.length" class="mt-4 space-y-2">
            <li v-for="s in recentGrades" :key="s.id" class="flex justify-between items-center">
              <div>
                <p>{{ s.assignment?.title }} ({{ s.assignment?.module?.name }})</p>
              </div>
              <span class="font-semibold" :class="{ 'text-green-600': s.grade >= 50, 'text-red-600': s.grade < 50 }">{{ s.grade }}%</span>
            </li>
          </ul>
          <p v-else class="text-gray-500 mt-4">No recent grades.</p>
        </div>
      </div>

      <!-- Right Column -->
      <div class="space-y-6">
        <!-- Announcements -->
        <div class="bg-white p-6 shadow-lg rounded-lg">
          <h3 class="text-xl font-semibold text-gray-700">Announcements</h3>
          <div v-if="announcements.length" class="mt-4 space-y-4">
            <div v-for="a in announcements" :key="a.id" class="border-b pb-2">
              <h4 class="font-semibold">{{ a.title }}</h4>
              <p class="text-sm text-gray-600 mt-1">{{ a.body.substring(0, 100) }}...</p>
            </div>
          </div>
          <p v-else class="text-gray-500 mt-4">No announcements.</p>
        </div>
      </div>
    </div>
  </IntranetLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';

const props = defineProps({
    totalStudents: Number,
    totalInstructors: Number,
    totalStaff: Number,
    pendingApprovals: Number,
    pendingLeaveRequests: Number,
    myModulesCount: Number,
    upcomingAssignments: Array,
    recentSubmissions: Array,
    upcomingDeadlines: Array,
    recentGrades: Array,
    announcements: Array,
    user: Object,
});

const page = usePage();
const isAdmin = computed(() => page.props.user.roles.some(r => r.name === 'admin'));
const isInstructor = computed(() => page.props.user.roles.some(r => r.name === 'instructor'));
const isStudent = computed(() => page.props.user.roles.some(r => r.name === 'student'));
</script>