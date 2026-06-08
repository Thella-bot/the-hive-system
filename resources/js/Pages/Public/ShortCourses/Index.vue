<template>
  <PublicLayout>
    <!-- Hero -->
    <section class="bg-gradient-to-br from-orange-600 to-orange-700 text-white py-16 lg:py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-bold">Short Courses & Workshops</h1>
        <p class="mt-4 text-lg text-orange-100 max-w-2xl mx-auto">Intensive, skill-focused courses with no long-term commitment. Develop new abilities quickly and boost your career.</p>
      </div>
    </section>

    <!-- Short Courses Grid -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div v-if="shortCourses.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="course in shortCourses" :key="course.id"
            class="group bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-lg hover:border-amber-200 hover:-translate-y-1 transition-all duration-300 dark:bg-gray-800 dark:border-gray-700 dark:hover:border-amber-200">
            <div class="flex items-start justify-between mb-4">
              <span class="px-2.5 py-1 text-xs font-semibold rounded-full"
                :class="{
                  'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300': course.type === 'workshop',
                  'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300': course.type === 'training',
                  'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300': course.type === 'short-course'
                }">
                {{ formatType(course.type) }}
              </span>
              <span v-if="course.accepting_applications" class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full dark:bg-emerald-900/40 dark:text-emerald-300">Open</span>
              <span v-else class="px-2 py-1 bg-gray-100 text-gray-500 text-xs rounded-full dark:bg-gray-700 dark:text-gray-400">Closed</span>
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
              <span v-if="course.end_date"> — {{ course.end_date }}</span>
            </div>
            <Link v-if="course.accepting_applications" :href="route('short-courses.apply', { short_course: course.id })"
              class="inline-flex items-center gap-2 text-amber-600 font-semibold text-sm hover:text-amber-700 transition">
              Apply Now
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </Link>
            <span v-else class="text-sm text-gray-400 dark:text-gray-500">Applications closed</span>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-20">
          <p class="text-gray-500 dark:text-gray-400">No short courses available at this time. Check back soon!</p>
          <Link :href="route('programmes')" class="text-amber-600 hover:text-amber-700 font-medium mt-4 inline-block dark:text-amber-400">
            ← View our programmes
          </Link>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-16 bg-gradient-to-br from-amber-600 to-amber-700 text-white text-center py-16">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold">Want to Pursue a Full Programme?</h2>
        <p class="mt-2 text-amber-100">Explore our professional diplomas and certificates for a complete culinary education.</p>
        <Link :href="route('programmes')"
          class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-white text-amber-700 font-semibold rounded-lg hover:bg-amber-50 transition">
          View Programmes
        </Link>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  shortCourses: Array,
});

const typeLabels = {
  workshop: 'Workshop',
  training: 'Training',
  'short-course': 'Short Course',
};

const formatType = (type) => typeLabels[type] ?? type?.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) ?? '';
</script>
