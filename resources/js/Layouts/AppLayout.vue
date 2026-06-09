<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden dark:bg-gray-900">

    <aside class="w-64 flex-shrink-0 flex flex-col bg-hbci-gray text-white dark:bg-gray-800">

      <!-- Brand -->
      <div class="p-4">
        <Link :href="route('hive.dashboard')">
          <img src="/images/hbci-logo-no-text.png" alt="The Hive" class="h-16 w-auto mx-auto" />
        </Link>
      </div>
      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-3">

        <NavItem :href="route('hive.dashboard')" active="hive.dashboard">
          <template #icon>
            <HomeIcon class="w-5 h-5" />
          </template>
          Dashboard
        </NavItem>

        <NavItem :href="route('hive.events.index')" active="hive.events.">
          <template #icon>
            <CalendarIcon class="w-5 h-5" />
          </template>
          Calendar
        </NavItem>

        <!-- Admissions -->
        <div v-if="can('view-applications')">
          <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            Admissions
          </p>

          <NavItem :href="route('hive.applications.index')" active="hive.applications.">
            <template #icon>
              <DocumentTextIcon class="w-5 h-5" />
            </template>
            Applications
          </NavItem>
        </div>

        <!-- School Management -->
        <div v-if="can('view-departments') || can('view-cohorts') || can('view-academic-years')">
          <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            School
          </p>

          <NavItem v-if="can('view-departments')"
            :href="route('hive.departments.index')"
            active="hive.departments.">
            <template #icon>
              <BuildingOfficeIcon class="w-5 h-5" />
            </template>
            Departments
          </NavItem>

          <NavItem v-if="can('view-programmes')"
                     :href="route('hive.programmes.index')"
                     active="hive.programmes.">
            <template #icon>
              <CodeBracketIcon class="w-5 h-5" />
            </template>
            Programmes
          </NavItem>


          <NavItem v-if="can('view-academic-years')"
            :href="route('hive.academic-years.index')"
            active="hive.academic-years.">
            <template #icon>
              <CalendarIcon class="w-5 h-5" />
            </template>
            Academic Years
          </NavItem>

          <NavItem v-if="can('view-cohorts')"
            :href="route('hive.cohorts.index')"
            active="hive.cohorts.">
            <template #icon>
              <UserGroupIcon class="w-5 h-5" />
            </template>
            Cohorts
          </NavItem>
        </div>

        <!-- People -->
        <div v-if="can('view-users')">
          <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            People
          </p>

          <NavItem :href="route('hive.users.index')" active="hive.users.">
            <template #icon>
              <UsersIcon class="w-5 h-5" />
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
              {{ $page.props.auth.user.role?.replace('-', ' ') ?? 'User' }}
            </p>
          </div>
          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="text-gray-400 hover:text-white transition-colors"
            title="Logout"
          >
            <ArrowRightOnRectangleIcon class="w-5 h-5" />
          </Link>
        </div>
      </div>
    </aside>

    <!-- Main content area -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <!-- Top bar -->
      <header class="flex-shrink-0 bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center gap-4">
          <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ title }}</h1>
            <p v-if="description" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ description }}</p>
          </div>
          <SearchInput />
        </div>
        <div class="flex items-center gap-3">
          <ThemeToggle />
          <slot name="header-actions" />
        </div>
      </header>

      <!-- Flash messages -->
      <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="px-8 pt-4">
        <div v-if="$page.props.flash?.success"
          class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/30 dark:border-green-800 dark:text-green-200 rounded-lg px-4 py-3 text-sm">
          <CheckCircleIcon class="w-5 h-5 text-green-500 flex-shrink-0" />
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error"
          class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/30 dark:border-red-800 dark:text-red-200 rounded-lg px-4 py-3 text-sm">
          <ExclamationCircleIcon class="w-5 h-5 text-red-500 flex-shrink-0" />
          {{ $page.props.flash.error }}
        </div>
      </div>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto px-8 py-6 dark:bg-gray-900">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import NavItem from '@/Components/NavItem.vue'
import SearchInput from '@/Components/SearchInput.vue'
import ThemeToggle from '@/Components/ThemeToggle.vue'
import { usePermissions } from '@/composables/usePermissions'
import {
  ArrowRightOnRectangleIcon,
  BuildingOfficeIcon,
  CalendarIcon,
  CheckCircleIcon,
  CodeBracketIcon,
  DocumentTextIcon,
  ExclamationCircleIcon,
  HomeIcon,
  UserGroupIcon,
  UsersIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
})

const { can } = usePermissions()
</script>
