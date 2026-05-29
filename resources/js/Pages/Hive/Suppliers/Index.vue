<template>
  <HiveLayout title="Supplier & Vendor Directory">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">🛒 Supplier & Vendor Directory</h1>
      <button v-if="canCreate" @click="showAdd = !showAdd"
        class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm">
        + Add Supplier
      </button>
    </div>

    <!-- Add form -->
    <div v-if="showAdd" class="bg-white rounded-xl border p-6 mb-6">
      <form @submit.prevent="submitSupplier" class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Name</label>
          <input v-model="form.name" class="w-full border-gray-300 rounded-md shadow-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Category</label>
          <input v-model="form.category" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="food, equipment..." />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Contact Name</label>
          <input v-model="form.contact_name" class="w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Phone</label>
          <input v-model="form.phone" class="w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input v-model="form.email" type="email" class="w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Contract Expiry</label>
          <input type="date" v-model="form.contract_expiry" class="w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium mb-1">Notes</label>
          <textarea v-model="form.notes" rows="2" class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
        </div>
        <div class="col-span-2">
          <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Save Supplier</button>
        </div>
      </form>
    </div>

    <div v-if="suppliers.length === 0" class="bg-white p-8 text-center rounded-xl border">
      <p class="text-gray-500">No suppliers added yet.</p>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Category</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Contact</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Contract Expiry</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Notes</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="s in suppliers" :key="s.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-medium text-gray-900">{{ s.name }}</td>
            <td class="px-6 py-4 text-gray-500"><span class="px-2 py-0.5 bg-gray-100 rounded text-xs">{{ s.category || '—' }}</span></td>
            <td class="px-6 py-4 text-gray-500">
              <div>{{ s.contact_name || '—' }}</div>
              <div class="text-xs text-gray-400">{{ s.phone }} {{ s.email }}</div>
            </td>
            <td class="px-6 py-4">
              <span v-if="s.contract_expiry" :class="isExpiringSoon(s.contract_expiry) ? 'text-red-600 font-medium' : 'text-gray-500'">
                {{ formatDate(s.contract_expiry) }}
              </span>
              <span v-else class="text-gray-400">—</span>
            </td>
            <td class="px-6 py-4 text-gray-500 text-xs">{{ s.notes ? s.notes.substring(0, 60) + '...' : '—' }}</td>
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

const props = defineProps({ suppliers: Array });
const showAdd = ref(false);
const roles = computed(() => usePage().props.auth?.user?.roles || []);
const canCreate = computed(() => roles.value.some(r => ['super-admin', 'school-admin', 'non_academic_staff'].includes(r)));
const form = ref({ name: '', category: '', contact_name: '', phone: '', email: '', contract_expiry: '', notes: '' });

const formatDate = (d) => dayjs(d).format('MMM D, YYYY');
const isExpiringSoon = (d) => dayjs(d).diff(dayjs(), 'month') <= 1;

const submitSupplier = () => {
  router.post(route('hive.suppliers.store'), form.value);
  form.value = { name: '', category: '', contact_name: '', phone: '', email: '', contract_expiry: '', notes: '' };
  showAdd.value = false;
};
</script>