<template>
  <PublicLayout>
    <!-- Hero -->
    <section class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white py-16 lg:py-20 overflow-hidden">
      <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>
      <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-3xl translate-x-1/3 -translate-y-1/3"></div>
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-amber-600/10 rounded-full blur-3xl -translate-x-1/3 translate-y-1/3"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-amber-400/5 rounded-full blur-3xl"></div>

      <!-- Decorative elements -->
      <div class="absolute top-20 left-10 w-16 h-16 border border-amber-500/20 rounded-full"></div>
      <div class="absolute bottom-32 right-16 w-10 h-10 border border-amber-500/10 rounded-full"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-amber-600/20 border border-amber-500/30 rounded-full text-amber-400 text-sm font-medium mb-6">
          <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
          Free Application
        </span>
        <h1 class="text-4xl lg:text-5xl font-bold">Apply Now</h1>
        <p class="mt-4 text-lg text-gray-300 max-w-2xl mx-auto">Start your journey to becoming a culinary professional. Complete the form below — it's free and takes only a few minutes.</p>
      </div>
    </section>

    <!-- Application Form -->
    <section class="py-16 lg:py-20 bg-gray-50 dark:bg-gray-800 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/5 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 left-0 w-72 h-72 bg-amber-500/5 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success -->
        <div v-if="$page.props.flash?.success" class="mb-8 p-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/40 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-emerald-800 dark:text-emerald-300">Application Submitted!</h3>
              <p class="text-emerald-700 dark:text-emerald-400 text-sm mt-1">{{ $page.props.flash.success }}</p>
            </div>
          </div>
        </div>

        <!-- Error -->
        <div v-if="$page.props.flash?.error" class="mb-8 p-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-red-800 dark:text-red-300">{{ $page.props.flash.error.title || 'Submission Failed' }}</h3>
              <p class="text-red-700 dark:text-red-400 text-sm mt-1">{{ $page.props.flash.error.message || $page.props.flash.error }}</p>
            </div>
          </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-8 lg:p-10 relative">
          <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-500 to-amber-600 rounded-t-2xl"></div>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Personal Info -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Personal Information</h3>
              <div class="space-y-5">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Full Name *</label>
                  <input v-model="form.name" type="text" id="name" required
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                    :class="form.errors.name ? 'border-red-400' : ''"
                    placeholder="Enter your full name" />
                  <div v-if="form.errors.name" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.name }}</div>
                </div>
                <div class="grid sm:grid-cols-2 gap-5">
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email Address *</label>
                    <input v-model="form.email" type="email" id="email" required
                      class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                      :class="form.errors.email ? 'border-red-400' : ''"
                      placeholder="your@email.com" />
                    <div v-if="form.errors.email" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.email }}</div>
                  </div>
                  <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Phone Number</label>
                    <input v-model="form.phone" type="tel" id="phone"
                      class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                      placeholder="+266 ..." />
                    <div v-if="form.errors.phone" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.phone }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Programme Selection -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Programme Choice</h3>
              <div class="space-y-4">
                <!-- Programme Select -->
                <div>
                  <label for="programme_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Programme *</label>
                  <select v-model="form.programme_id" id="programme_id" required
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                    @change="onProgrammeChange">
                    <option value="" disabled>Select a programme</option>
                    <option v-for="p in programmes" :key="p.id" :value="p.id">
                      {{ p.name }}
                    </option>
                  </select>
                  <div v-if="form.errors.programme_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.programme_id }}</div>
                </div>

                <!-- Programme Info Banner -->
                <div v-if="currentProgramme" class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl">
                  <p class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-1">{{ currentProgramme.name }}</p>
                  <p class="text-xs text-amber-700 dark:text-amber-400">{{ currentProgramme.description }}</p>
                  <div v-if="currentProgramme.department" class="mt-2 flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    {{ currentProgramme.department.name }}
                  </div>
                </div>

                <!-- Variant Select (if programme has variants) -->
                <div v-if="currentVariants.length">
                  <label for="variant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Payment Option *</label>
                  <div class="space-y-2">
                    <label v-for="variant in currentVariants" :key="variant.id"
                      class="flex items-center justify-between p-4 rounded-xl border cursor-pointer transition"
                      :class="form.variant_id === variant.id
                        ? 'border-amber-400 bg-amber-50 dark:bg-amber-900/20'
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800'">
                      <div class="flex items-center gap-3">
                        <input type="radio" :value="variant.id" v-model="form.variant_id" class="w-4 h-4 text-amber-600" />
                        <div>
                          <p class="font-medium text-gray-900 dark:text-white">{{ variant.label }}</p>
                          <p class="text-sm text-gray-500 dark:text-gray-400">{{ variant.label === 'Monthly Installments' ? `LSL ${parseFloat(variant.monthly_fee).toLocaleString()} / month` : variant.duration }}</p>
                        </div>
                      </div>
                      <div class="text-right">
                        <p class="font-bold text-amber-700 dark:text-amber-400">
                          <template v-if="variant.label === 'Monthly Installments'">
                            LSL {{ parseFloat(variant.monthly_fee).toLocaleString() }}<span class="text-xs font-normal text-gray-500 dark:text-gray-400">/mo</span>
                          </template>
                          <template v-else>
                            LSL {{ parseFloat(variant.total_price).toLocaleString() }}
                          </template>
                        </p>
                      </div>
                    </label>
                  </div>
                  <div v-if="form.errors.variant_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.variant_id }}</div>
                </div>

                <!-- No variants fallback -->
                <div v-else-if="currentProgramme" class="p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    No study options available for this programme. Fill in your details and we'll be in touch.
                  </p>
                </div>
              </div>
            </div>

            <!-- Document Uploads -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Supporting Documents <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(Optional)</span></h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Upload any relevant documents such as certificates, employer letters, or IDs. Max file size: 2MB per file.</p>
              <div class="space-y-4">
                <!-- Certificate -->
                <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                  <div class="flex-shrink-0 mt-1">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  </div>
                  <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Certificate / Academic Transcripts</label>
                    <input @change="handleFileUpload($event, 'certificate')" type="file" accept=".pdf,.jpeg,.jpg,.png"
                      class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 dark:file:bg-amber-900/40 file:text-amber-700 dark:file:text-amber-300 hover:file:bg-amber-100 dark:hover:file:bg-amber-900/60"/>
                    <div v-if="form.errors.attachments && form.errors.attachments.certificate" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.attachments.certificate }}</div>
                    <div v-if="uploadedFiles.certificate" class="mt-2 flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                      {{ uploadedFiles.certificate.name }}
                    </div>
                  </div>
                </div>

                <!-- Employer Letter -->
                <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                  <div class="flex-shrink-0 mt-1">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                  </div>
                  <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Employer Reference Letter</label>
                    <input @change="handleFileUpload($event, 'employer_letter')" type="file" accept=".pdf,.jpeg,.jpg,.png"
                      class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 dark:file:bg-amber-900/40 file:text-amber-700 dark:file:text-amber-300 hover:file:bg-amber-100 dark:hover:file:bg-amber-900/60"/>
                    <div v-if="form.errors.attachments && form.errors.attachments.employer_letter" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.attachments.employer_letter }}</div>
                    <div v-if="uploadedFiles.employer_letter" class="mt-2 flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                      {{ uploadedFiles.employer_letter.name }}
                    </div>
                  </div>
                </div>

                <!-- ID Document -->
                <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                  <div class="flex-shrink-0 mt-1">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                  </div>
                  <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">ID Document (Passport/National ID)</label>
                    <input @change="handleFileUpload($event, 'id_document')" type="file" accept=".pdf,.jpeg,.jpg,.png"
                      class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 dark:file:bg-amber-900/40 file:text-amber-700 dark:file:text-amber-300 hover:file:bg-amber-100 dark:hover:file:bg-amber-900/60"/>
                    <div v-if="form.errors.attachments && form.errors.attachments.id_document" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.attachments.id_document }}</div>
                    <div v-if="uploadedFiles.id_document" class="mt-2 flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                      {{ uploadedFiles.id_document.name }}
                    </div>
                  </div>
                </div>

                <!-- Additional Documents -->
                <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                  <div class="flex-shrink-0 mt-1">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                  </div>
                  <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Additional Documents</label>
                    <input @change="handleFileUpload($event, 'additional')" type="file" accept=".pdf,.jpeg,.jpg,.png,.doc,.docx" multiple
                      class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 dark:file:bg-amber-900/40 file:text-amber-700 dark:file:text-amber-300 hover:file:bg-amber-100 dark:hover:file:bg-amber-900/60"/>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">You can select multiple files</p>
                    <div v-if="uploadedFiles.additional?.length" class="mt-2 space-y-1">
                      <div v-for="(file, index) in uploadedFiles.additional" :key="index" class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ file.name }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Submit -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
              <button type="submit" :disabled="form.processing"
                class="w-full px-8 py-4 bg-gradient-to-r from-amber-600 to-amber-700 text-white text-base font-bold rounded-lg hover:from-amber-700 hover:to-amber-800 transition disabled:opacity-50 flex items-center justify-center gap-2 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                <span v-if="form.processing">Submitting...</span>
                <span v-else>Submit Application</span>
                <svg v-if="!form.processing" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
              </button>
              <div class="flex items-center justify-center gap-4 mt-4 text-gray-500 dark:text-gray-400 text-xs">
                <span class="inline-flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                  Free to apply
                </span>
                <span class="w-1 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></span>
                <span class="inline-flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  Quick response
                </span>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
  programmes: Array,
});

// Track uploaded files locally for display
const uploadedFiles = reactive({
  certificate: null,
  employer_letter: null,
  id_document: null,
  additional: [],
});

// Form state with files
const form = useForm({
  name: '',
  email: '',
  phone: '',
  programme_id: props.programmes.length > 0 ? props.programmes[0].id : '',
  variant_id: '',
  attachments: {
    certificate: null,
    employer_letter: null,
    id_document: null,
    additional: [],
  },
});

// Handle file upload
const handleFileUpload = (event, type) => {
  const files = event.target.files;

  if (type === 'additional') {
    // Handle multiple files
    uploadedFiles.additional = Array.from(files);
    form.attachments.additional = Array.from(files);
  } else {
    uploadedFiles[type] = files[0];
    form.attachments[type] = files[0];
  }
};

// Computed: current selected programme
const currentProgramme = computed(() => {
  return props.programmes.find(p => p.id === form.programme_id) || null;
});

// Computed: variants for currently selected programme
const currentVariants = computed(() => {
  const prog = currentProgramme.value;
  return prog?.variants?.filter(v => v.is_active) || [];
});

// Reset variant when programme changes
const onProgrammeChange = () => {
  form.variant_id = '';
};

watch(() => form.programme_id, () => {
  form.variant_id = '';
});

const submit = async () => {
  form.processing = true;

  const data = new FormData();
  data.append('name', form.name);
  data.append('email', form.email);
  data.append('phone', form.phone || '');
  data.append('programme_id', form.programme_id);
  if (form.variant_id) {
    data.append('variant_id', form.variant_id);
  }

  // Append files only if they exist
  if (form.attachments.certificate instanceof File) {
    data.append('attachments[certificate]', form.attachments.certificate);
  }
  if (form.attachments.employer_letter instanceof File) {
    data.append('attachments[employer_letter]', form.attachments.employer_letter);
  }
  if (form.attachments.id_document instanceof File) {
    data.append('attachments[id_document]', form.attachments.id_document);
  }
  form.attachments.additional.forEach(file => {
    if (file instanceof File) {
      data.append('attachments[additional][]', file);
    }
  });

  try {
    const response = await fetch(route('public.apply.store'), {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
      body: data,
    });

    if (response.ok) {
      // Clear form and show success via Inertia visit
      form.reset();
      uploadedFiles.certificate = null;
      uploadedFiles.employer_letter = null;
      uploadedFiles.id_document = null;
      uploadedFiles.additional = [];
      router.get(route('public.apply'));
    } else {
      const errorData = await response.json();
      if (errorData.errors) {
        form.errors = errorData.errors;
      }
      if (errorData.message) {
        // Handle server-level error (will be shown via flash)
        form.processing = false;
      }
    }
  } catch (error) {
    console.error('Submission failed:', error);
    form.processing = false;
  }
};
</script>