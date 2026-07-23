<template>
  <HiveLayout :title="programme.name" :description="`Details for ${programme.name}`">
    <div class="bg-white rounded-xl border border-gray-200 dark:border-gray-700 p-6">
      <div class="flex justify-between items-start">
        <div>
          <h2 class="text-2xl font-bold text-gray-800">{{ programme.name }}</h2>
          <p class="text-gray-600 mt-2">{{ programme.description }}</p>
          <p class="text-sm text-gray-500 mt-4">Duration: {{ programme.duration }}</p>
        </div>
        <Link v-if="isAdmin" :href="route('hive.programmes.edit', { programme: programme.id })" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 active:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition">
          Manage Courses
        </Link>
      </div>

      <div class="mt-8">
        <h3 class="text-xl font-semibold text-gray-700">Courses in this Programme</h3>
        <div v-if="programme.modules.length" class="mt-4 border-t border-gray-200">
          <ul class="divide-y divide-gray-200">
            <li v-for="module in programme.modules" :key="module.id" class="py-4 flex justify-between items-center">
              <div>
                <p class="font-medium text-gray-900">{{ module.name }} ({{ module.code }})</p>
                <p class="text-sm text-gray-500">{{ module.description }}</p>
              </div>
            </li>
          </ul>
        </div>
        <p v-else class="text-gray-500 mt-4">No courses have been assigned to this programme yet.</p>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useUser } from '@/composables/useUser';

const props = defineProps({
  programme: Object,
});

const { isAdmin } = useUser();
</script>