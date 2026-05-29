<template>
  <div class="min-h-screen bg-gradient-to-br from-amber-700 to-amber-900 flex items-center justify-center p-4">
    <!-- Student ID Card -->
    <div class="w-full max-w-md">
      <div class="bg-gradient-to-br from-amber-500 to-amber-700 text-white rounded-2xl shadow-2xl overflow-hidden">
        <!-- Header bar -->
        <div class="bg-amber-900/40 px-6 py-3 flex justify-between items-center">
          <div class="text-xs font-bold tracking-wider uppercase opacity-80">HBCI — Honey Bee Culinary Institute</div>
          <div class="text-xs opacity-60">{{ currentYear }}</div>
        </div>

        <!-- Main card body -->
        <div class="p-6">
          <div class="flex items-start gap-4">
            <!-- Photo -->
            <div class="w-20 h-20 rounded-xl overflow-hidden bg-amber-900/30 border-2 border-amber-200 flex-shrink-0">
              <img v-if="studentId.photo" :src="studentId.photo" :alt="studentId.name"
                class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-2xl">👤</div>
            </div>

            <!-- Info -->
            <div class="flex-1">
              <h2 class="font-bold text-lg leading-tight">{{ studentId.name }}</h2>
              <p class="text-amber-100 text-sm mt-0.5">{{ studentId.email }}</p>
              <p class="text-amber-200 text-xs mt-1">{{ studentId.programme }}</p>
            </div>
          </div>

          <!-- Stats row -->
          <div class="grid grid-cols-3 gap-3 mt-5">
            <div class="bg-amber-900/30 rounded-lg p-3 text-center">
              <div class="text-xs text-amber-200 mb-0.5">Student No.</div>
              <div class="font-bold text-sm truncate">{{ studentId.student_number || 'N/A' }}</div>
            </div>
            <div class="bg-amber-900/30 rounded-lg p-3 text-center">
              <div class="text-xs text-amber-200 mb-0.5">Cohort</div>
              <div class="font-bold text-sm truncate">{{ studentId.cohort || 'N/A' }}</div>
            </div>
            <div class="bg-amber-900/30 rounded-lg p-3 text-center">
              <div class="text-xs text-amber-200 mb-0.5">ID Valid</div>
              <div class="font-bold text-sm">{{ currentYear }}</div>
            </div>
          </div>

          <!-- QR Code -->
          <div class="flex justify-center mt-5">
            <div class="bg-white rounded-xl p-3">
              <canvas ref="qrCanvas" class="w-28 h-28"></canvas>
            </div>
          </div>
          <p class="text-center text-xs text-amber-200 mt-2">Scan to verify student identity</p>
        </div>

        <!-- Footer -->
        <div class="bg-amber-900/30 px-6 py-3">
          <p class="text-center text-xs text-amber-200">
            This card is property of HBCI. Return to reception if found.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const studentId = computed(() => page.props.student_id || {});
const currentYear = new Date().getFullYear();
const qrCanvas = ref(null);

onMounted(async () => {
  if (typeof window === 'undefined' || !qrCanvas.value || !studentId.value.qr_data) return;
  const QRCode = (await import('qrcode')).default;
  await QRCode.toCanvas(qrCanvas.value, String(studentId.value.qr_data), {
    width: 112,
    color: { dark: '#92400E', light: '#FFFFFF' },
  });
});
</script>