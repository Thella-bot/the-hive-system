<template>
  <HiveLayout title="Document Production" description="Generate and audit institutional documents">
    <div class="space-y-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Generate</h2>
            <form @submit.prevent="generate" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Document Type</label>
                  <select v-model="form.document_type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option v-for="(label, value) in documentTypes" :key="value" :value="value">{{ label }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Entity Type</label>
                  <select v-model="form.entity_type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="App\Models\User">Students / Staff</option>
                    <option value="App\Models\Application">Applications</option>
                    <option value="App\Models\Payment">Payments</option>
                    <option value="App\Models\Invoice">Invoices</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Entity ID</label>
                  <input v-model="form.entity_id" type="number" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="e.g. 1">
                </div>
              </div>
              <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">Generate Document</button>
            </form>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Audit Documents</h2>
              <button @click="showAuditModal = true" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">Run Audit</button>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Audit identifies missing documents across students, staff, applications, payments, and invoices.</p>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Documents</h2>
          <div class="space-y-3">
            <div v-for="doc in recent" :key="doc.id" class="flex items-start justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ doc.document_type }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ doc.generator?.name || 'System' }}</p>
              </div>
              <span class="text-xs text-gray-400">{{ formatDate(doc.generated_at) }}</span>
            </div>
            <div v-if="!recent.length" class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No documents generated yet.</div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showAuditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Audit Missing Documents</h3>
            <button @click="showAuditModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
          </div>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Entity Type</label>
            <select v-model="auditEntityType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
              <option value="App\Models\User">Students / Staff</option>
              <option value="App\Models\Application">Applications</option>
              <option value="App\Models\Payment">Payments</option>
              <option value="App\Models\Invoice">Invoices</option>
            </select>
          </div>
          <div class="flex gap-3">
            <Link :href="route('documents.production.audit', { entity_type: auditEntityType })" class="flex-1 text-center px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">View Audit Report</Link>
            <button @click="batchGenerate" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Batch Generate Missing</button>
          </div>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
  documentTypes: Object,
  recent: Array,
});

const form = useForm({
  document_type: '',
  entity_type: 'App\Models\User',
  entity_id: '',
});

const showAuditModal = ref(false);
const auditEntityType = ref('App\Models\User');

const generate = () => {
  form.post(route('documents.production.generate'), {
    onSuccess: () => {
      form.reset();
    },
  });
};

const batchGenerate = () => {
  useForm({ entity_type: auditEntityType.value }).post(route('documents.production.audit.batch'), {
    onSuccess: () => {
      showAuditModal.value = false;
    },
  });
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString();
};
</script>
