<template>
  <PublicLayout>
    <!-- Page Hero -->
    <section class="relative bg-gray-900 text-white py-20 lg:py-28 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-800 to-amber-900"></div>
      <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>
      <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 left-0 w-72 h-72 bg-amber-600/10 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-bold">Our Programmes</h1>
        <p class="mt-4 text-lg text-gray-300 max-w-2xl mx-auto">From professional diplomas to short workshops — find the perfect path to launch your culinary career.</p>
      </div>
    </section>

    <!-- Programme Variants Info Banner -->
    <div class="bg-amber-50 border-b border-amber-100 py-4">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-amber-800">
          <strong>Flexible Study Options:</strong> Many programmes offer multiple study modes (e.g., full-time, part-time, weekend) with different durations and fees. Click into any programme to see all available options.
        </p>
      </div>
    </div>

    <!-- Programmes -->
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-20">
          <div v-for="(programme, index) in programmes" :key="programme.id"
            class="group grid lg:grid-cols-2 gap-12 items-start"
            :class="index % 2 === 1 ? 'lg:grid-flow-dense' : ''">

            <!-- Content -->
            <div :class="index % 2 === 1 ? 'lg:col-start-2' : ''">
              <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3 group-hover:text-amber-700 transition">{{ programme.name }}</h2>
              <p class="text-gray-600 leading-relaxed mb-6">{{ programme.description }}</p>

              <!-- Duration Options -->
              <div v-if="programme.variants && programme.variants.length" class="mb-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Available Options</h3>
                <div class="space-y-2">
                  <div v-for="variant in programme.variants" :key="variant.id"
                    class="flex items-center justify-between p-4 rounded-xl border transition hover:border-amber-300 hover:bg-amber-50"
                    :class="selectedVariants[programme.id] === variant.id ? 'border-amber-400 bg-amber-50' : 'border-gray-200'">
                    <div class="flex items-center gap-3">
                      <div>
                        <p class="font-semibold text-gray-900">{{ variant.label }}</p>
                        <p class="text-sm text-gray-500">{{ variant.duration }}</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p class="font-bold text-amber-700">LSL {{ parseFloat(variant.total_price).toLocaleString() }}</p>
                      <p v-if="variant.monthly_fee > 0" class="text-xs text-gray-500">LSL {{ parseFloat(variant.monthly_fee).toLocaleString() }}/mo</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Modules -->
              <div v-if="programme.modules && programme.modules.length" class="mb-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">What You'll Learn</h3>
                <div class="flex flex-wrap gap-2">
                  <span v-for="module in programme.modules" :key="module.id"
                    class="px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-lg border border-gray-200 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 transition">
                    {{ module.name }}
                  </span>
                </div>
              </div>

              <div class="flex flex-wrap gap-4">
                <Link :href="route('apply')" class="group inline-flex items-center gap-2 px-6 py-3 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition shadow-sm">
                  Apply Now
                  <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </Link>
                <Link :href="route('contact')" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                  Ask a Question
                </Link>
              </div>
            </div>

            <!-- Visual Info Card -->
            <div :class="index % 2 === 1 ? 'lg:col-start-1 lg:row-start-1' : ''">
              <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-2xl border border-amber-200 p-8 sticky top-24 hover:shadow-xl transition-shadow duration-300">
                <div class="bg-amber-600 text-white rounded-xl p-5 mb-6">
                  <p class="text-sm text-amber-200 mb-1">Programmes</p>
                  <p class="text-xl font-bold leading-tight">{{ programme.name }}</p>
                </div>

                <!-- Variants Summary -->
                <div v-if="programme.variants && programme.variants.length" class="mb-6">
                  <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Study Options</p>
                  <div class="space-y-2">
                    <div v-for="variant in programme.variants" :key="variant.id" class="flex items-center justify-between text-sm">
                      <span class="text-gray-700">{{ variant.label }}</span>
                      <span class="font-medium text-gray-900">{{ variant.duration }}</span>
                    </div>
                  </div>
                </div>

                <div class="space-y-3 text-sm">
                  <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.628-9-1.732-2.78 1.104-6.817 1.732-9 1.732-3.183 0-6.22-.628-9-1.732a23.942 23.942 0 010-4.49C2.78 8.778 6.817 8.15 9.9 7.046A23.99 23.99 0 0112 4c4.97 0 9 1.522 12 4.255A23.931 23.931 0 0112 15z"/>
                    </svg>
                    <div>
                      <p class="text-gray-500">Location</p>
                      <p class="font-medium text-gray-900">Maseru, Lesotho</p>
                    </div>
                  </div>
                  <div v-if="programme.department" class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <div>
                      <p class="text-gray-500">Department</p>
                      <p class="font-medium text-gray-900">{{ programme.department.name }}</p>
                    </div>
                  </div>
                </div>

                <!-- Short Courses attached to this programme -->
                <div v-if="programme.short_courses && programme.short_courses.length" class="mt-6 pt-6 border-t border-amber-200">
                  <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Workshops & Short Courses</p>
                  <div class="space-y-2">
                    <div v-for="sc in programme.short_courses" :key="sc.id" class="flex items-center justify-between p-3 bg-white rounded-lg border border-amber-100">
                      <div>
                        <p class="text-sm font-medium text-gray-900">{{ sc.title }}</p>
                        <p class="text-xs text-gray-500">{{ sc.type }} · {{ sc.duration }}</p>
                      </div>
                      <span v-if="sc.accepting_applications" class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs rounded-full">Open</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Short Courses & Workshops -->
    <section v-if="allShortCourses.length" class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-orange-100 text-orange-700 rounded-full text-xs font-bold uppercase tracking-wide mb-4">
              Enrol Now
            </div>
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">Short Courses & Workshops</h2>
            <p class="mt-2 text-gray-600">Intensive workshops and training programmes. No long-term commitment — develop specific skills quickly.</p>
          </div>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="course in allShortCourses" :key="course.id"
            class="group bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-lg hover:border-orange-200 hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
              <span class="px-2.5 py-1 text-xs font-semibold rounded-full"
                :class="{
                  'bg-blue-100 text-blue-700': course.type === 'workshop',
                  'bg-purple-100 text-purple-700': course.type === 'training',
                  'bg-orange-100 text-orange-700': course.type === 'short-course'
                }">
                {{ course.type.replace('-', ' ') }}
              </span>
              <span v-if="course.accepting_applications" class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full">Open</span>
              <span v-else class="px-2 py-1 bg-gray-100 text-gray-500 text-xs rounded-full">Closed</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ course.title }}</h3>
            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ course.description }}</p>
            <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ course.duration }}
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M3 21h18a2 2 0 100-4h-2m-11 0a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                LSL {{ parseFloat(course.price).toLocaleString() }}
              </span>
            </div>
            <div v-if="course.start_date" class="text-xs text-gray-400 mb-4">
              {{ course.start_date }}
            </div>
            <Link :href="route('apply')" class="group/sub inline-flex items-center gap-2 text-amber-600 font-semibold text-sm hover:text-amber-700 transition">
              Apply Now
              <svg class="w-4 h-4 group-hover/sub:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-16 bg-gradient-to-br from-amber-600 to-amber-700 relative overflow-hidden">
      <div class="absolute top-0 left-0 w-96 h-96 bg-white/5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 right-0 w-72 h-72 bg-white/5 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
      <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl lg:text-3xl font-bold text-white">Not Sure Which Programme Is Right for You?</h2>
        <p class="mt-3 text-amber-100">Our admissions team can help guide you to the best choice based on your goals.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-4">
          <Link :href="route('contact')" class="group inline-flex items-center gap-2 px-6 py-3 bg-white text-amber-700 font-semibold rounded-lg hover:bg-amber-50 transition">Contact Admissions</Link>
          <Link :href="route('about')" class="px-6 py-3 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition">Learn About HBCI</Link>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  programmes: Array,
  departmentCourses: Array,
});

// Track selected variant per programme (default to first)
const selectedVariants = ref({});

const initSelectedVariants = () => {
  props.programmes.forEach(p => {
    if (p.variants && p.variants.length && !(p.id in selectedVariants.value)) {
      selectedVariants.value[p.id] = p.variants[0].id;
    }
  });
};

// Flatten programme short courses + department short courses
const allShortCourses = computed(() => {
  const progCourses = props.programmes.flatMap(p => p.short_courses || []);
  const deptCourses = props.departmentCourses || [];
  return [...progCourses, ...deptCourses].filter(c => c.is_active && c.accepting_applications);
});

initSelectedVariants();
</script>