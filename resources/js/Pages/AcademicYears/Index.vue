<template>
  <AppLayout title="Academic Years" description="Define and manage school year periods">
    <template #header-actions>
      <Link v-if="can('create-academic-years')" :href="route('academic-years.create')"
        class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Academic Year
      </Link>
    </template>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Period</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Cohorts</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="years.data.length === 0">
            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
              No academic years defined yet.
              <Link :href="route('academic-years.create')" class="text-amber-600 hover:underline ml-1">Create one.</Link>
            </td>
          </tr>
          <tr v-for="year in years.data" :key="year.id"
            class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-900">{{ year.name }}</span>
                <Badge v-if="year.is_current" color="amber">Current</Badge>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-500">
              {{ formatDate(year.start_date) }} → {{ formatDate(year.end_date) }}
            </td>
            <td class="px-6 py-4 text-gray-700 font-medium">{{ year.cohorts_count }}</td>
            <td class="px-6 py-4">
              <Badge :color="year.is_ongoing ? 'green' : 'gray'">
                {{ year.is_ongoing ? 'Ongoing' : 'Completed' }}
              </Badge>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center justify-end gap-3">
                <Link v-if="can('edit-academic-years')" :href="route('academic-years.edit', year.id)"
                  class="text-gray-400 hover:text-amber-600 font-medium text-xs transition-colors">Edit</Link>
                <button v-if="can('delete-academic-years') && year.cohorts_count === 0"
                  @click="deleteYear(year)"
                  class="text-gray-400 hover:text-red-500 font-medium text-xs transition-colors">Delete</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination v-if="years.data.length > 0" :links="years.links" :meta="years.meta" />
  </AppLayout>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'

defineProps({
  years: { type: Object, required: true },
})

const can = (p) => usePage().props.auth.user?.permissions?.includes(p) ?? false

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—'

const deleteYear = (year) => {
  if (confirm(`Delete "${year.name}"? This cannot be undone.`)) {
    router.delete(route('academic-years.destroy', year.id))
  }
}
</script>
