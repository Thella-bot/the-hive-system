<template>
  <HiveLayout title="Achievement Board" description="Competitions, awards, and certifications">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">🏆 Achievement Board</h1>
      <button v-if="canCreate" @click="showAdd = true"
        class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm">
        + Add Achievement
      </button>
    </div>

    <div v-if="achievements.data.length === 0" class="bg-white p-8 text-center rounded-xl border">
      <p class="text-gray-500">No achievements yet.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="ach in achievements.data" :key="ach.id"
        class="bg-white rounded-xl border p-5 shadow-sm hover:shadow-md transition">
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center text-lg">🏆</div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">{{ ach.title }}</h3>
            <p class="text-sm text-gray-500">{{ ach.user?.name }}</p>
          </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-500">
          <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded">{{ ach.type }}</span>
          <span v-if="ach.awarded_by">Awarded by: {{ ach.awarded_by }}</span>
          <span v-if="ach.awarded_at">on {{ formatDate(ach.awarded_at) }}</span>
        </div>
        <div v-if="canDelete" class="mt-3 pt-3 border-t">
          <button @click="remove(ach.id)" class="text-red-600 text-sm hover:text-red-700">Remove</button>
        </div>
      </div>
    </div>

    <div v-if="achievements.links" class="mt-6 flex justify-center gap-2">
      <Link v-for="link in achievements.links" :key="link.label" :href="link.url || '#'"
        :class="['px-3 py-1 rounded', link.active ? 'bg-amber-600 text-white' : 'bg-white text-gray-700']"
        v-html="link.label" />
    </div>

    <!-- Add Achievement Modal -->
    <DialogModal :show="showAdd" @close="showAdd = false">
      <template #title>Add Achievement</template>
      <template #content>
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium mb-1">User</label>
            <select v-model="form.user_id" class="w-full border-gray-300 rounded-md shadow-sm">
              <option value="">Select user...</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Type</label>
            <select v-model="form.type" class="w-full border-gray-300 rounded-md shadow-sm">
              <option value="competition">Competition</option>
              <option value="award">Award</option>
              <option value="certification">Certification</option>
              <option value="recognition">Recognition</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input v-model="form.title" class="w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Awarded By</label>
            <input v-model="form.awarded_by" class="w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Date</label>
            <input type="date" v-model="form.awarded_at" class="w-full border-gray-300 rounded-md shadow-sm" />
          </div>
        </div>
      </template>
      <template #footer>
        <button @click="showAdd = false" class="mr-3 text-sm text-gray-600">Cancel</button>
        <button @click="submitAchievement" class="bg-amber-600 text-white px-4 py-2 rounded text-sm hover:bg-amber-700">Save</button>
      </template>
    </DialogModal>
  </HiveLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import dayjs from 'dayjs';

const props = defineProps({ achievements: Object, users: Array });
const showAdd = ref(false);
const roles = computed(() => usePage().props.auth?.user?.roles || []);
const canCreate = computed(() => roles.value.some(r => ['super-admin', 'school-admin'].includes(r)));
const canDelete = computed(() => roles.value.some(r => ['super-admin', 'school-admin'].includes(r)));

const form = ref({ user_id: '', type: 'competition', title: '', awarded_by: '', awarded_at: '' });

const formatDate = (d) => dayjs(d).format('MMM D, YYYY');

const remove = (id) => {
  if (confirm('Remove this achievement?')) router.delete(route('hive.achievements.destroy', { achievement: id }));
};

const submitAchievement = () => {
  router.post(route('hive.achievements.store'), form.value);
  showAdd.value = false;
};
</script>