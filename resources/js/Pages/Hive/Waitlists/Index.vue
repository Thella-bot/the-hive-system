<template>
  <HiveLayout title="Waitlist" description="Programme waitlist">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Programme Waitlist</h1>
      <button v-if="!isAdmin && !isOnWaitlist" @click="showJoin = !showJoin"
        class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm font-medium">
        Join Waitlist
      </button>
    </div>

    <!-- Join form for students -->
    <div v-if="showJoin" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6 max-w-md">
      <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Join Programme Waitlist</h3>
      <form @submit.prevent="joinWaitlist">
        <div class="mb-3">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Programme</label>
          <select v-model="joinForm.programme_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500">
            <option value="">Select programme...</option>
            <option v-for="p in programmes" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>
        <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm font-medium">Join</button>
      </form>
    </div>

    <!-- Student own waitlist -->
    <div v-if="!isAdmin && waitlists.length">
      <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">Your Waitlist Positions</h2>
      <div class="space-y-3 mb-8">
        <div v-for="w in waitlists" :key="w.id" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 flex justify-between items-center">
          <div>
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ w.programme?.name }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Position #{{ w.position }} · Joined {{ formatDate(w.joined_at) }}</p>
          </div>
          <button @click="leave(w.id)" class="text-red-600 dark:text-red-400 text-sm hover:text-red-700 dark:hover:text-red-300">Leave</button>
        </div>
      </div>
    </div>

    <!-- Admin view -->
    <div v-if="isAdmin" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Student</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Programme</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Position</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Joined</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="w in (waitlists.data || waitlists)" :key="w.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
            <td class="px-6 py-4 text-gray-900 dark:text-gray-100">{{ w.user?.name }}</td>
            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ w.programme?.name }}</td>
            <td class="px-6 py-4 text-gray-900 dark:text-gray-100">#{{ w.position }}</td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ formatDate(w.joined_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';
import { useUser } from '@/composables/useUser';

const props = defineProps({ waitlists: [Array, Object], programmes: Array });
const showJoin = ref(false);
const { isAdmin } = useUser();
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