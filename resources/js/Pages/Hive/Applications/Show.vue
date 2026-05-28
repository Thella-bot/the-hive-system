<template>
  <HiveLayout :title="`Application from ${applicantName}`">
    <div class="max-w-3xl mx-auto space-y-6">
      <!-- Application Details -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-900">Application Details</h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Applicant</h3>
              <p class="font-medium text-gray-900">{{ applicantName }}</p>
              <p class="text-sm text-gray-500">{{ applicantEmail }}</p>
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Programme</h3>
              <p class="font-medium text-gray-900">{{ application.programme?.name }}</p>
              <p v-if="application.variant" class="text-sm text-amber-600 mt-1">
                {{ application.variant.label }} · {{ application.variant.duration }}
              </p>
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Phone</h3>
              <p class="text-gray-900">{{ application.phone || '—' }}</p>
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Status</h3>
              <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full" :class="statusClass(application.status)">
                {{ application.status }}
              </span>
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Applied On</h3>
              <p class="text-gray-900">{{ new Date(application.created_at).toLocaleDateString() }}</p>
            </div>
            <div v-if="application.notes">
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Notes</h3>
              <p class="text-gray-700 text-sm">{{ application.notes }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Update Status -->
      <div v-if="canUpdate" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-900">Update Status</h2>
        </div>
        <div class="p-6">
          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
              <select id="status" v-model="form.status"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
            <div>
              <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">Internal Notes</label>
              <textarea id="notes" v-model="form.notes" rows="3"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                placeholder="Add internal notes about this application..."></textarea>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition disabled:opacity-50"
                :disabled="form.processing">
                Update Application
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  application: Object,
  canUpdate: {
    type: Boolean,
    default: false,
  },
});

const form = useForm({
  status: props.application.status,
  notes: props.application.notes,
});

const applicantName = computed(() => props.application.user?.name || props.application.name || 'Applicant');
const applicantEmail = computed(() => props.application.user?.email || props.application.email || 'No email recorded');

const submit = () => {
  form.patch(route('hive.applications.update', props.application.id));
};

const statusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-yellow-100 text-yellow-800';
    case 'approved': return 'bg-green-100 text-green-800';
    case 'rejected': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};
</script>
