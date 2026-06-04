<template>
  <AppLayout title="Dashboard" :description="currentYear ? `Academic Year: ${currentYear.name}` : 'No active academic year set'">
    <template #header-actions>
      <span v-if="currentYear" class="inline-flex items-center gap-1.5 text-sm text-amber-600 font-medium">
        <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
        {{ currentYear.name }} in progress
      </span>
    </template>

    <!-- Welcome Section -->
    <div class="mb-8 animate-fade-in">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 p-6 rounded-xl bg-gradient-to-r from-amber-50 to-white border border-amber-100 shadow-sm animate-fade-in">
        <div>
          <h2 class="text-2xl font-bold text-amber-700 mb-1">Welcome back, {{ $page.props.auth.user.name }}!</h2>
          <p class="text-gray-600">Here's a quick look at your dashboard for today.</p>
        </div>
        <div class="flex items-center gap-3 mt-4 md:mt-0">
          <img :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" class="w-14 h-14 rounded-full object-cover border-2 border-amber-300 shadow" />
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8 animate-fade-in-slow">
      <StatCard label="Departments" :value="stats.departments" sub="Active departments" icon-bg="bg-amber-100">
        <template #icon>
          <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </template>
      </StatCard>

      <StatCard label="Active Cohorts" :value="stats.active_cohorts" sub="Currently running" icon-bg="bg-amber-100">
        <template #icon>
          <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </template>
      </StatCard>

      <StatCard label="Students" :value="stats.active_students" sub="Currently enrolled" icon-bg="bg-amber-100">
        <template #icon>
          <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
          </svg>
        </template>
      </StatCard>

      <StatCard label="Staff" :value="stats.staff" sub="Academic & non-academic" icon-bg="bg-amber-100">
        <template #icon>
          <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </template>
      </StatCard>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 animate-fade-in-delay">

      <!-- Recent Cohorts -->
      <div class="xl:col-span-2 bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Active Cohorts</h2>
          <Link :href="route('hive.cohorts.index')" class="text-sm text-amber-600 hover:text-amber-700 font-medium">
            View all →
          </Link>
        </div>
        <div class="divide-y divide-gray-50">
          <div v-if="recentCohorts.length === 0" class="px-6 py-10 text-center text-gray-400 text-sm">
            No cohorts yet. <Link :href="route('hive.cohorts.create')" class="text-amber-600 hover:underline">Create one.</Link>
          </div>
          <div v-for="cohort in recentCohorts" :key="cohort.id"
            class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
            <div
              class="w-2 h-10 rounded-full flex-shrink-0"
              :style="{ backgroundColor: cohort.department?.color ?? '#f59e0b' }"
            ></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">{{ cohort.name }}</p>
              <p class="text-xs text-gray-400">{{ cohort.department?.name }} · {{ cohort.academic_year?.name }}</p>
            </div>
            <div class="text-right flex-shrink-0">
              <p class="text-sm font-semibold text-gray-900">{{ cohort.students_count }}</p>
              <p class="text-xs text-gray-400">/ {{ cohort.max_students }}</p>
            </div>
            <Link :href="route('hive.cohorts.show', { cohort: cohort.id })"
              class="text-gray-400 hover:text-amber-600 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </Link>
          </div>
        </div>
      </div>

      <!-- Department Snapshot -->
      <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Departments</h2>
          <Link :href="route('hive.departments.index')" class="text-sm text-amber-600 hover:text-amber-700 font-medium">
            View all →
          </Link>
        </div>
        <div class="divide-y divide-gray-50">
          <div v-if="departmentSnapshot.length === 0" class="px-6 py-10 text-center text-gray-400 text-sm">
            No departments yet.
          </div>
          <div v-for="dept in departmentSnapshot" :key="dept.id"
            class="flex items-center gap-3 px-6 py-4 hover:bg-gray-50 transition-colors">
            <div
              class="w-3 h-3 rounded-full flex-shrink-0"
              :style="{ backgroundColor: dept.color }"
            ></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">{{ dept.name }}</p>
              <p class="text-xs text-gray-400">{{ dept.active_cohort_count }} cohorts · {{ dept.staff_count }} staff</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import '../../../resources/css/dashboard-animations.css';
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'

defineProps({
  stats:               { type: Object, required: true },
  currentYear:         { type: Object, default: null },
  recentCohorts:       { type: Array, default: () => [] },
  departmentSnapshot:  { type: Array, default: () => [] },
})
</script>