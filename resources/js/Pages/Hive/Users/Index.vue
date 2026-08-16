<script setup>
import { ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'
import UserRow from '@/Components/UserRow.vue'
import EmptyState from '@/Components/EmptyState.vue'
import {
  PlusIcon,
  EyeIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  users:   { type: Object, required: true, default: () => ({ data: [], links: [], meta: {} }) },
  roles:   { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({ }) },
})

const { auth } = usePage().props

const search = ref(props.filters.search ?? '')
const roleFilter = ref(props.filters.role ?? '')
let searchTimeout = null

const applyFilters = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get(route('hive.users.index'),
      { search: search.value, role: roleFilter.value },
      { preserveState: true, replace: true }
    )
  }, 300)
}

const clearFilters = () => {
  search.value = ''
  roleFilter.value = ''
  applyFilters()
}

const canDelete = () => {
  const roles = auth?.user?.roles ?? []
  return roles.some(r => ['super-admin', 'it-support'].includes(r))
}

const deleteUser = (user) => {
  if (!confirm(`Delete user "${user.name}"? This action cannot be undone.`)) return
  router.delete(route('hive.users.destroy', { user: user.id }))
}
</script>

<template>
  <HiveLayout title="Users" description="All staff, instructors and students">
    <template #header-actions>
      <Link :href="route('hive.users.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        New User
      </Link>
    </template>

    <!-- Search & filter bar -->
    <div class="flex items-center gap-3 mb-5 flex-wrap">
      <div class="relative flex-1 min-w-48 max-w-sm">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
        <input v-model="search" @input="applyFilters" type="text" placeholder="Search name or email..."
          class="w-full pl-9 pr-3.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" />
      </div>

      <select v-model="roleFilter" @change="applyFilters"
        class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
        <option value="">All Roles</option>
        <option v-for="role in roles" :key="role" :value="role">{{ role.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) }}</option>
      </select>

      <button v-if="search || roleFilter" @click="clearFilters"
        class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 underline">Clear</button>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">User</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Role</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Affiliation</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="users.data.length === 0">
            <td colspan="4" class="px-6 py-12 text-center">
              <EmptyState type="users" title="No users found" :description="search || roleFilter ? 'No users match your filters' : 'Get started by adding a user'">
                <template #action>
                  <div v-if="!search && !roleFilter">
                    <Link :href="route('hive.users.create')" class="text-amber-600 hover:text-amber-700">Add a user</Link>
                  </div>
                  <div v-else>
                    <button @click="clearFilters" class="text-amber-600 hover:text-amber-700">Clear filters</button>
                  </div>
                </template>
              </EmptyState>
            </td>
          </tr>
          <UserRow v-for="user in users.data" :key="user.id" :user="user">
            <template #role="{ user }">
              <div class="flex flex-wrap gap-1">
                <Badge v-for="role in user.roles" :key="role.id" :color="role.name === 'super-admin' ? 'purple' : role.name === 'it-support' ? 'indigo' : 'gray'">
                  {{ role.name.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) }}
                </Badge>
              </div>
            </template>
            <template #affiliation="{ user }">
              <span v-if="user.profile?.department">
                {{ user.profile.department.name }}
              </span>
              <span v-else-if="user.profile?.cohort">
                {{ user.profile.cohort.name }}
              </span>
              <span v-else class="text-gray-300 dark:text-gray-600">—</span>
            </template>
            <template #actions="{ user }">
              <Link :href="route('hive.users.show', { user: user.id })"
                class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 transition" title="View">
                <EyeIcon class="w-4 h-4" />
              </Link>
              <Link :href="route('hive.users.edit', { user: user.id })"
                class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 transition" title="Edit">
                <PencilSquareIcon class="w-4 h-4" />
              </Link>
              <button v-if="canDelete()" @click="deleteUser(user)"
                class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition" title="Delete">
                <TrashIcon class="w-4 h-4" />
              </button>
            </template>
          </UserRow>
        </tbody>
      </table>
    </div>

    <Pagination v-if="users.data.length > 0" :links="users.links" :meta="users.meta" />
  </HiveLayout>
</template>