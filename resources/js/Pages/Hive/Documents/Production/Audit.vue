<template>
  <HiveLayout title="Document Audit" description="Audit results for a single entity">
    <div class="space-y-6">
      <div class="flex items-center gap-4">
        <Link :href="route('documents.production.index')" class="text-sm text-gray-500 hover:text-amber-600 flex items-center gap-1">
          <ArrowLeftIcon class="w-4 h-4" />
          Back to Production
        </Link>
        <Link :href="route('documents.production.audit')" class="text-sm text-amber-600 hover:text-amber-700">Run Audit for All</Link>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Audit: {{ entityLabel }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Entity ID: {{ entityId }} &middot; Type: {{ entityType }}</p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Document Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Template</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Generated</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="item in audit" :key="item.type">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ item.label }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ item.template }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="item.is_generated" class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Generated</span>
                <span v-else class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Missing</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ item.generated_at ? formatDate(item.generated_at) : '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <button v-if="!item.is_generated" @click="generateOne(item.type)" class="text-amber-600 hover:text-amber-700">Generate</button>
                <span v-else class="text-gray-400">Done</span>
              </td>
            </tr>
            <tr v-if="!audit.length">
              <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No documents required for this entity.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
  audit: Array,
  entity: Object,
  entityType: String,
});

const entityLabel = computed(() => {
  if (!props.entity) return 'Unknown';
  if (props.entity.name) return props.entity.name;
  return 'Entity #' + props.entity.id;
});

const entityId = computed(() => props.entity?.id ?? 'N/A');

const generateOne = (type) => {
  useForm({
    document_type: type,
    entity_type: props.entityType,
    entity_id: props.entity.id,
  }).post(route('documents.production.generate'));
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString();
};
</script>
