<template>
  <HiveLayout :title="adminList ? 'Registrations' : 'Registration'" :description="adminList ? 'Manage student registrations' : 'Complete your enrollment registration.'">
    <div class="max-w-2xl mx-auto space-y-6">

      <!-- Admin List View -->
      <div v-if="adminList" class="space-y-6">
        <!-- Filters -->
        <div class="bg-white rounded-xl border border-gray-200 p-4">
          <div class="flex items-center gap-4">
            <button v-for="f in ['pending', 'submitted', 'completed', 'all']" :key="f"
              @click="$inertia.get(route('hive.registration.index', { filter: f }))"
              class="px-4 py-2 rounded-lg text-sm font-medium transition"
              :class="filter === f ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
              {{ f === 'all' ? 'All' : f.charAt(0).toUpperCase() + f.slice(1) }}
            </button>
          </div>
        </div>

        <!-- Registrations Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase tracking-wide">Student</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase tracking-wide">Programme</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase tracking-wide">Admitted</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="reg in adminList.data" :key="reg.id">
                <td class="px-6 py-4">
                  <p class="font-medium text-gray-900">{{ reg.name || reg.user?.name }}</p>
                  <p class="text-gray-500 text-xs">{{ reg.email || reg.user?.email }}</p>
                </td>
                <td class="px-6 py-4 text-gray-700">{{ reg.programme?.name }}</td>
                <td class="px-6 py-4 text-gray-500 text-xs">{{ reg.admitted_at ? new Date(reg.admitted_at).toLocaleDateString() : '—' }}</td>
                <td class="px-6 py-4">
                  <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full" :class="registrationStatusClass(reg.registration_status)">
                    {{ reg.registration_status || 'pending' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <Link :href="route('hive.applications.show', { application: reg.id })"
                      class="text-amber-600 hover:text-amber-700 font-medium text-sm">
                      View
                    </Link>
                    <template v-if="canManageRegistration">
                      <button v-if="reg.registration_status === 'submitted'"
                        @click="completeRegistration(reg)"
                        class="text-emerald-600 hover:text-emerald-700 font-medium text-sm">
                        Approve
                      </button>
                      <button v-if="reg.registration_status !== 'rejected' && reg.registration_status !== 'completed'"
                        @click="rejectRegistration(reg)"
                        class="text-red-600 hover:text-red-700 font-medium text-sm">
                        Reject
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
              <tr v-if="adminList.data.length === 0">
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No registrations found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Student View -->
      <template v-else>
        <!-- Not admitted -->
        <div v-if="!application" class="bg-white rounded-xl border border-gray-200 p-8 text-center">
          <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h2 class="text-xl font-bold text-gray-900 mb-2">Registration Not Available</h2>
          <p class="text-gray-500">You do not have an admitted application. Please complete your application process first.</p>
        </div>

        <!-- Registration completed -->
        <div v-else-if="registration?.status === 'completed'" class="bg-white rounded-xl border border-gray-200 p-8 text-center">
          <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <h2 class="text-xl font-bold text-gray-900 mb-2">Registration Complete!</h2>
          <p class="text-gray-500 mb-4">You have completed your registration. You now have full access to the Hive.</p>
          <Link :href="route('hive.dashboard')" class="inline-flex items-center px-6 py-3 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition">
            Go to Dashboard
          </Link>
        </div>

        <!-- Registration submitted (awaiting verification) -->
        <div v-else-if="registration?.status === 'submitted'" class="bg-white rounded-xl border border-gray-200 p-8 text-center">
          <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h2 class="text-xl font-bold text-gray-900 mb-2">Registration Under Review</h2>
          <p class="text-gray-500 mb-4">Your registration documents and payment proof have been submitted. Our team is verifying your information.</p>
          <p class="text-sm text-gray-400">You will receive an email once your registration is approved.</p>
        </div>

        <!-- Registration pending - show form -->
        <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Complete Your Registration</h2>
            <p class="text-sm text-gray-500 mt-1">Upload your proof of payment to complete the registration process.</p>
          </div>
          <div class="p-6">
            <!-- Application summary -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Your Programme</h3>
              <p class="font-medium text-gray-900">{{ application?.programme?.name }}</p>
              <p v-if="application?.variant" class="text-sm text-amber-600">{{ application?.variant?.label }}</p>
            </div>

            <!-- Success -->
            <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
              <p class="text-emerald-700 text-sm">{{ $page.props.flash.success }}</p>
            </div>

            <!-- Upload form -->
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Proof of Payment *</label>
                <input type="file" ref="fileInput" accept=".pdf,.jpg,.jpeg,.png"
                  class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                  :class="form.errors.payment_proof ? 'border-red-400' : ''"
                  @change="form.payment_proof = $event.target.files[0]" />
                <div v-if="form.errors.payment_proof" class="text-red-600 text-xs mt-1">{{ form.errors.payment_proof }}</div>
                <p class="text-xs text-gray-500 mt-1">Accepted: PDF, JPG, PNG (max 5MB)</p>
              </div>

              <div class="flex justify-end">
                <button type="submit" :disabled="form.processing"
                  class="px-6 py-2.5 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition disabled:opacity-50">
                  {{ form.processing ? 'Submitting...' : 'Submit Registration' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </template>
    </div>
  </HiveLayout>
</template>

<script setup>
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { Link, useForm, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  application: Object,
  registration: Object,
  adminList: Object,
  filter: {
    type: String,
    default: 'pending',
  },
});

const form = useForm({
  payment_proof: null,
});

// Non-academic staff can manage registrations, but not academic_staff
const canManageRegistration = computed(() => {
  const { props: pageProps } = usePage();
  const roles = pageProps.auth?.user?.roles || [];
  return roles.includes('super-admin') || roles.includes('school-admin') || roles.includes('non_academic_staff');
});

const submit = () => {
  form.post(route('hive.registration.store'), {
    preserveScroll: true,
  });
};

const completeRegistration = (reg) => {
  if (!confirm(`Approve registration for ${reg.name || reg.user?.name}?`)) return;
  router.patch(route('hive.registration.update', { registration: reg.id }), {
    registration_status: 'completed',
  });
};

const rejectRegistration = (reg) => {
  if (!confirm(`Reject registration for ${reg.name || reg.user?.name}?`)) return;
  router.patch(route('hive.registration.update', { registration: reg.id }), {
    registration_status: 'rejected',
  });
};

const registrationStatusClass = (status) => {
  switch (status) {
    case 'completed': return 'bg-emerald-100 text-emerald-800';
    case 'submitted': return 'bg-blue-100 text-blue-800';
    case 'pending': return 'bg-gray-100 text-gray-600';
    case 'rejected': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-600';
  }
};
</script>