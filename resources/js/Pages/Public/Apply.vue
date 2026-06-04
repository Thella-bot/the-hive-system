<template>
  <PublicLayout>
    <!-- Hero -->
    <section class="bg-gradient-to-br from-amber-600 to-amber-700 text-white py-16 lg:py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-bold">Apply Now</h1>
        <p class="mt-4 text-lg text-amber-100 max-w-2xl mx-auto">Start your journey to becoming a culinary professional. Complete the form below — it's free and takes only a few minutes.</p>
      </div>
    </section>

    <!-- Application Form -->
    <section class="py-16 lg:py-20 bg-gray-50">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success -->
        <div v-if="$page.props.flash?.success" class="mb-8 p-6 bg-emerald-50 border border-emerald-200 rounded-2xl">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-emerald-800">Application Submitted!</h3>
              <p class="text-emerald-700 text-sm mt-1">{{ $page.props.flash.success }}</p>
            </div>
          </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 lg:p-10">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Personal Info -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
              <div class="space-y-5">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full Name *</label>
                  <input v-model="form.name" type="text" id="name" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                    :class="form.errors.name ? 'border-red-400' : ''"
                    placeholder="Enter your full name" />
                  <div v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</div>
                </div>
                <div class="grid sm:grid-cols-2 gap-5">
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address *</label>
                    <input v-model="form.email" type="email" id="email" required
                      class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                      :class="form.errors.email ? 'border-red-400' : ''"
                      placeholder="your@email.com" />
                    <div v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</div>
                  </div>
                  <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                    <input v-model="form.phone" type="tel" id="phone"
                      class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                      placeholder="+266 ..." />
                    <div v-if="form.errors.phone" class="text-red-600 text-xs mt-1">{{ form.errors.phone }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Programme Selection -->
            <div class="border-t border-gray-100 pt-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Programme Choice</h3>
              <div class="space-y-4">
                <!-- Programme Select -->
                <div>
                  <label for="programme_id" class="block text-sm font-medium text-gray-700 mb-1.5">Programme *</label>
                  <select v-model="form.programme_id" id="programme_id" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                    @change="onProgrammeChange">
                    <option value="" disabled>Select a programme</option>
                    <option v-for="p in programmes" :key="p.id" :value="p.id">
                      {{ p.name }}
                    </option>
                  </select>
                  <div v-if="form.errors.programme_id" class="text-red-600 text-xs mt-1">{{ form.errors.programme_id }}</div>
                </div>

                <!-- Variant Select (if programme has variants) -->
                <div v-if="currentVariants.length">
                  <label for="variant_id" class="block text-sm font-medium text-gray-700 mb-1.5">Study Option *</label>
                  <div class="space-y-2">
                    <label v-for="variant in currentVariants" :key="variant.id"
                      class="flex items-center justify-between p-4 rounded-xl border cursor-pointer transition"
                      :class="form.variant_id === variant.id
                        ? 'border-amber-400 bg-amber-50'
                        : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'">
                      <div class="flex items-center gap-3">
                        <input type="radio" :value="variant.id" v-model="form.variant_id" class="w-4 h-4 text-amber-600" />
                        <div>
                          <p class="font-medium text-gray-900">{{ variant.label }}</p>
                          <p class="text-sm text-gray-500">{{ variant.duration }}</p>
                        </div>
                      </div>
                      <div class="text-right">
                        <p class="font-bold text-amber-700">LSL {{ parseFloat(variant.total_price).toLocaleString() }}</p>
                      </div>
                    </label>
                  </div>
                  <div v-if="form.errors.variant_id" class="text-red-600 text-xs mt-1">{{ form.errors.variant_id }}</div>
                </div>
              </div>
            </div>

            <!-- Submit -->
            <div class="border-t border-gray-100 pt-6">
              <button type="submit" :disabled="form.processing"
                class="w-full px-8 py-4 bg-amber-600 text-white text-base font-bold rounded-lg hover:bg-amber-700 transition disabled:opacity-50 flex items-center justify-center gap-2">
                <span v-if="form.processing">Submitting...</span>
                <span v-else>Submit Application</span>
                <svg v-if="!form.processing" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
              </button>
              <p class="text-center text-gray-500 text-xs mt-4">Free to apply. No payment required at this stage. We'll be in touch within 5 business days.</p>
            </div>
          </form>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { computed, watch } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  programmes: Array,
});

// Form state
const form = useForm({
  name: '',
  email: '',
  phone: '',
  programme_id: props.programmes.length > 0 ? props.programmes[0].id : '',
  variant_id: '',
});

// Computed: variants for currently selected programme
const currentVariants = computed(() => {
  const prog = props.programmes.find(p => p.id === form.programme_id);
  return prog?.variants?.filter(v => v.is_active) || [];
});

// Reset variant when programme changes
const onProgrammeChange = () => {
  form.variant_id = '';
};

watch(() => form.programme_id, () => {
  form.variant_id = '';
});

const submit = () => {
  form.post(route('public.apply.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};
</script>