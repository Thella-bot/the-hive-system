<template>
  <HiveLayout :title="cohort.name" :description="`${cohort.department?.name} · ${cohort.academic_year?.name}`">
    <template #header-actions>
      <div class="flex items-center gap-3">
        <Link :href="route('hive.cohorts.index')" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
          ← Cohorts
        </Link>
        <Link v-if="can('edit-cohorts')" :href="route('hive.cohorts.edit', { cohort: cohort.id })"
          class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
          Edit
        </Link>
      </div>
    </template>

    <div class="space-y-6">

      <!-- Stats row -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
          <p class="text-3xl font-bold text-gray-900">{{ cohort.students?.length ?? 0 }}</p>
          <p class="text-xs text-gray-500 mt-1">Enrolled</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
          <p class="text-3xl font-bold text-gray-900">{{ cohort.max_students }}</p>
          <p class="text-xs text-gray-500 mt-1">Capacity</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
          <p class="text-3xl font-bold" :class="availableSpots > 0 ? 'text-green-600' : 'text-red-500'">
            {{ availableSpots }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Spots Left</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
          <Badge :color="cohort.is_active ? 'green' : 'gray'" class="text-sm">
            {{ cohort.is_active ? 'Active' : 'Closed' }}
          </Badge>
          <p class="text-xs text-gray-500 mt-2">Status</p>
        </div>
      </div>

      <!-- Capacity bar -->
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex justify-between text-sm font-medium text-gray-700 mb-2">
          <span>Enrolment Capacity</span>
          <span>{{ capacityPct }}%</span>
        </div>
        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all"
            :class="capacityPct >= 90 ? 'bg-red-500' : capacityPct >= 70 ? 'bg-amber-500' : 'bg-green-500'"
            :style="{ width: capacityPct + '%' }">
          </div>
        </div>
      </div>

      <!-- Students table -->
      <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Students ({{ cohort.students?.length ?? 0 }})</h2>
          <Link v-if="can('create-students')" :href="route('hive.users.create')"
            class="text-sm text-amber-600 hover:text-amber-700 font-medium">+ Enrol Student</Link>
        </div>
        <div class="divide-y divide-gray-50">
          <div v-if="!cohort.students?.length"
            class="px-6 py-12 text-center text-sm text-gray-400">
            No students enrolled in this cohort yet.
          </div>
          <div v-for="student in cohort.students" :key="student.id"
            class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 transition-colors">
            <img :src="student.user?.profile_photo_url" :alt="student.user?.name"
              class="w-9 h-9 rounded-full object-cover flex-shrink-0" />
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">{{ student.user?.name }}</p>
              <p class="text-xs text-gray-400">{{ student.student_number }}</p>
            </div>
            <Badge :color="student.status_color">{{ formatStatus(student.status) }}</Badge>
            <Link :href="route('hive.users.show', { user: student.user_id })"
              class="text-gray-400 hover:text-amber-600 transition-colors ml-2">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </Link>
          </div>
        </div>
      </div>

    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
  cohort: { type: Object, required: true },
})

const can = (p) => usePage().props.auth.user?.permissions?.includes(p) ?? false

const enrolled = computed(() => props.cohort.students?.length ?? 0)
const availableSpots = computed(() => Math.max(0, props.cohort.max_students - enrolled.value))
const capacityPct = computed(() => Math.min(100, Math.round((enrolled.value / props.cohort.max_students) * 100)))

const formatStatus = (s) => s?.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) ?? '—'
</script>