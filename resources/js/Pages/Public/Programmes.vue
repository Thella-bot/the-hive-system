<template>
  <PublicLayout>
    <!-- Page Hero -->
    <section class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white py-20 lg:py-28 overflow-hidden">
      <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>
      <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-3xl translate-x-1/3 -translate-y-1/3"></div>
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-amber-600/10 rounded-full blur-3xl -translate-x-1/3 translate-y-1/3"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-amber-400/5 rounded-full blur-3xl"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-amber-600/20 border border-amber-500/30 rounded-full text-amber-400 text-sm font-medium mb-6">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          Accredited Training
        </span>
        <h1 class="text-4xl lg:text-5xl font-bold">Our Programmes</h1>
        <p class="mt-4 text-lg text-gray-300 max-w-2xl mx-auto">From professional diplomas to short workshops — find the perfect path to launch your culinary career.</p>
      </div>
    </section>

    <!-- Flexible Study Banner -->
    <div class="bg-amber-50 dark:bg-amber-900/20 border-b border-amber-100 dark:border-amber-800 py-4">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-amber-800 dark:text-amber-200">
          <strong>Flexible Study Options:</strong> Many programmes offer multiple study modes (e.g., full-time, part-time, weekend) with different durations and fees.
        </p>
      </div>
    </div>

    <!-- Programmes -->
    <section class="py-20 bg-white dark:bg-gray-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-20">
          <div v-for="(programme, index) in programmes" :key="programme.id"
            class="group grid lg:grid-cols-2 gap-12 items-start"
            :class="index % 2 === 1 ? 'lg:grid-flow-dense' : ''">

            <!-- Content Column -->
            <div :class="index % 2 === 1 ? 'lg:col-start-2' : ''">
              <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition">{{ programme.name }}</h2>
              <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">{{ programme.description }}</p>

              <!-- Modules -->
              <div v-if="programme.modules?.length" class="mb-6">
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">What You'll Learn</h3>
                <div class="flex flex-wrap gap-2">
                  <span v-for="module in programme.modules" :key="module.id"
                    class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm rounded-lg border border-gray-200 dark:border-gray-700 hover:border-amber-300 dark:hover:border-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 hover:text-amber-700 dark:hover:text-amber-400 transition">
                    {{ module.name }}
                  </span>
                </div>
              </div>

              <div class="flex flex-wrap gap-4">
                <Link :href="programme.name === 'Short Courses and Cooking Sessions' ? route('short-courses.index') : route('apply')"
                  class="inline-flex items-center gap-2 px-6 py-3 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition shadow-sm">
                  Apply Now
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </Link>
                <Link :href="route('contact')" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                  Ask a Question
                </Link>
              </div>
            </div>

            <!-- Info Card -->
            <div :class="index % 2 === 1 ? 'lg:col-start-1 lg:row-start-1' : ''">
              <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 dark:from-amber-900/20 dark:to-amber-900/10 rounded-2xl border border-amber-200 dark:border-amber-700 p-8 sticky top-24 hover:shadow-xl transition-shadow duration-300">
                <div class="bg-amber-600 text-white rounded-xl p-5 mb-6">
                  <p class="text-sm text-amber-200 mb-1">Programmes</p>
                  <p class="text-xl font-bold leading-tight">{{ programme.name }}</p>
                </div>

                <div class="space-y-3 text-sm">
                  <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.628-9-1.732-2.78 1.104-6.817 1.732-9 1.732-3.183 0-6.22-.628-9-1.732a23.942 23.942 0 010-4.49C2.78 8.778 6.817 8.15 9.9 7.046A23.99 23.99 0 0112 4c4.97 0 9 1.522 12 4.255A23.931 23.931 0 0112 15z"/>
                    </svg>
                    <div>
                      <p class="text-gray-500 dark:text-gray-400">Location</p>
                      <p class="font-medium text-gray-900 dark:text-white">Maseru, Lesotho</p>
                    </div>
                  </div>
                  <div v-if="programme.department" class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <div>
                      <p class="text-gray-500 dark:text-gray-400">Department</p>
                      <p class="font-medium text-gray-900 dark:text-white">{{ programme.department.name }}</p>
                    </div>
                  </div>
                  <div v-if="programme.intake_period" class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div>
                      <p class="text-gray-500 dark:text-gray-400">Intake</p>
                      <p class="font-medium text-gray-900 dark:text-white">{{ programme.intake_period }}</p>
                    </div>
                  </div>
                </div>

                <!-- Fee Breakdown -->
                <div v-if="programme.total_price > 0" class="mt-6 pt-6 border-t border-amber-200 dark:border-amber-700">
                  <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Fee Breakdown</p>
                  <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between">
                      <span class="text-gray-600 dark:text-gray-400">Registration</span>
                      <span class="font-medium text-gray-900 dark:text-white">LSL {{ parseFloat(programme.registration_fee || 0).toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600 dark:text-gray-400">Monthly</span>
                      <span class="font-medium text-gray-900 dark:text-white">LSL {{ parseFloat(programme.monthly_fee || 0).toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600 dark:text-gray-400">Academic Resource</span>
                      <span class="font-medium text-gray-900 dark:text-white">LSL {{ parseFloat(programme.academic_resource_fee || 0).toLocaleString() }}</span>
                    </div>
                    <div v-if="programme.uniform_fee > 0" class="flex justify-between">
                      <span class="text-gray-600 dark:text-gray-400">Uniform</span>
                      <span class="font-medium text-gray-900 dark:text-white">LSL {{ parseFloat(programme.uniform_fee).toLocaleString() }}</span>
                    </div>
                    <div v-if="programme.tools_cost > 0" class="flex justify-between">
                      <span class="text-gray-600 dark:text-gray-400">Tools</span>
                      <span class="font-medium text-gray-900 dark:text-white">LSL {{ parseFloat(programme.tools_cost).toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-amber-100 dark:border-amber-800 font-semibold">
                      <span class="text-gray-700 dark:text-gray-300">Total</span>
                      <span class="text-amber-700 dark:text-amber-400">LSL {{ parseFloat(programme.total_price).toLocaleString() }}</span>
                    </div>
                  </div>
                  <p v-if="programme.payment_method" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ programme.payment_method === 'both' ? 'Monthly or Quarterly' : programme.payment_method }} payment available
                  </p>
                </div>

                <!-- Requirements -->
                <div v-if="programme.requirements" class="mt-6 pt-6 border-t border-amber-200 dark:border-amber-700">
                  <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Entry Requirements</p>
                  <p class="text-sm text-gray-700 dark:text-gray-300">{{ programme.requirements }}</p>
                </div>

                <!-- Career Opportunities -->
                <div v-if="programme.career_opportunities" class="mt-6 pt-6 border-t border-amber-200 dark:border-amber-700">
                  <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Career Opportunities</p>
                  <p class="text-sm text-gray-700 dark:text-gray-300">{{ programme.career_opportunities }}</p>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-16 bg-gradient-to-br from-amber-600 to-amber-700 relative overflow-hidden">
      <div class="absolute top-0 left-0 w-[400px] h-[400px] bg-white/5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 right-0 w-[300px] h-[300px] bg-white/5 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-amber-500/10 rounded-full blur-3xl"></div>
      <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white text-sm font-medium mb-4">
          <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
          Need Help Choosing?
        </span>
        <h2 class="text-2xl lg:text-3xl font-bold text-white">Not Sure Which Programme Is Right for You?</h2>
        <p class="mt-3 text-amber-100">Our admissions team can help guide you to the best choice based on your goals.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-4">
          <Link :href="route('contact')" class="group inline-flex items-center gap-2 px-6 py-3 bg-white text-amber-700 font-semibold rounded-lg hover:bg-amber-50 transition hover:-translate-y-0.5">Contact Admissions
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </Link>
          <Link :href="route('about')" class="px-6 py-3 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition">Learn About HBCI</Link>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  programmes: Array,
});
</script>
