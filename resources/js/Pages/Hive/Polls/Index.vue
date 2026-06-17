<template>
  <HiveLayout title="Polls" description="Staff polls and surveys">
    <template #header-actions>
      <button v-if="canCreate" @click="showCreate = !showCreate"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        New Poll
      </button>
    </template>

    <!-- Create form -->
    <div v-if="showCreate" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
      <form @submit.prevent="submitPoll" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Question</label>
          <input v-model="form.question" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" required />
        </div>
        <div class="flex gap-4">
          <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="radio" v-model="form.type" value="binary" class="text-amber-600 focus:ring-amber-500" /> Thumbs up/down
          </label>
          <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="radio" v-model="form.type" value="choice" class="text-amber-600 focus:ring-amber-500" /> Multiple choice
          </label>
        </div>
        <div v-if="form.type === 'choice'">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Options (one per line)</label>
          <textarea v-model="optionsText" rows="4" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500"
            placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expires at <span class="text-gray-400">(optional)</span></label>
          <input type="datetime-local" v-model="form.expires_at" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" />
        </div>
        <div>
          <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm font-medium">Create Poll</button>
        </div>
      </form>
    </div>

    <div v-if="polls.data.length === 0" class="bg-white dark:bg-gray-800 p-8 text-center rounded-xl border border-gray-200 dark:border-gray-700">
      <div class="w-16 h-16 mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto">
        <ChartBarIcon class="w-8 h-8 text-gray-400" />
      </div>
      <p class="text-gray-500 dark:text-gray-400">No active polls.</p>
    </div>

    <div class="space-y-4">
      <div v-for="poll in polls.data" :key="poll.id" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ poll.question }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">by {{ poll.user?.name }} · {{ formatDate(poll.created_at) }}</p>
          </div>
          <span v-if="poll.expires_at" class="text-xs text-gray-400">Expires {{ formatDate(poll.expires_at) }}</span>
        </div>

        <!-- Binary poll -->
        <div v-if="poll.type === 'binary'" class="flex gap-3">
          <button v-for="choice in ['yes', 'no']" :key="choice"
            @click="!hasVoted(poll) && vote(poll.id, choice)"
            :disabled="hasVoted(poll)"
            :class="['flex-1 py-3 rounded-lg border text-sm font-medium transition',
              getVoteClass(poll, choice),
              hasVoted(poll) ? 'cursor-not-allowed opacity-60' : 'hover:bg-gray-50 dark:hover:bg-gray-700']">
            {{ choice === 'yes' ? '👍 Yes' : '👎 No' }}
            <span v-if="poll.vote_counts" class="ml-2">({{ poll.vote_counts[choice] || 0 }})</span>
          </button>
        </div>

        <!-- Choice poll -->
        <div v-else class="space-y-2">
          <button v-for="opt in (poll.options || [])" :key="opt"
            @click="!hasVoted(poll) && vote(poll.id, opt)"
            :disabled="hasVoted(poll)"
            :class="['w-full text-left px-4 py-3 rounded-lg border text-sm transition',
              getVoteClass(poll, opt),
              hasVoted(poll) ? 'cursor-not-allowed opacity-60' : 'hover:bg-gray-50 dark:hover:bg-gray-700']">
            {{ opt }} <span v-if="poll.vote_counts" class="float-right">({{ poll.vote_counts[opt] || 0 }})</span>
          </button>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { ChartBarIcon, PlusIcon } from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import { useUser } from '@/composables/useUser';

const props = defineProps({ polls: Object });
const showCreate = ref(false);
const { isAdmin, isAcademicStaff, isNonAcademicStaff } = useUser();
const canCreate = computed(() => isAdmin.value || isAcademicStaff.value || isNonAcademicStaff.value);
const form = ref({ question: '', type: 'binary', expires_at: '' });
const optionsText = ref('');

const formatDate = (d) => dayjs(d).format('MMM D, YYYY h:mm A');

const hasVoted = (poll) => {
  return poll.user_vote !== undefined;
};

const vote = (pollId, choice) => {
  router.post(route('hive.polls.vote', { poll: pollId }), { choice });
};

const getVoteClass = (poll, choice) => {
  if (poll.user_vote?.choice === choice) {
    return choice === 'yes' ? 'bg-green-100 text-green-800 border-green-300 dark:bg-green-900/40 dark:text-green-300 dark:border-green-700' :
           choice === 'no' ? 'bg-red-100 text-red-800 border-red-300 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700' :
           'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-700';
  }
  return 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600';
};

const submitPoll = () => {
  const data = { ...form.value };
  if (form.value.type === 'choice' && optionsText.value) {
    data.options = optionsText.value.split('\n').filter(o => o.trim());
  }
  router.post(route('hive.polls.store'), data);
  form.value = { question: '', type: 'binary', expires_at: '' };
  optionsText.value = '';
  showCreate.value = false;
};
</script>