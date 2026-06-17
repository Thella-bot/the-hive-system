<template>
  <PublicLayout>
    <!-- Hero -->
    <section class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white py-16 lg:py-20 overflow-hidden">
      <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>
      <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-amber-500/10 rounded-full blur-3xl translate-x-1/3 -translate-y-1/3"></div>
      <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-amber-600/10 rounded-full blur-3xl -translate-x-1/3 translate-y-1/3"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-amber-600/20 border border-amber-500/30 rounded-full text-amber-400 text-sm font-medium mb-4">
          <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
          Short Course
        </span>
        <h1 class="text-4xl lg:text-5xl font-bold">Apply Now</h1>
        <p class="mt-4 text-lg text-gray-300 max-w-2xl mx-auto">Join our short course and develop new skills — quick, practical, and career-focused.</p>
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
          <Link :href="route('short-courses.index')"
            class="text-amber-600 hover:text-amber-700 font-medium">
            ← Back to Short Courses
          </Link>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="$page.props.flash?.error" class="py-10">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-6 bg-red-50 border border-red-200 rounded-2xl">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-red-800">{{ $page.props.flash.error.title || 'Submission Failed' }}</h3>
              <p class="text-red-700 text-sm mt-1">{{ $page.props.flash.error.message || $page.props.flash.error }}</p>
            </div>
          </div>
        </div>
        <div class="mt-4 text-center">
          <Link :href="route('programmes')" class="text-amber-600 hover:text-amber-700 font-medium">
            ← Back to Programmes
          </Link>
        </div>
      </div>
    </div>

    <!-- Application Form -->
    <section v-else class="py-16 lg:py-20 bg-gray-50 dark:bg-gray-800 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-72 h-72 bg-amber-500/5 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 left-0 w-56 h-56 bg-amber-500/5 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>
      <div class="relative max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
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

            <!-- Notes -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
              <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Additional Notes (optional)</label>
                <textarea v-model="form.notes" id="notes"
                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                  rows="3" placeholder="Any questions or special requirements..."></textarea>
                <div v-if="form.errors.notes" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.notes }}</div>
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
              <p class="text-center text-gray-500 dark:text-gray-400 text-xs mt-4">Free to apply. We'll be in touch within 5 business days.</p>
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
