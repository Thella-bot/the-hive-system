<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { ArrowDownTrayIcon, ArrowLeftIcon, ArrowsRightLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  student_id: { type: Object, required: true },
  can_manage: { type: Boolean, default: false },
  download_url: { type: String, required: true },
});

const studentId = computed(() => props.student_id || {});
const showBack = ref(false);
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

        <div class="flex items-center gap-3">
          <button
            @click="showBack = !showBack"
            class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
          >
            <ArrowsRightLeftIcon class="w-4 h-4" />
            {{ showBack ? 'Show Front' : 'Show Back' }}
          </button>

          <a
            :href="download_url"
            class="inline-flex items-center gap-2 bg-hbci-gold hover:bg-hbci-gold-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
          >
            <ArrowDownTrayIcon class="w-4 h-4" />
            Download PDF
          </a>
        </div>
      </div>

      <!-- Card flip container -->
      <div class="perspective-1000" style="perspective: 1000px;">
        <div
          class="relative mx-auto transition-transform duration-700 ease-in-out"
          style="width: min(100%, 726px); aspect-ratio: 242 / 153; transform-style: preserve-3d;"
          :style="{ transform: showBack ? 'rotateY(180deg)' : 'rotateY(0deg)' }"
        >
          <!-- FRONT OF CARD -->
          <div
            class="absolute inset-0 overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5"
            style="backface-visibility: hidden;"
          >
            <!-- Subtle guilloche pattern overlay -->
            <div class="absolute inset-0 opacity-[0.015]" style="background: repeating-linear-gradient(45deg, transparent, transparent 2px, #f5c842 2px, #f5c842 4px);"></div>

            <!-- Gold accent stripe -->
            <div class="absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b from-hbci-gold via-hbci-warm-400 to-hbci-gold-dark"></div>

            <!-- Header -->
            <div
              class="absolute inset-x-0 top-0 flex items-center"
              style="height: 22%; padding-left: 6%; padding-right: 4%; background: linear-gradient(135deg, #1f2937 0%, #374151 100%);"
            >
              <img
                src="/images/hbci-logo.png"
                alt="Honey Bee Culinary Institute"
                class="h-auto object-contain brightness-0 invert"
                style="width: 11%; max-height: 65%;"
              />
              <div class="ml-3 flex flex-col">
                <span
                  class="text-hbci-gold font-black tracking-wider uppercase leading-none truncate"
                  style="font-size: clamp(11px, 1.8vw, 22px);"
                >
                  Honey Bee
                </span>
                <span
                  class="text-white font-semibold tracking-wide uppercase leading-none truncate"
                  style="font-size: clamp(7px, 1vw, 12px);"
                >
                  Culinary Institute
                </span>
              </div>
              <div class="ml-auto text-right">
                <span
                  class="text-hbci-gold font-bold uppercase tracking-wider"
                  style="font-size: clamp(6px, 0.75vw, 9px);"
                >
                  Student Identification
                </span>
              </div>
            </div>

            <!-- Main content area -->
            <div class="absolute inset-x-0 bottom-0" style="top: 22%; padding: 0 5%;">
              <div class="flex h-full gap-4">
                <!-- Left: Details -->
                <div class="flex-1 flex flex-col justify-center">
                  <!-- Student name -->
                  <div class="mb-2">
                    <span
                      class="font-black text-gray-900 uppercase tracking-wide truncate block"
                      style="font-size: clamp(11px, 1.6vw, 20px); line-height: 1.1;"
                    >
                      {{ studentId.name || 'Name Here' }}
                    </span>
                  </div>

                  <!-- Details grid -->
                  <div class="space-y-1">
                    <div class="flex items-baseline gap-2">
                      <span class="text-gray-500 font-semibold uppercase" style="font-size: clamp(6px, 0.72vw, 8.5px); width: 28%;">ID No.</span>
                      <span class="text-gray-900 font-bold uppercase" style="font-size: clamp(7px, 0.85vw, 10px);">{{ studentId.student_number || 'N/A' }}</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                      <span class="text-gray-500 font-semibold uppercase" style="font-size: clamp(6px, 0.72vw, 8.5px); width: 28%;">Programme</span>
                      <span class="text-gray-900 font-bold uppercase truncate" style="font-size: clamp(6px, 0.78vw, 9px);">{{ studentId.programme || 'N/A' }}</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                      <span class="text-gray-500 font-semibold uppercase" style="font-size: clamp(6px, 0.72vw, 8.5px); width: 28%;">Cohort</span>
                      <span class="text-gray-900 font-bold uppercase" style="font-size: clamp(7px, 0.85vw, 10px);">{{ studentId.cohort || 'N/A' }}</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                      <span class="text-gray-500 font-semibold uppercase" style="font-size: clamp(6px, 0.72vw, 8.5px); width: 28%;">Year</span>
                      <span class="text-gray-900 font-bold uppercase" style="font-size: clamp(7px, 0.85vw, 10px);">{{ studentId.year || 'N/A' }}</span>
                    </div>
                    <div v-if="studentId.valid_until" class="flex items-baseline gap-2">
                      <span class="text-gray-500 font-semibold uppercase" style="font-size: clamp(6px, 0.72vw, 8.5px); width: 28%;">Valid Until</span>
                      <span class="text-hbci-gold-dark font-bold uppercase" style="font-size: clamp(7px, 0.85vw, 10px);">{{ studentId.valid_until }}</span>
                    </div>
                  </div>
                </div>

                <!-- Right: Photo & QR -->
                <div class="flex flex-col items-center justify-center gap-2" style="width: 32%;">
                  <!-- Photo -->
                  <div
                    class="overflow-hidden rounded-lg shadow-inner"
                    style="width: 100%; aspect-ratio: 4/3.2; box-shadow: inset 0 0 0 2px rgba(245, 200, 66, 0.3);"
                  >
                    <img
                      v-if="studentId.photo_url"
                      :src="studentId.photo_url"
                      alt="Student photo"
                      class="w-full h-full object-cover"
                    />
                    <div
                      v-else
                      class="w-full h-full flex items-center justify-center bg-gradient-to-br from-hbci-gold-light to-hbci-warm-200 text-hbci-gold-dark font-black"
                      style="font-size: clamp(16px, 3vw, 36px);"
                    >
                      {{ studentId.initials }}
                    </div>
                  </div>

                  <!-- QR Code -->
                  <div class="flex items-center gap-1.5 w-full">
                    <div
                      class="bg-white rounded border border-gray-200 p-0.5 flex-shrink-0 shadow-sm"
                      style="width: 30%; aspect-ratio: 1;"
                    >
                      <img
                        v-if="studentId.qr_code"
                        :src="studentId.qr_code"
                        alt="Verification QR code"
                        class="w-full h-full"
                      />
                      <div v-else class="w-full h-full flex items-center justify-center text-[6px] text-gray-400">QR</div>
                    </div>
                    <span class="text-gray-400 uppercase leading-none" style="font-size: clamp(4px, 0.5vw, 6px);">Scan to verify</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer accent -->
            <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-hbci-gold via-hbci-warm-400 to-hbci-gold-dark"></div>
          </div>

          <!-- BACK OF CARD -->
          <div
            class="absolute inset-0 overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5"
            style="backface-visibility: hidden; transform: rotateY(180deg);"
          >
            <!-- Background pattern -->
            <div class="absolute inset-0 opacity-[0.02]" style="background: repeating-linear-gradient(-45deg, transparent, transparent 3px, #1f2937 3px, #1f2937 6px);"></div>

            <!-- Magnetic stripe -->
            <div
              class="absolute top-0 inset-x-0 bg-gray-800"
              style="height: 22%;"
            ></div>

            <!-- Content -->
            <div class="absolute inset-0" style="padding-top: 28%; padding-left: 5%; padding-right: 5%;">
              <div class="flex flex-col h-full">
                <!-- Signature line -->
                <div class="mb-4">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="text-gray-500 font-semibold uppercase" style="font-size: clamp(6px, 0.7vw, 8px);">Holder's Signature</span>
                  </div>
                  <div class="border-b-2 border-dashed border-gray-300" style="width: 55%;"></div>
                </div>

                <!-- Contact info -->
                <div class="space-y-1.5 mb-auto">
                  <div class="flex items-center gap-2">
                    <span class="text-gray-500 font-semibold uppercase" style="font-size: clamp(5px, 0.6vw, 7px); width: 20%;">Email</span>
                    <span class="text-gray-800 font-medium" style="font-size: clamp(5px, 0.65vw, 7.5px);">{{ studentId.email || 'N/A' }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-gray-500 font-semibold uppercase" style="font-size: clamp(5px, 0.6vw, 7px); width: 20%;">Issued</span>
                    <span class="text-gray-800 font-medium" style="font-size: clamp(5px, 0.65vw, 7.5px);">{{ studentId.year || 'N/A' }}</span>
                  </div>
                  <div v-if="studentId.valid_until" class="flex items-center gap-2">
                    <span class="text-gray-500 font-semibold uppercase" style="font-size: clamp(5px, 0.6vw, 7px); width: 20%;">Expires</span>
                    <span class="text-hbci-gold-dark font-bold" style="font-size: clamp(5px, 0.65vw, 7.5px);">{{ studentId.valid_until }}</span>
                  </div>
                </div>

                <!-- Barcode / ID number -->
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <!-- Simulated barcode -->
                    <div class="flex gap-px h-6">
                      <div v-for="i in 40" :key="i" class="bg-gray-900" :style="{ width: (i % 3 === 0 ? 2 : 1) + 'px' }"></div>
                    </div>
                    <span class="text-gray-600 font-mono font-bold tracking-widest" style="font-size: clamp(7px, 0.8vw, 9px);">
                      {{ studentId.student_number || 'N/A' }}
                    </span>
                  </div>

                  <!-- Institute footer -->
                  <div class="text-right">
                    <span class="text-hbci-gold font-bold uppercase tracking-wider" style="font-size: clamp(5px, 0.6vw, 7px);">HBCI</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Bottom accent -->
            <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-gray-800 via-gray-600 to-gray-800"></div>
          </div>
        </div>
      </div>

      <!-- Card info -->
      <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
        <span>This card is the property of Honey Bee Culinary Institute. Return to reception if found.</span>
        <span v-if="studentId.valid_until" class="text-hbci-gold-dark font-medium">
          Valid until {{ studentId.valid_until }}
        </span>
      </div>

      <!-- Usage notes -->
      <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Card Usage</h3>
        <ul class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
          <li>• This card must be worn visibly while on campus premises</li>
          <li>• Present this card when accessing library, laboratories, and examination halls</li>
          <li>• Report lost or stolen cards to the Student Affairs office immediately</li>
          <li>• This card remains the property of Honey Bee Culinary Institute</li>
        </ul>
      </div>
    </div>
  </HiveLayout>
</template>
