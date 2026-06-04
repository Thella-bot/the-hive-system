<template>
  <HiveLayout title="Staff Polls & Surveys">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">📊 Polls & Surveys</h1>
      <button v-if="canCreate" @click="showCreate = !showCreate"
        class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm">
        + New Poll
      </button>
    </div>

    <!-- Create form -->
    <div v-if="showCreate" class="bg-white rounded-xl border p-6 mb-6">
      <form @submit.prevent="submitPoll" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Question</label>
          <input v-model="form.question" class="w-full border-gray-300 rounded-md shadow-sm" required />
        </div>
        <div class="flex gap-4">
          <label class="flex items-center gap-2 text-sm">
            <input type="radio" v-model="form.type" value="binary" /> Thumbs up/down
          </label>
          <label class="flex items-center gap-2 text-sm">
            <input type="radio" v-model="form.type" value="choice" /> Multiple choice
          </label>
        </div>
        <div v-if="form.type === 'choice'">
          <label class="block text-sm font-medium mb-1">Options (one per line)</label>
          <textarea v-model="optionsText" rows="4" class="w-full border-gray-300 rounded-md shadow-sm"
            placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Expires at <span class="text-gray-400">(optional)</span></label>
          <input type="datetime-local" v-model="form.expires_at" class="w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Create Poll</button>
        </div>
      </form>
    </div>

    <div v-if="polls.data.length === 0" class="bg-white p-8 text-center rounded-xl border">
      <p class="text-gray-500">No active polls.</p>
    </div>

    <div class="space-y-4">
      <div v-for="poll in polls.data" :key="poll.id" class="bg-white rounded-xl border p-6">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="font-semibold text-lg text-gray-900">{{ poll.question }}</h3>
            <p class="text-sm text-gray-500">by {{ poll.user?.name }} · {{ formatDate(poll.created_at) }}</p>
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
              hasVoted(poll) ? 'cursor-not-allowed opacity-60' : 'hover:bg-gray-50']">
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
              hasVoted(poll) ? 'cursor-not-allowed opacity-60' : 'hover:bg-gray-50']">
            {{ opt }} <span v-if="poll.vote_counts" class="float-right">({{ poll.vote_counts[opt] || 0 }})</span>
          </button>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({ polls: Object });
const showCreate = ref(false);
const roles = computed(() => usePage().props.auth?.user?.roles || []);
const canCreate = computed(() => roles.value.some(r => ['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff'].includes(r)));
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
    return choice === 'yes' ? 'bg-green-100 text-green-800 border-green-300' :
           choice === 'no' ? 'bg-red-100 text-red-800 border-red-300' :
           'bg-amber-100 text-amber-800 border-amber-300';
  }
  return 'bg-white text-gray-700 border-gray-200';
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