<template>
  <PublicLayout>
    <!-- Hero -->
    <section class="bg-gradient-to-br from-orange-600 to-orange-700 text-white py-16 lg:py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-bold">Apply Now</h1>
        <p class="mt-4 text-lg text-orange-100 max-w-2xl mx-auto">Join our short course and develop new skills — quick, practical, and career-focused.</p>
      </div>
    </section>

    <!-- Course Info Banner -->
    <div class="bg-amber-50 border-b border-amber-100 dark:bg-amber-900/20 dark:border-amber-800 py-6">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
          <span class="px-3 py-1 text-xs font-semibold rounded-full"
            :class="{
              'bg-blue-100 text-blue-700': shortCourse.type === 'workshop',
              'bg-purple-100 text-purple-700': shortCourse.type === 'training',
              'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300': shortCourse.type === 'short-course'
            }">
            {{ shortCourse.type?.replace('-', ' ') }}
          </span>
          <div>
            <h2 class="font-bold text-gray-900 dark:text-gray-100">{{ shortCourse.title }}</h2>
            <p class="text-sm text-gray-600">
              {{ shortCourse.duration }}
              <span v-if="shortCourse.courseable?.name"> · {{ shortCourse.courseable.name }}</span>
            </p>
            <p v-if="shortCourse.start_date" class="text-xs text-gray-500 mt-0.5">
              Starts {{ shortCourse.start_date }}
              <span v-if="shortCourse.end_date"> — Ends {{ shortCourse.end_date }}</span>
            </p>
          </div>
          <div class="ml-auto text-right">
            <p v-if="shortCourse.price > 0" class="text-xl font-bold text-amber-700 dark:text-amber-300">LSL {{ parseFloat(shortCourse.price).toLocaleString() }}</p>
            <p v-else class="text-xl font-bold text-green-700">Free</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Success -->
    <div v-if="$page.props.flash?.success" class="py-10">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-6 bg-emerald-50 border border-emerald-200 rounded-2xl">
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
        <div class="mt-4 text-center">
          <Link :href="route('programmes')"
            class="text-amber-600 hover:text-amber-700 font-medium">
            ← Back to Programmes
          </Link>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="$page.props.flash?.error" class="py-10">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-6 bg-red-50 border border-red-200 rounded-2xl text-center">
          <p class="text-red-700">{{ $page.props.flash.error }}</p>
          <Link :href="route('programmes')" class="text-amber-600 hover:text-amber-700 font-medium mt-2 inline-block">
            ← Back to Programmes
          </Link>
        </div>
      </div>
    </div>

    <!-- Application Form -->
    <section v-else class="py-16 lg:py-20 bg-gray-50">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 lg:p-10">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Personal Info -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
              <div class="space-y-5">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full Name *</label>
                  <input v-model="form.name" type="text" id="name" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                    :class="form.errors.name ? 'border-red-400' : ''"
                    placeholder="Enter your full name" />
                  <div v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</div>
                </div>
                <div class="grid sm:grid-cols-2 gap-5">
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address *</label>
                    <input v-model="form.email" type="email" id="email" required
                      class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                      :class="form.errors.email ? 'border-red-400' : ''"
                      placeholder="your@email.com" />
                    <div v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</div>
                  </div>
                  <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                    <input v-model="form.phone" type="tel" id="phone"
                      class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                      placeholder="+266 ..." />
                    <div v-if="form.errors.phone" class="text-red-600 text-xs mt-1">{{ form.errors.phone }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div class="border-t border-gray-100 pt-6">
              <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">Additional Notes (optional)</label>
                <textarea v-model="form.notes" id="notes"
                  class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                  rows="3" placeholder="Any questions or special requirements..."></textarea>
                <div v-if="form.errors.notes" class="text-red-600 text-xs mt-1">{{ form.errors.notes }}</div>
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
              <p class="text-center text-gray-500 text-xs mt-4">Free to apply. We'll be in touch within 5 business days.</p>
            </div>
          </form>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  shortCourse: { type: Object, required: true },
});

const form = useForm({
  name: '',
  email: '',
  phone: '',
  notes: '',
});

const submit = () => {
  form.post(route('short-courses.apply.store', { short_course: props.shortCourse.id }), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};
</script>
