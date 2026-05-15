<template>
  <AppLayout title="Users" description="All staff, instructors and students">
    <template #header-actions>
      <Link v-if="can('create-users')" :href="route('users.create')"
        class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New User
      </Link>
    </template>

    <!-- Search & filter bar -->
    <div class="flex items-center gap-3 mb-5 flex-wrap">
      <div class="relative flex-1 min-w-48 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="search" @input="applyFilters" type="text" placeholder="Search name or email…"
          class="w-full pl-9 pr-3.5 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" />
      </div>

      <select v-model="roleFilter" @change="applyFilters"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
        <option value="">All Roles</option>
        <option v-for="role in roles" :key="role" :value="role">{{ formatRole(role) }}</option>
      </select>

      <button v-if="search || roleFilter" @click="clearFilters"
        class="text-sm text-gray-500 hover:text-gray-700 underline">Clear</button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">User</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Role</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Affiliation</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="users.data.length === 0">
            <td colspan="4" class="px-6 py-12 text-center text-gray-400">No users found.</td>
          </tr>
          <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <img :src="user.profile_photo_url" :alt="user.name"
                  class="w-9 h-9 rounded-full object-cover flex-shrink-0" />
                <div>
                  <p class="font-medium text-gray-900">{{ user.name }}</p>
                  <p class="text-xs text-gray-400">{{ user.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-1">
                <Badge v-for="role in user.roles" :key="role.id" :color="roleColor(role.name)">
                  {{ formatRole(role.name) }}
                </Badge>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-500 hidden md:table-cell">
              <span v-if="user.staff_profile?.department">
                {{ user.staff_profile.department.name }}
              </span>
              <span v-else-if="user.student_profile?.cohort">
                {{ user.student_profile.cohort.name }}
              </span>
              <span v-else class="text-gray-300">—</span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center justify-end gap-3">
                <Link :href="route('users.show', user.id)"
                  class="text-amber-600 hover:text-amber-700 font-medium text-xs">View</Link>
                <Link v-if="can('edit-users')" :href="route('users.edit', user.id)"
                  class="text-gray-500 hover:text-gray-700 font-medium text-xs">Edit</Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination v-if="users.data.length > 0" :links="users.links" :meta="users.meta" />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  users:   { type: Object, required: true },
  roles:   { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const can = (p) => usePage().props.auth.user?.permissions?.includes(p) ?? false

const search = ref(props.filters.search ?? '')
const roleFilter = ref(props.filters.role ?? '')

const applyFilters = () => router.get(route('users.index'),
  { search: search.value, role: roleFilter.value },
  { preserveState: true, replace: true }
)

const clearFilters = () => {
  search.value = ''
  roleFilter.value = ''
  applyFilters()
}

const formatRole = (r) => r.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())

const roleColor = (r) => ({
  'super-admin': 'purple',
  'school-admin': 'blue',
  'department-head': 'amber',
  'chef-instructor': 'orange',
  'student': 'green',
}[r] ?? 'gray')
</script>
