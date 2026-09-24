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

.card-stage {
  width: 100%;
  overflow-x: auto;
  padding: 0.25rem 0.5rem 1rem;
}

.reference-card {
  position: relative;
  width: 484px;
  height: 306px;
  overflow: hidden;
  background: #ffffff url('/images/id-card-bg.png') center / cover no-repeat;
  font-family: Arial, Helvetica, sans-serif;
}

.reference-card::before,
.reference-card::after {
  display: none;
  position: absolute;
  z-index: 0;
  content: '';
  pointer-events: none;
}

.reference-card::before {
  top: -156px;
  right: -36px;
  width: 486px;
  height: 312px;
  border: 18px solid #ffbf5b;
  border-radius: 50%;
}

.reference-card::after {
  bottom: -208px;
  left: -115px;
  width: 710px;
  height: 330px;
  border: 18px solid #ffbf5b;
  border-radius: 50%;
  box-shadow: 0 -15px 0 0 #000000;
}

.card-content {
  position: relative;
  z-index: 1;
  width: 100%;
  height: 100%;
}

.reference-foreground {
  position: absolute;
  z-index: 2;
  inset: 0;
  background: url('/images/id-card-bg.png') center / cover no-repeat;
  clip-path: polygon(0 54%, 16% 68%, 36% 83%, 60% 96%, 100% 93%, 100% 100%, 0 100%);
  pointer-events: none;
}

.reference-photo-frame {
  border: 4px solid #ffffff;
  border-radius: 14px 14px 10px 10px;
  box-shadow: 0 3px 10px rgba(17, 24, 39, 0.2);
}

</style>

<template>
  <HiveLayout title="Student ID Card" description="Digital and printable student identification">
    <div class="max-w-2xl mx-auto">
      <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <Link :href="route('hive.dashboard')"
          class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
          <ArrowLeftIcon class="w-4 h-4" />
          Back to Dashboard
        </Link>
        <a :href="download_url"
          class="inline-flex items-center gap-2 rounded-xl bg-[#13252b] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#26434a] focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
          <ArrowDownTrayIcon class="w-4 h-4" />
          Download PDF
        </a>
      </div>

      <div class="card-stage">
        <div class="reference-card mx-auto shadow-2xl">
          <div class="card-content">
            <img src="/images/hbci-logo-no-text.png" alt=""
              class="absolute object-contain" style="top: 38px; left: 32px; width: 42px; height: 42px;" />
            <div class="absolute uppercase font-black" style="top: 40px; left: 78px; width: 115px; color: #ffbf5b; font-size: 11px; line-height: 1.05;">Honey Bee<br />Culinary Institute</div>
            <div class="absolute uppercase font-black text-center" style="top: 47px; left: 202px; width: 212px; padding: 7px 0; border-radius: 32px; background: #ffbf5b; color: #ffffff; font-size: 23px; line-height: 1; box-shadow: 0 1px 0 rgba(0, 0, 0, 0.04);">Student Card</div>
            <img v-if="studentId.photo_url" :src="studentId.photo_url" alt="Student photo"
              class="absolute object-cover reference-photo-frame" style="top: 84px; left: 25px; width: 148px; height: 190px; object-position: center top;" />
            <div v-else class="absolute flex items-center justify-center reference-photo-frame"
              style="top: 84px; left: 25px; width: 148px; height: 190px; background: #e8edf0; color: #13252b; font-weight: 900; font-size: 38px;">
              {{ studentId.initials }}
            </div>
            <div class="reference-foreground"></div>
            <div class="absolute font-bold" style="top: 106px; left: 210px; font-size: 12px; color: #111111; letter-spacing: 0.15px;">STUDENT NO:</div>
            <div class="absolute font-bold overflow-hidden whitespace-nowrap text-ellipsis" style="top: 106px; left: 304px; width: 146px; font-size: 12px; color: #111111;">{{ studentId.student_number || 'N/A' }}</div>
            <div class="absolute font-bold" style="top: 141px; left: 210px; font-size: 12px; color: #111111; letter-spacing: 0.15px;">NAME:</div>
            <div class="absolute font-bold uppercase overflow-hidden whitespace-nowrap text-ellipsis" style="top: 141px; left: 264px; width: 186px; font-size: 12px; color: #111111;">{{ studentId.name }}</div>
            <div class="absolute font-bold" style="top: 176px; left: 210px; font-size: 12px; color: #111111; letter-spacing: 0.15px;">YEAR:</div>
            <div class="absolute font-bold" style="top: 176px; left: 264px; font-size: 12px; color: #111111;">{{ studentId.year }}</div>
            <div class="absolute font-bold" style="top: 211px; left: 210px; font-size: 12px; color: #111111; letter-spacing: 0.15px;">COURSE:</div>
            <div class="absolute uppercase overflow-hidden whitespace-nowrap text-ellipsis" style="top: 211px; left: 272px; width: 178px; font-family: 'Oswald', sans-serif; font-weight: 700; font-size: 11px; color: #111111;">{{ studentId.programme || 'N/A' }}</div>
          </div>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>