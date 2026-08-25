<template>
  <HiveLayout title="Document Audit" description="Audit results for all entities">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link :href="route('documents.production.index')" class="text-sm text-gray-500 hover:text-amber-600 flex items-center gap-1">
            <ArrowLeftIcon class="w-4 h-4" />
            Back to Production
          </Link>
        </div>
        <button @click="batchGenerate" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Batch Generate Missing</button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Entities with Missing Docs</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalEntities }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Missing Documents</p>
          <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ totalMissing }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Entity Type</p>
          <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ entityTypeLabel }}</p>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Entity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Missing Documents</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="result in results" :key="result.entity_id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ result.entity_label }}</td>
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="doc in result.missing_documents" :key="doc.type" class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">{{ doc.label }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                  <Link :href="route('documents.production.audit', { entity_type: result.entity_type, entity_id: result.entity_id })" class="text-amber-600 hover:text-amber-700">View</Link>
                </td>
              </tr>
              <tr v-if="!results.length">
                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">All documents are generated for this entity type.</td>
              </tr>
            </tbody>
          </table>
        </div>
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
  results: Array,
  totalMissing: Number,
  totalEntities: Number,
  entityType: String,
  entityTypes: Object,
});

const entityTypeLabel = computed(() => {
  return props.entityTypes[props.entityType] || props.entityType;
});

const batchGenerate = () => {
  useForm({ entity_type: props.entityType }).post(route('documents.production.audit.batch'), {
    onSuccess: () => {},
  });
};
</script>
