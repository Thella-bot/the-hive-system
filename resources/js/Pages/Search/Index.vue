<template>
  <HiveLayout title="Search Results">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-2xl font-semibold text-gray-900">
        Search results for "{{ query }}"
      </h1>

      <div v-if="users.length" class="mt-6">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
          Users
        </h2>
        <ul class="mt-3 grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
          <li v-for="user in users" :key="user.id" class="col-span-1 flex flex-col text-center bg-white rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-200">
            <div class="flex-1 flex flex-col p-8">
              <img class="w-32 h-32 flex-shrink-0 mx-auto rounded-full" :src="user.profile_photo_url" :alt="user.name" />
              <h3 class="mt-6 text-gray-900 text-sm font-medium">{{ user.name }}</h3>
              <dl class="mt-1 flex-grow flex flex-col justify-between">
                <dt class="sr-only">Email</dt>
                <dd class="text-gray-500 text-sm">{{ user.email }}</dd>
              </dl>
            </div>
          </li>
        </ul>
      </div>

      <div v-else class="mt-6 text-center">
        <p class="text-lg text-gray-500">No results found.</p>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
  query: String,
  users: Array,
});
</script>
