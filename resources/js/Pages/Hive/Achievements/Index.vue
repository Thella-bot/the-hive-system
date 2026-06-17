<template>
  <HiveLayout title="Achievements" description="Competitions, awards, and certifications">
    <template #header-actions>
      <button v-if="canCreate" @click="showAdd = true"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        Add Achievement
      </button>
    </template>

    <div v-if="achievements.data.length === 0" class="bg-white dark:bg-gray-800 p-8 text-center rounded-xl border border-gray-200 dark:border-gray-700">
      <div class="w-16 h-16 mb-4 bg-amber-100 dark:bg-amber-900/50 rounded-full flex items-center justify-center mx-auto">
        <TrophyIcon class="w-8 h-8 text-amber-500 dark:text-amber-400" />
      </div>
      <p class="text-gray-500 dark:text-gray-400">No achievements yet.</p>
      <button v-if="canCreate" @click="showAdd = true" class="mt-3 text-amber-600 hover:text-amber-700 text-sm font-medium">
        Add Achievement
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="ach in achievements.data" :key="ach.id"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm hover:shadow-md dark:hover:shadow-gray-900/30 transition">
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center">
            <TrophyIcon class="w-5 h-5 text-amber-600 dark:text-amber-400" />
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ ach.title }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ ach.user?.name }}</p>
          </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
          <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded">{{ formatType(ach.type) }}</span>
          <span v-if="ach.awarded_by">Awarded by: {{ ach.awarded_by }}</span>
          <span v-if="ach.awarded_at">on {{ formatDate(ach.awarded_at) }}</span>
        </div>
        <div v-if="canDelete" class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
          <button @click="remove(ach.id)" class="text-red-600 dark:text-red-400 text-sm hover:text-red-700">Remove</button>
        </div>
      </div>
    </div>

    <div v-if="achievements.links" class="mt-6 flex justify-center gap-2">
      <Link v-for="link in achievements.links" :key="link.label" :href="link.url || '#'"
        :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700']"
        v-html="link.label" />
    </div>

    <!-- Add Achievement Modal -->
    <DialogModal :show="showAdd" @close="showAdd = false">
      <template #title>Add Achievement</template>
      <template #content>
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User</label>
            <select v-model="form.user_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500">
              <option value="">Select user...</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
            <select v-model="form.type" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500">
              <option value="competition">Competition</option>
              <option value="award">Award</option>
              <option value="certification">Certification</option>
              <option value="recognition">Recognition</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
            <input v-model="form.title" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Awarded By</label>
            <input v-model="form.awarded_by" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
            <input type="date" v-model="form.awarded_at" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" />
          </div>
        </div>
      </template>
      <template #footer>
        <button @click="showAdd = false" class="mr-3 text-sm text-gray-600 dark:text-gray-400">Cancel</button>
        <button @click="submitAchievement" class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-amber-700">Save</button>
      </template>
    </DialogModal>
  </HiveLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import { TrophyIcon, PlusIcon } from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import { useUser } from '@/composables/useUser';

const props = defineProps({ achievements: Object, users: Array });
const showAdd = ref(false);
const { isAdmin } = useUser();
const canCreate = computed(() => isAdmin.value);
const canDelete = computed(() => isAdmin.value);

const form = ref({ user_id: '', type: 'competition', title: '', awarded_by: '', awarded_at: '' });

const formatDate = (d) => d ? dayjs(d).format('MMM D, YYYY') : '—';

const typeLabels = {
  competition: 'Competition',
  award: 'Award',
  certification: 'Certification',
  recognition: 'Recognition',
};

const formatType = (t) => typeLabels[t] ?? t?.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) ?? '';

const remove = (id) => {
  if (confirm('Remove this achievement?')) router.delete(route('hive.achievements.destroy', { achievement: id }));
};

const submitAchievement = () => {
  router.post(route('hive.achievements.store'), form.value);
  showAdd.value = false;
};
</script>