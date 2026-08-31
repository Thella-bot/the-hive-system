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
    <div class="max-w-3xl mx-auto px-4">
      <div class="mb-6 flex items-center justify-between">
        <Link
          :href="route('hive.dashboard')"
          class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-sm flex items-center gap-1"
        >
          <ArrowLeftIcon class="w-4 h-4" />
          Back to Dashboard
        </Link>

        <a
          :href="download_url"
          class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        >
          <ArrowDownTrayIcon class="w-4 h-4" />
          Download PDF
        </a>
      </div>

      <!-- Digital card. Keep the aspect ratio identical to the 242pt x 153pt PDF. -->
      <div
        class="relative mx-auto overflow-hidden rounded-[28px] bg-white shadow-2xl ring-1 ring-black/5"
        style="width: min(100%, 726px); aspect-ratio: 242 / 153;"
      >
        <!-- Header -->
        <div
          class="absolute inset-x-0 top-0 flex items-center bg-amber-500"
          style="height: 25.49%; padding: 0 5.37%;"
        >
          <img
            src="/images/hbci-logo.png"
            alt="Honey Bee Culinary Institute"
            class="h-auto object-contain"
            style="width: 13.5%; max-height: 62%;"
          />
          <div
            class="ml-4 truncate text-white font-black tracking-wide"
            style="font-size: clamp(14px, 2.45vw, 29px);"
          >
            HONEY BEE CULINARY INSTITUTE
          </div>
        </div>

        <!-- Details -->
        <div
          class="absolute"
          style="left: 5.79%; top: 33.33%; width: 44.63%;"
        >
          <div class="grid grid-cols-[42%_58%] items-baseline leading-none mb-[5.5%]">
            <span class="font-bold text-gray-900" style="font-size: clamp(8px, 1.18vw, 14px);">STUDENT ID:</span>
            <span class="font-black text-gray-900 truncate uppercase" style="font-size: clamp(9px, 1.38vw, 16px);">{{ studentId.student_number || 'N/A' }}</span>
          </div>

          <div class="grid grid-cols-[42%_58%] items-baseline leading-none mb-[5.5%]">
            <span class="font-bold text-gray-900" style="font-size: clamp(8px, 1.18vw, 14px);">PROGRAMME:</span>
            <span class="font-black text-gray-900 truncate uppercase" style="font-size: clamp(8px, 1.28vw, 15px);">{{ studentId.programme || 'N/A' }}</span>
          </div>

          <div class="grid grid-cols-[42%_58%] items-baseline leading-none mb-[5.5%]">
            <span class="font-bold text-gray-900" style="font-size: clamp(8px, 1.18vw, 14px);">YEAR:</span>
            <span class="font-black text-gray-900 truncate uppercase" style="font-size: clamp(9px, 1.38vw, 16px);">{{ studentId.year || 'N/A' }}</span>
          </div>

          <div class="grid grid-cols-[42%_58%] items-baseline leading-none">
            <span class="font-bold text-gray-900" style="font-size: clamp(8px, 1.18vw, 14px);">COHORT:</span>
            <span class="font-black text-gray-900 truncate uppercase" style="font-size: clamp(9px, 1.38vw, 16px);">{{ studentId.cohort || 'N/A' }}</span>
          </div>
        </div>

        <!-- Photo -->
        <div
          class="absolute overflow-hidden rounded bg-gray-200 ring-1 ring-gray-300"
          style="right: 8.26%; top: 32.03%; width: 19.83%; height: 35.95%;"
        >
          <img
            v-if="studentId.photo_url"
            :src="studentId.photo_url"
            alt="Student photo"
            class="w-full h-full object-cover"
          />
          <div
            v-else
            class="w-full h-full flex items-center justify-center bg-gray-200 text-orange-700 font-black"
            style="font-size: clamp(20px, 4vw, 48px);"
          >
            {{ studentId.initials }}
          </div>
        </div>

        <!-- Student name -->
        <div
          class="absolute text-center text-amber-500 font-black uppercase truncate"
          style="right: 6.2%; top: 69.28%; width: 24%; font-size: clamp(12px, 2.05vw, 24px);"
        >
          {{ studentId.name || 'Name Here' }}
        </div>

        <!-- QR -->
        <div
          class="absolute bg-white rounded border border-gray-200 p-1"
          style="right: 8.26%; bottom: 5.88%; width: 16.12%; aspect-ratio: 1;"
        >
          <img
            v-if="studentId.qr_code"
            :src="studentId.qr_code"
            alt="Student verification QR code"
            class="w-full h-full"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-xs text-gray-400">QR</div>
        </div>

        <!-- Signature -->
        <div
          class="absolute text-gray-900"
          style="left: 6.2%; bottom: 7.2%; font-size: clamp(8px, 1.05vw, 12px);"
        >
          Authorize Signature
        </div>

        <div
          class="absolute left-[6.2%] text-gray-400"
          style="bottom: 2.4%; font-size: clamp(5px, .65vw, 8px);"
        >
          This card is the property of Honey Bee Culinary Institute. Return to reception if found.
        </div>
      </div>

      <p class="text-center text-xs text-gray-400 mt-4">
        This card is the property of Honey Bee Culinary Institute. Return to reception if found.
      </p>
    </div>
  </HiveLayout>
</template>
