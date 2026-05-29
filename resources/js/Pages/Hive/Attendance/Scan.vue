<template>
  <HiveLayout title="QR Check-In Scanner" class="max-w-md mx-auto">
    <div class="text-center">
      <div class="text-4xl mb-4">📡</div>
      <h1 class="text-xl font-bold mb-2">Scan QR to Check In</h1>
      <p class="text-gray-500 text-sm mb-6">Point your phone camera at the class QR code</p>

      <div class="bg-black rounded-xl overflow-hidden mb-6" style="min-height: 300px;">
        <div id="qr-reader" class="w-full" style="min-height: 300px;"></div>
        <div v-if="!hasCamera" class="flex items-center justify-center text-white p-12">
          <p>Camera access required. Please allow camera permission.</p>
        </div>
      </div>

      <div v-if="scanResult" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
        <p class="text-green-800 font-medium">✓ Checked in successfully!</p>
        <p class="text-green-600 text-sm">{{ scanResult }}</p>
      </div>

      <div v-if="scanError" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
        <p class="text-red-800 font-medium">✗ {{ scanError }}</p>
      </div>

      <p class="text-xs text-gray-400">Or enter code manually:</p>
      <form @submit.prevent="manualCheckIn" class="mt-2 flex gap-2">
        <input v-model="manualCode" class="flex-1 border rounded p-2 text-sm" placeholder="e.g. EVENT-42" />
        <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded text-sm">Check In</button>
      </form>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const scanResult = ref('');
const scanError = ref('');
const manualCode = ref('');
const hasCamera = ref(true);

let html5QrCode = null;

const onScanSuccess = (decodedText) => {
  scanError.value = '';
  scanResult.value = decodedText;

  router.post(route('hive.attendance.checkin'), {
    code: decodedText,
    method: 'qr',
  }, {
    onSuccess: () => {
      scanResult.value = '✓ Checked in!';
    },
    onError: () => {
      scanError.value = 'Could not verify code. Try again.';
    },
  });
};

onMounted(async () => {
  try {
    html5QrCode = new Html5Qrcode('qr-reader');
    await html5QrCode.start(
      { facingMode: 'environment' },
      { fps: 10, qrbox: 250 },
      onScanSuccess,
      () => {}
    );
  } catch (e) {
    hasCamera.value = false;
  }
});

onUnmounted(() => {
  if (html5QrCode) html5QrCode.stop().catch(() => {});
});

const manualCheckIn = () => {
  if (!manualCode.value.trim()) return;
  router.post(route('hive.attendance.checkin'), {
    code: manualCode.value,
    method: 'manual',
  });
};
</script>