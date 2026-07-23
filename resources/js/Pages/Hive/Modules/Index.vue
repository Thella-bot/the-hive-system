<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useUser } from '@/composables/useUser';
import {
  MagnifyingGlassIcon,
  PlusIcon,
  PencilSquareIcon,
  EyeIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  modules: Array,
});

const { isAdmin, isStudent } = useUser();
const searchQuery = ref('');

const filteredModules = computed(() => {
  if (!searchQuery.value) return props.modules;
  const query = searchQuery.value.toLowerCase();
  return props.modules.filter(m =>
    m.name?.toLowerCase().includes(query) ||
    m.code?.toLowerCase().includes(query) ||
    m.department?.name?.toLowerCase().includes(query)
  );
});
</script>

<template>
  <HiveLayout title="Modules" description="A list of all the modules in the system.">
    <!-- Header with search and create button -->
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
      <!-- Search -->
      <div class="relative flex-1 max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
        </div>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search modules..."
          class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
        />
      </div>
      <Link v-if="isAdmin" :href="route('hive.modules.create')" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 active:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition">
        <PlusIcon class="h-4 w-4 mr-2" />
        Create Module
      </Link>
    </div>

    <!-- Results count -->
    <p v-if="searchQuery" class="text-sm text-gray-500 dark:text-gray-400 mb-4">
      Showing {{ filteredModules.length }} of {{ modules.length }} modules
    </p>

    <!-- Modules table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
      <table v-if="filteredModules.length > 0" class="w-full whitespace-nowrap">
        <thead class="bg-gray-50 dark:bg-gray-900/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Module</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Code</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Department</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Programmes</th>
            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="module in filteredModules" :key="module.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
            <td class="px-6 py-4">
              <Link :href="route('hive.modules.show', { module: module.id })" class="text-gray-900 dark:text-gray-100 font-medium hover:text-amber-600 dark:hover:text-amber-400">
                {{ module.name }}
              </Link>
            </td>
            <td class="px-6 py-4">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                {{ module.code }}
              </span>
            </td>
            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ module.department?.name || '—' }}</td>
            <td class="px-6 py-4">
              <div v-if="module.programmes?.length" class="flex flex-wrap gap-1">
                <span v-for="p in module.programmes" :key="p.id" class="inline-flex items-center px-2 py-0.5 bg-amber-50 text-amber-700 text-xs rounded-md dark:bg-amber-900/40 dark:text-amber-300">
                  {{ p.name }}
                </span>
              </div>
              <span v-else class="text-gray-400 dark:text-gray-500 text-sm">—</span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2">
                <Link :href="route('hive.modules.show', { module: module.id })" class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 transition" title="View">
                  <EyeIcon class="h-4 w-4" />
                </Link>
                <Link v-if="isAdmin" :href="route('hive.modules.edit', { module: module.id })" class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 transition" title="Edit">
                  <PencilSquareIcon class="h-4 w-4" />
                </Link>
                <Link v-if="isStudent" :href="route('hive.enrollment.destroy', { module: module.id })" method="delete" as="button" class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition" title="Deregister">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty state -->
      <div v-else class="p-12 text-center">
        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
          <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <p class="text-gray-500 dark:text-gray-400 mb-2">{{ searchQuery ? 'No modules match your search' : 'No modules found' }}</p>
        <p v-if="searchQuery" class="text-sm text-gray-400 dark:text-gray-500">Try adjusting your search terms</p>
        <p v-else-if="isAdmin" class="text-sm text-gray-400 dark:text-gray-500">
          <Link :href="route('hive.modules.create')" class="text-amber-600 hover:text-amber-700 dark:text-amber-400">Create a module</Link> to get started
        </p>
      </div>
    </div>
  </HiveLayout>
</template>