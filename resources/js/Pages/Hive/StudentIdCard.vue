<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { ArrowDownTrayIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  student_id: { type: Object, required: true },
  can_manage: { type: Boolean, default: false },
  download_url: { type: String, required: true },
});

const studentId = computed(() => props.student_id || {});
</script>

<template>
  <HiveLayout title="Student ID Card" description="Digital and printable student identification">
    <div class="max-w-2xl mx-auto">
      <div class="mb-6 flex items-center justify-between">
        <Link :href="route('hive.dashboard')"
          class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-sm flex items-center gap-1">
          <ArrowLeftIcon class="w-4 h-4" />
          Back to Dashboard
        </Link>
        <a :href="download_url"
          class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          <ArrowDownTrayIcon class="w-4 h-4" />
          Download PDF
        </a>
      </div>

      <!-- Card, matching HBCI brand ID design -->
      <div class="relative w-full aspect-[16/10] rounded-3xl overflow-hidden bg-white shadow-2xl border border-gray-100">
        <!-- Decorative orange blobs -->
        <div class="absolute -top-16 -right-10 w-64 h-64 rounded-full bg-amber-400"></div>
        <div class="absolute -bottom-20 -left-16 w-56 h-56 rounded-full bg-amber-400"></div>
        <!-- Diagonal black accent, bottom right -->
        <div class="absolute bottom-0 right-0 w-40 h-24 bg-black" style="clip-path: polygon(100% 0, 100% 100%, 0 100%);"></div>

        <!-- Content -->
        <div class="relative z-10 p-6 sm:p-8 h-full flex flex-col">
          <div class="flex items-center justify-between">
            <img src="/images/hbci-logo.png" alt="Honey Bee Culinary Institute" class="h-9 sm:h-10" />
          </div>

          <div class="mt-3">
            <span class="inline-block bg-amber-500 text-white font-extrabold text-xl sm:text-2xl tracking-wide px-6 py-2 rounded-full">
              STUDENT CARD
            </span>
          </div>

          <div class="mt-5 space-y-2.5 text-sm sm:text-base">
            <div class="grid grid-cols-[auto,1fr] gap-x-3">
              <span class="font-bold text-gray-900">STUDENT NO:</span>
              <span class="font-extrabold text-gray-900">{{ studentId.student_number || 'N/A' }}</span>
            </div>
            <div class="grid grid-cols-[auto,1fr] gap-x-3">
              <span class="font-bold text-gray-900">NAME:</span>
              <span class="font-extrabold text-gray-900 uppercase">{{ studentId.name }}</span>
            </div>
            <div class="grid grid-cols-[auto,1fr] gap-x-3">
              <span class="font-bold text-gray-900">YEAR:</span>
              <span class="font-extrabold text-gray-900">{{ studentId.year }}</span>
            </div>
            <div class="grid grid-cols-[auto,1fr] gap-x-3">
              <span class="font-bold text-gray-900">COURSE:</span>
              <span class="font-extrabold text-gray-900 uppercase">{{ studentId.programme || 'N/A' }}</span>
            </div>
          </div>
        </div>
      </div>

      <p class="text-center text-xs text-gray-400 mt-4">
        This card is the property of Honey Bee Culinary Institute. Return to reception if found.
      </p>
    </div>
  </HiveLayout>
</template>
