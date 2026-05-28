<template>
  <HiveLayout title="Document Repository" description="Academic materials, recipes, schedules, and institute resources.">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">Document Repository</h1>
      <Link v-if="canCreate" :href="route('hive.documents.create')"
            class="bg-indigo-600 text-white px-4 py-2 rounded">Upload New</Link>
    </div>
    <div class="grid gap-4">
      <div v-for="doc in documents" :key="doc.id"
           class="bg-white p-4 shadow rounded flex justify-between">
        <div>
          <h2 class="font-semibold">{{ doc.title }}</h2>
          <p class="text-sm text-gray-600">{{ doc.description?.substring(0,80) }}</p>
          <p class="text-xs">Category: {{ doc.category }} | Version {{ doc.latest_version?.version_number }}</p>
        </div>
        <div class="space-x-2">
          <a v-if="doc.latest_version"
             :href="route('hive.documents.version.download', doc.latest_version.id)"
             class="text-blue-600 underline">Download</a>
          <Link :href="route('hive.documents.show', doc.id)" class="text-indigo-600">Details</Link>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>
<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({ documents: Array, categories: Array });
const canCreate = computed(() => usePage().props.auth.user?.roles.some(r => ['super-admin','school-admin', 'academic_staff', 'non_academic_staff'].includes(r)));
</script>
