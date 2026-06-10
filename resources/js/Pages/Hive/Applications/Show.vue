<template>
  <HiveLayout :title="`Application from ${applicantName}`">
    <template #header-actions>
      <Link :href="route('hive.applications.index')" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
        ← Applications
      </Link>
    </template>
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
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Admitted</h3>
              <p class="text-gray-900">{{ application.admitted_at ? new Date(application.admitted_at).toLocaleDateString() : '—' }}</p>
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Registration</h3>
              <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full" :class="registrationStatusClass(application.registration_status)">
                {{ application.registration_status || 'pending' }}
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

        <!-- Attachments -->
        <div v-if="application.attachments && application.attachments.length" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Supporting Documents</h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="(attachment, index) in application.attachments" :key="index"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ getAttachmentLabel(attachment.type) }}</p>
                    <p class="text-xs text-gray-500">{{ attachment.name }} · {{ formatFileSize(attachment.size) }}</p>
                  </div>
                </div>
                <a :href="'/storage/' + attachment.path" target="_blank"
                  class="px-3 py-1.5 bg-amber-50 text-amber-700 text-xs font-medium rounded-lg hover:bg-amber-100 transition">
                  View
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Registration Status (for admitted) -->
      <div v-if="application.admitted_at" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-900">Registration</h2>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Status</h3>
              <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full" :class="registrationStatusClass(application.registration_status)">
                {{ application.registration_status || 'pending' }}
              </span>
            </div>
            <div v-if="application.registered_at">
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Completed On</h3>
              <p class="text-gray-900">{{ new Date(application.registered_at).toLocaleDateString() }}</p>
            </div>
            <div v-if="application.payment_verified_at">
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Payment Verified</h3>
              <p class="text-gray-900">{{ new Date(application.payment_verified_at).toLocaleDateString() }}</p>
            </div>
          </div>

          <!-- Payment proof -->
          <div v-if="application.payment_proof_path" class="p-4 bg-gray-50 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Payment Proof</h3>
            <a :href="'/storage/' + application.payment_proof_path" target="_blank"
              class="inline-flex items-center gap-2 text-sm text-amber-600 hover:text-amber-700 font-medium">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              View Payment Proof
            </a>
          </div>

          <!-- Admin complete registration -->
          <div v-if="canUpdate && application.registration_status !== 'completed'" class="pt-4 border-t border-gray-100">
            <p class="text-sm text-gray-500 mb-3">Once payment proof is verified, complete the registration to grant full student access.</p>
            <button @click="completeRegistration"
              class="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition">
              Complete Registration
            </button>
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
import { useForm, router, Link } from '@inertiajs/vue3';

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
  form.patch(route('hive.applications.update', { application: props.application.id }));
};

const completeRegistration = () => {
  if (confirm('Complete registration for this student? They will gain full access to modules.')) {
    router.post(route('hive.applications.complete-registration', { application: props.application.id }));
  }
};

const statusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-yellow-100 text-yellow-800';
    case 'approved': return 'bg-green-100 text-green-800';
    case 'rejected': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};

const registrationStatusClass = (status) => {
  switch (status) {
    case 'completed': return 'bg-emerald-100 text-emerald-800';
    case 'submitted': return 'bg-blue-100 text-blue-800';
    case 'pending': return 'bg-gray-100 text-gray-600';
    default: return 'bg-gray-100 text-gray-600';
  }
};

const getAttachmentLabel = (type) => {
  const labels = {
    certificate: 'Certificate / Transcripts',
    employer_letter: 'Employer Reference Letter',
    id_document: 'ID Document',
    additional: 'Additional Document',
  };
  return labels[type] || type;
};

const formatFileSize = (bytes) => {
  if (!bytes) return 'Unknown size';
  const kb = bytes / 1024;
  if (kb < 1024) {
    return kb.toFixed(1) + ' KB';
  }
  return (kb / 1024).toFixed(1) + ' MB';
};
</script>
