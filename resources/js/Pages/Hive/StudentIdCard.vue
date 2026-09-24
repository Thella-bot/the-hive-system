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
        <div class="relative mx-auto overflow-hidden shadow-2xl"
             style="width: 484px; height: 306px; border-radius: 16px; background: #13252b;">

        <div class="absolute top-0 left-0" style="width: 484px; height: 72px;">
          <div class="absolute" style="top: 0; left: 0; width: 14px; height: 306px; background: #f4b41a;"></div>
          <img src="/images/hbci-logo-no-text.png" alt=""
               class="absolute" style="top: 16px; left: 30px; width: 38px; height: 38px; padding: 4px; border-radius: 50%; background: #f4b41a;" />
          <div class="absolute text-gray-100 font-black"
               style="top: 18px; left: 78px; font-size: 14px; letter-spacing: 0.9px; white-space: nowrap;">
            HONEY BEE CULINARY INSTITUTE
          </div>
          <div class="absolute uppercase"
               style="top: 39px; left: 78px; color: #d9e1dd; font-size: 9px; letter-spacing: 1.6px;">
            Learn. Create. Lead.
          </div>
          <div class="absolute uppercase font-black"
               style="top: 20px; right: 28px; color: #f4b41a; font-size: 9px; letter-spacing: 2px;">
            Student identity
          </div>
          <div class="absolute uppercase font-black"
            :style="studentId.status === 'Expired'
              ? 'top: 50px; right: 28px; padding: 4px 10px; border-radius: 999px; background: #fde4e4; color: #9b2c2c; font-size: 8px; letter-spacing: 1px;'
              : 'top: 50px; right: 28px; padding: 4px 10px; border-radius: 999px; background: #d9f5e5; color: #17633c; font-size: 8px; letter-spacing: 1px;'">
            {{ studentId.status || 'Active' }}
          </div>
        </div>

        <div class="absolute" style="top: 72px; left: 14px; width: 314px; height: 218px; padding: 24px 20px; background: #f8f5ed;">
          <div class="uppercase font-black" style="color: #a86f00; font-size: 9px; letter-spacing: 2px;">Official student record</div>
        </div>
        <div class="absolute uppercase" style="top: 86px; right: 184px; color: #8b9692; font-size: 8px; letter-spacing: 1px;">HBCI / ID</div>
        <div class="absolute" style="top: 132px; left: 34px; width: 274px; height: 1px; background: #dfe5df;"></div>
        <div class="absolute" style="top: 164px; left: 34px; width: 274px; height: 1px; background: #dfe5df;"></div>
        <div class="absolute" style="top: 196px; left: 34px; width: 274px; height: 1px; background: #dfe5df;"></div>

           <img v-if="studentId.photo_url" :src="studentId.photo_url" alt="Student photo"
             class="absolute object-cover border border-gray-200"
             style="top: 88px; right: 32px; width: 96px; height: 116px; border-radius: 8px; border: 4px solid #f4b41a;" />
        <div v-else class="absolute flex items-center justify-center border border-gray-200"
             style="top: 88px; right: 32px; width: 96px; height: 116px; border-radius: 8px; border: 4px solid #f4b41a; background: #26434a; color: #f4b41a; font-weight: 900; font-size: 30px;">
          {{ studentId.initials }}
        </div>

        <!-- Centred under the photo, wraps to 2 lines rather than
             truncating a long name from both ends (a center-aligned
             single-line box with overflow:hidden clips symmetrically,
             losing the start AND end of an overflowing name - confirmed
             by testing before this was changed to wrap). -->
        <div class="absolute text-center font-black uppercase overflow-hidden"
             style="top: 212px; right: 20px; width: 120px; max-height: 28px; line-height: 14px; font-size: 11px; color: #f8f5ed; word-break: break-word;">
          {{ studentId.name }}
        </div>

        <img v-if="studentId.qr_code" :src="studentId.qr_code" alt="Scan to verify"
             class="absolute" style="top: 238px; right: 32px; width: 58px; height: 58px; padding: 4px; border: 1px solid #f4b41a; border-radius: 6px; background: #f8f5ed;" />
           <div class="absolute uppercase" style="top: 248px; left: 354px; color: #b6c7c3; font-size: 8px; letter-spacing: 1px;">Scan to verify</div>
           <div class="absolute uppercase font-black" style="top: 264px; left: 354px; color: #f4b41a; font-size: 8px; letter-spacing: 1.4px;">Secure ID check</div>

          <div class="absolute font-bold" style="top: 108px; left: 34px; width: 90px; font-size: 11px; color: #65716e;">Student ID</div>
          <div class="absolute font-bold" style="top: 108px; left: 126px; font-size: 11px; color: #a86f00;">:</div>
          <div class="absolute font-bold overflow-hidden whitespace-nowrap" style="top: 108px; left: 140px; width: 168px; font-size: 11px; color: #13252b;">{{ studentId.student_number || 'N/A' }}</div>

          <div class="absolute font-bold" style="top: 140px; left: 34px; width: 90px; font-size: 11px; color: #65716e;">Programme</div>
          <div class="absolute font-bold" style="top: 140px; left: 126px; font-size: 11px; color: #a86f00;">:</div>
        <div class="absolute uppercase overflow-hidden whitespace-nowrap"
             style="top: 140px; left: 140px; width: 168px; font-family: 'Oswald', sans-serif; font-weight: 700; font-size: 11px; color: #13252b;">
          {{ studentId.programme || 'N/A' }}
        </div>

          <div class="absolute font-bold" style="top: 172px; left: 34px; width: 90px; font-size: 11px; color: #65716e;">Year</div>
          <div class="absolute font-bold" style="top: 172px; left: 126px; font-size: 11px; color: #a86f00;">:</div>
          <div class="absolute font-bold overflow-hidden whitespace-nowrap" style="top: 172px; left: 140px; width: 168px; font-size: 11px; color: #13252b;">{{ studentId.year }}</div>

          <div class="absolute font-bold" style="top: 204px; left: 34px; width: 90px; font-size: 11px; color: #65716e;">Cohort</div>
          <div class="absolute font-bold" style="top: 204px; left: 126px; font-size: 11px; color: #a86f00;">:</div>
          <div class="absolute font-bold overflow-hidden whitespace-nowrap" style="top: 204px; left: 140px; width: 168px; font-size: 11px; color: #13252b;">{{ studentId.cohort || 'N/A' }}</div>

          <div class="absolute italic" style="bottom: 16px; left: 34px; color: #8b9692; font-size: 9px;">Authorize Signature</div>
          <div class="absolute uppercase" style="top: 266px; left: 238px; color: #a86f00; font-size: 8px; letter-spacing: 1px;">Valid thru</div>
          <div class="absolute font-black" style="top: 264px; left: 290px; color: #13252b; font-size: 10px;">{{ studentId.valid_until || 'Active' }}</div>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>