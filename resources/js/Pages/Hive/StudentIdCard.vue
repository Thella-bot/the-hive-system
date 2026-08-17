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

      <!-- Card, matching the PDF student ID card design exactly -->
      <div class="relative mx-auto overflow-hidden shadow-2xl"
           style="width: 484px; height: 306px;">

        <!-- Decorative background: traced from the reference design
             (yellow field, one large rotated white ellipse, three black
             shard accents) - viewBox coordinates are 1:1 with the
             reference image (1011x639), scaled to fill the card. -->
        <svg viewBox="0 0 1011 639" preserveAspectRatio="none"
             class="absolute inset-0" style="width: 100%; height: 100%;">
          <rect x="0" y="0" width="1011" height="639" fill="#FFBD59" />
          <ellipse cx="522.6" cy="284.5" rx="312.2" ry="572.7" fill="#ffffff"
                   transform="rotate(99.6 522.6 284.5)" />
          <polygon points="24,0 0,1 0,40" fill="#000000" />
          <polygon points="1010,404 937,469 843,523 735,560 623,577 829,564 1009,531" fill="#000000" />
          <polygon points="0,560 0,588 50,638 78,638" fill="#000000" />
        </svg>

        <!-- Content -->
        <div class="relative z-10 h-full" style="padding: 20px 28px;">
          <!-- Header row: logo + STUDENT CARD pill side by side -->
          <div class="flex items-center justify-between">
            <img src="/images/hbci-logo.png" alt="Honey Bee Culinary Institute" style="height: 40px;" />
            <!-- Pill uses the same yellow as the background field, per the
                 reference design - it reads as a cutout, not a darker accent. -->
            <span class="text-white font-black tracking-widest"
                  style="background: #FFBD59; font-size: 21px; padding: 9px 30px; border-radius: 9999px; letter-spacing: 1px;">
              STUDENT CARD
            </span>
          </div>

          <!-- Details row -->
          <div class="flex gap-5" style="margin-top: 18px;">
            <!-- Photo: sized/positioned to match the reference's bounding box -->
            <div v-if="studentId.photo_url" class="flex-shrink-0">
              <img :src="studentId.photo_url" alt="Student photo"
                   class="object-cover border-2 border-white shadow"
                   style="width: 144px; height: 188px; border-radius: 4px;" />
            </div>
            <div v-else class="flex-shrink-0 flex items-center justify-center border-2 border-white shadow"
                 style="width: 144px; height: 188px; border-radius: 4px; background: #fef3c7; color: #b45309; font-weight: 900; font-size: 40px;">
              {{ studentId.initials }}
            </div>

            <!-- Text details -->
            <div class="space-y-3" style="font-size: 13px; line-height: 18px; padding-top: 6px;">
              <div class="grid gap-x-3 items-baseline" style="grid-template-columns: auto 1fr;">
                <span style="font-weight: 700; color: #111827; font-size: 11px;">STUDENT NO:</span>
                <span style="font-weight: 900; color: #111827; font-size: 14px; text-transform: uppercase;">{{ studentId.student_number || 'N/A' }}</span>
              </div>
              <div class="grid gap-x-3 items-baseline" style="grid-template-columns: auto 1fr;">
                <span style="font-weight: 700; color: #111827; font-size: 11px;">NAME:</span>
                <span style="font-weight: 900; color: #111827; font-size: 14px; text-transform: uppercase;">{{ studentId.name }}</span>
              </div>
              <div class="grid gap-x-3 items-baseline" style="grid-template-columns: auto 1fr;">
                <span style="font-weight: 700; color: #111827; font-size: 11px;">YEAR:</span>
                <span style="font-weight: 900; color: #111827; font-size: 14px; text-transform: uppercase;">{{ studentId.year }}</span>
              </div>
              <div class="grid gap-x-3 items-baseline" style="grid-template-columns: auto 1fr;">
                <span style="font-weight: 700; color: #111827; font-size: 11px;">COURSE:</span>
                <!-- Distinct condensed treatment to echo the reference's
                     contrasting display font. Swap in a real condensed
                     webfont (e.g. Oswald) for an exact match. -->
                <span style="font-weight: 900; color: #111827; font-size: 14.5px; text-transform: uppercase; letter-spacing: -0.3px;">{{ studentId.programme || 'N/A' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="absolute left-0 right-0 text-center"
             style="bottom: 6px; font-size: 9px; color: #9ca3af;">
          This card is the property of Honey Bee Culinary Institute. Return to reception if found.
        </div>
      </div>

      <p class="text-center text-xs text-gray-400 mt-4">
        This card is the property of Honey Bee Culinary Institute. Return to reception if found.
      </p>
    </div>
  </HiveLayout>
</template>
