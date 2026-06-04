<template>
  <HiveLayout title="Programme Waitlist">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">⏳ Programme Waitlist</h1>
      <button v-if="!isAdmin && !isOnWaitlist" @click="showJoin = !showJoin"
        class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm">
        Join Waitlist
      </button>
    </div>

    <!-- Join form for students -->
    <div v-if="showJoin" class="bg-white rounded-xl border p-6 mb-6 max-w-md">
      <h3 class="font-semibold mb-3">Join Programme Waitlist</h3>
      <form @submit.prevent="joinWaitlist">
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Programme</label>
          <select v-model="joinForm.programme_id" class="w-full border-gray-300 rounded-md shadow-sm">
            <option value="">Select programme...</option>
            <option v-for="p in programmes" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>
        <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Join</button>
      </form>
    </div>

    <!-- Student own waitlist -->
    <div v-if="!isAdmin && waitlists.length">
      <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">Your Waitlist Positions</h2>
      <div class="space-y-3 mb-8">
        <div v-for="w in waitlists" :key="w.id" class="bg-white rounded-xl border p-5 flex justify-between items-center">
          <div>
            <h3 class="font-semibold text-gray-900">{{ w.programme?.name }}</h3>
            <p class="text-sm text-gray-500">Position #{{ w.position }} · Joined {{ formatDate(w.joined_at) }}</p>
          </div>
          <button @click="leave(w.id)" class="text-red-600 text-sm hover:text-red-700">Leave</button>
        </div>
      </div>
    </div>

    <!-- Admin view -->
    <div v-if="isAdmin" class="bg-white rounded-xl border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Student</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Programme</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Position</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Joined</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="w in (waitlists.data || waitlists)" :key="w.id">
            <td class="px-6 py-4">{{ w.user?.name }}</td>
            <td class="px-6 py-4">{{ w.programme?.name }}</td>
            <td class="px-6 py-4">#{{ w.position }}</td>
            <td class="px-6 py-4 text-gray-500">{{ formatDate(w.joined_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({ waitlists: [Array, Object], programmes: Array });
const showJoin = ref(false);
const roles = computed(() => usePage().props.auth?.user?.roles || []);
const isAdmin = computed(() => roles.value.some(r => ['super-admin', 'school-admin'].includes(r)));
const joinForm = ref({ programme_id: '' });

const isOnWaitlist = computed(() => {
  return Array.isArray(props.waitlists) && props.waitlists.length > 0;
});

const formatDate = (d) => dayjs(d).format('MMM D, YYYY');

const joinWaitlist = () => {
  router.post(route('hive.waitlist.join'), joinForm.value);
};

const leave = (id) => {
  if (confirm('Leave this waitlist?')) router.delete(route('hive.waitlist.leave', { waitlist: id }));
};
</script>