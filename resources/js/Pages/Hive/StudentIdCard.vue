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

<style scoped>
/* Same condensed display font as the PDF template
   (public/fonts/Oswald-Bold.ttf, registered there via dompdf's
   FontMetrics - see StudentIdController::download()), so the web preview
   and the PDF use the exact same typeface for the programme name. */
@font-face {
  font-family: 'Oswald';
  src: url('/fonts/Oswald-Bold.ttf') format('truetype');
  font-weight: 700 900;
  font-style: normal;
  font-display: swap;
}
</style>

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

      <!--
        Redesigned per a reference layout the user supplied: a solid
        colour header banner, a plain white body with a colon-aligned
        label/value list on the left, and a photo + name + QR code
        stacked on the right. Matches resources/views/pdf/student-id-card.blade.php
        exactly, at 2x scale (484x306px web vs 242x153pt PDF).
      -->
      <div class="relative mx-auto rounded-[20pt] overflow-hidden shadow-2xl bg-white"
           style="width: 484px; height: 306px;">

        <div class="absolute top-0 left-0" style="width: 484px; height: 72px; background: #FFBD59;">
          <img src="/images/hbci-bee-white.png" alt=""
               class="absolute" style="top: 16px; left: 24px; height: 40px;" />
          <div class="absolute text-white font-black"
               style="top: 24px; left: 76px; font-size: 17px; letter-spacing: 0.4px; white-space: nowrap;">
            HONEY BEE CULINARY INSTITUTE
          </div>
        </div>

        <img v-if="studentId.photo_url" :src="studentId.photo_url" alt="Student photo"
             class="absolute object-cover border border-gray-200"
             style="top: 84px; right: 26px; width: 92px; height: 112px; border-radius: 6px;" />
        <div v-else class="absolute flex items-center justify-center border border-gray-200"
             style="top: 84px; right: 26px; width: 92px; height: 112px; border-radius: 6px; background: #fef3c7; color: #b45309; font-weight: 900; font-size: 30px;">
          {{ studentId.initials }}
        </div>

        <!-- Centred under the photo, wraps to 2 lines rather than
             truncating a long name from both ends (a center-aligned
             single-line box with overflow:hidden clips symmetrically,
             losing the start AND end of an overflowing name - confirmed
             by testing before this was changed to wrap). -->
        <div class="absolute text-center font-black uppercase overflow-hidden"
             style="top: 198px; right: 26px; width: 132px; max-height: 28px; line-height: 14px; font-size: 12px; color: #d9820c; word-break: break-word;">
          {{ studentId.name }}
        </div>

        <img v-if="studentId.qr_code" :src="studentId.qr_code" alt="Scan to verify"
             class="absolute" style="top: 232px; right: 40px; width: 64px; height: 64px;" />

        <div class="absolute font-bold" style="top: 96px; left: 26px; width: 116px; font-size: 14px; color: #374151;">Student ID</div>
        <div class="absolute font-bold" style="top: 96px; left: 144px; font-size: 14px; color: #374151;">:</div>
        <div class="absolute font-bold overflow-hidden whitespace-nowrap" style="top: 96px; left: 158px; width: 176px; font-size: 14px; color: #111827;">{{ studentId.student_number || 'N/A' }}</div>

        <div class="absolute font-bold" style="top: 128px; left: 26px; width: 116px; font-size: 14px; color: #374151;">Programme</div>
        <div class="absolute font-bold" style="top: 128px; left: 144px; font-size: 14px; color: #374151;">:</div>
        <!-- Real Oswald condensed font, matching the PDF exactly (see the
             @font-face above) so long programme names still fit on one line. -->
        <div class="absolute uppercase overflow-hidden whitespace-nowrap"
             style="top: 128px; left: 158px; width: 176px; font-family: 'Oswald', sans-serif; font-weight: 700; font-size: 14px; color: #111827;">
          {{ studentId.programme || 'N/A' }}
        </div>

        <div class="absolute font-bold" style="top: 160px; left: 26px; width: 116px; font-size: 14px; color: #374151;">Year</div>
        <div class="absolute font-bold" style="top: 160px; left: 144px; font-size: 14px; color: #374151;">:</div>
        <div class="absolute font-bold overflow-hidden whitespace-nowrap" style="top: 160px; left: 158px; width: 176px; font-size: 14px; color: #111827;">{{ studentId.year }}</div>

        <div class="absolute font-bold" style="top: 192px; left: 26px; width: 116px; font-size: 14px; color: #374151;">Cohort</div>
        <div class="absolute font-bold" style="top: 192px; left: 144px; font-size: 14px; color: #374151;">:</div>
        <div class="absolute font-bold overflow-hidden whitespace-nowrap" style="top: 192px; left: 158px; width: 176px; font-size: 14px; color: #111827;">{{ studentId.cohort || 'N/A' }}</div>

        <div class="absolute italic text-gray-400" style="bottom: 16px; left: 26px; font-size: 12px;">Authorize Signature</div>
      </div>
    </div>
  </HiveLayout>
</template>