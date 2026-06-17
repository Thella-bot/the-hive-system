<template>
  <HiveLayout title="Suppliers" description="Supplier and vendor directory">
    <template #header-actions>
      <button v-if="canCreate" @click="showAdd = !showAdd"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <PlusIcon class="w-4 h-4" />
        Add Supplier
      </button>
    </template>

    <!-- Add form -->
    <div v-if="showAdd" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
      <form @submit.prevent="submitSupplier" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
          <input v-model="form.name" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
          <input v-model="form.category" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" placeholder="food, equipment..." />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Name</label>
          <input v-model="form.contact_name" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
          <input v-model="form.phone" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
          <input v-model="form.email" type="email" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contract Expiry</label>
          <input type="date" v-model="form.contract_expiry" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500" />
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
          <textarea v-model="form.notes" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500"></textarea>
        </div>
        <div class="md:col-span-2">
          <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm font-medium">Save Supplier</button>
        </div>
      </form>
    </div>

    <div v-if="suppliers.length === 0" class="bg-white dark:bg-gray-800 p-8 text-center rounded-xl border border-gray-200 dark:border-gray-700">
      <div class="w-16 h-16 mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto">
        <BuildingOffice2Icon class="w-8 h-8 text-gray-400" />
      </div>
      <p class="text-gray-500 dark:text-gray-400">No suppliers added yet.</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Name</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Category</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Contact</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Contract Expiry</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">Notes</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="s in suppliers" :key="s.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ s.name }}</td>
            <td class="px-6 py-4"><span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-xs rounded">{{ s.category || '—' }}</span></td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
              <div>{{ s.contact_name || '—' }}</div>
              <div class="text-xs text-gray-400 dark:text-gray-500">{{ s.phone }} {{ s.email }}</div>
            </td>
            <td class="px-6 py-4">
              <span v-if="s.contract_expiry" :class="isExpiringSoon(s.contract_expiry) ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-500 dark:text-gray-400'">
                {{ formatDate(s.contract_expiry) }}
              </span>
              <span v-else class="text-gray-400 dark:text-gray-500">—</span>
            </td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs hidden lg:table-cell">{{ s.notes ? s.notes.substring(0, 60) + '...' : '—' }}</td>
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
import { PlusIcon, BuildingOffice2Icon } from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import { useUser } from '@/composables/useUser';

const props = defineProps({ suppliers: Array });
const showAdd = ref(false);
const { isAdmin, isNonAcademicStaff } = useUser();
const canCreate = computed(() => isAdmin.value || isNonAcademicStaff.value);
const form = ref({ name: '', category: '', contact_name: '', phone: '', email: '', contract_expiry: '', notes: '' });

const formatDate = (d) => dayjs(d).format('MMM D, YYYY');
const isExpiringSoon = (d) => dayjs(d).diff(dayjs(), 'month') <= 1;

const submitSupplier = () => {
  router.post(route('hive.suppliers.store'), form.value);
  form.value = { name: '', category: '', contact_name: '', phone: '', email: '', contract_expiry: '', notes: '' };
  showAdd.value = false;
};
</script>