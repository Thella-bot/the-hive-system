<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 flex flex-col bg-gray-900 text-white">

      <!-- Brand -->
      <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-700">
        <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 3C8 3 4 6 4 10c0 2.5 1.5 4.8 3.8 6.2L9 21h6l1.2-4.8C18.5 14.8 20 12.5 20 10c0-4-4-7-8-7z"/>
          </svg>
        </div>
        <div>
          <p class="font-bold text-sm leading-none">The Hive</p>
          <p class="text-xs text-gray-400 mt-0.5">Culinary Institute</p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-3">

        <NavItem :href="route('dashboard')" :active="isActive('dashboard')">
          <template #icon>
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
          </template>
          Dashboard
        </NavItem>

        <!-- School Management -->
        <div v-if="can('view-departments') || can('view-cohorts') || can('view-academic-years')">
          <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            School
          </p>

          <NavItem v-if="can('view-departments')"
            :href="route('departments.index')"
            :active="isActive('departments')">
            <template #icon>
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
            </template>
            Departments
          </NavItem>

          <NavItem v-if="can('view-academic-years')"
            :href="route('academic-years.index')"
            :active="isActive('academic-years')">
            <template #icon>
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </template>
            Academic Years
          </NavItem>

          <NavItem v-if="can('view-cohorts')"
            :href="route('cohorts.index')"
            :active="isActive('cohorts')">
            <template #icon>
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </template>
            Cohorts
          </NavItem>
        </div>

        <!-- People -->
        <div v-if="can('view-users')">
          <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            People
          </p>

          <NavItem :href="route('users.index')" :active="isActive('users')">
            <template #icon>
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </template>
            All Users
          </NavItem>
        </div>

      </nav>

      <!-- User menu at bottom -->
      <div class="border-t border-gray-700 p-4">
        <div class="flex items-center gap-3">
          <img
            :src="$page.props.auth.user.profile_photo_url"
            :alt="$page.props.auth.user.name"
            class="w-8 h-8 rounded-full object-cover flex-shrink-0"
          />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate">{{ $page.props.auth.user.name }}</p>
            <p class="text-xs text-gray-400 truncate capitalize">
              {{ $page.props.auth.user.roles[0]?.replace('-', ' ') ?? 'User' }}
            </p>
          </div>
          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="text-gray-400 hover:text-white transition-colors"
            title="Logout"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </Link>
        </div>
      </div>
    </aside>

    <!-- Main content area -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <!-- Top bar -->
      <header class="flex-shrink-0 bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold text-gray-900">{{ title }}</h1>
          <p v-if="description" class="text-sm text-gray-500 mt-0.5">{{ description }}</p>
        </div>
        <slot name="header-actions" />
      </header>

      <!-- Flash messages -->
      <div v-if="$page.props.flash.success || $page.props.flash.error" class="px-8 pt-4">
        <div v-if="$page.props.flash.success"
          class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
          <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash.error"
          class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
          <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          {{ $page.props.flash.error }}
        </div>
      </div>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto px-8 py-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import NavItem from '@/Components/NavItem.vue'

defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
})

const page = usePage()

const can = (permission) => {
  return page.props.auth.user?.permissions?.includes(permission) ?? false
}

const isActive = (name) => {
  return route().current(name + '*')
}
</script>
