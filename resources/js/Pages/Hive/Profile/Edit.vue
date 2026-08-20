<template>
  <HiveLayout>
    <h1 class="text-2xl font-bold mb-4">My Profile</h1>
    <form @submit.prevent="form.post(route('hive.profile.update'))" class="max-w-md space-y-4">
      <div>
        <label>First Name</label>
        <input v-model="form.first_name" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" />
      </div>
      <div>
        <label>Last Name</label>
        <input v-model="form.last_name" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" />
      </div>
      <div>
        <label>Date of Birth</label>
        <input type="date" v-model="form.date_of_birth" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" />
      </div>
      <div>
        <label>Phone</label>
        <input v-model="form.phone" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" />
      </div>
      <div>
        <label>Address</label>
        <textarea v-model="form.address" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white"></textarea>
      </div>
      <div>
        <label>Emergency Contact Name</label>
        <input v-model="form.emergency_contact_name" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" />
      </div>
      <div>
        <label>Emergency Contact Phone</label>
        <input v-model="form.emergency_contact_phone" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" />
      </div>

      <div>
        <label>Emergency Contact Relationship</label>
        <input v-model="form.emergency_contact_relationship" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" />
      </div>

      <div>
        <label>Dietary Restrictions</label>
        <textarea v-model="form.dietary_restrictions" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white"></textarea>
      </div>

      <div>
        <label>Bio</label>
        <textarea v-model="form.bio" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white"></textarea>
      </div>

      <div>
        <label>Specialization</label>
        <input v-model="form.specialization" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" />
      </div>

      <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-amber-700 transition-colors">Save</button>
    </form>
  </HiveLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
  profile: Object,
  departments: Array,
  cohorts: Array,
});

const form = useForm({
  first_name: props.profile?.first_name ?? '',
  last_name: props.profile?.last_name ?? '',
  date_of_birth: props.profile?.date_of_birth ?? '',
  phone: props.profile?.phone ?? '',
  address: props.profile?.address ?? '',
  emergency_contact_name: props.profile?.emergency_contact_name ?? '',
  emergency_contact_phone: props.profile?.emergency_contact_phone ?? '',
  emergency_contact_relationship: props.profile?.emergency_contact_relationship ?? '',
  dietary_restrictions: props.profile?.dietary_restrictions ?? '',
  bio: props.profile?.bio ?? '',
  specialization: props.profile?.specialization ?? '',
});
</script>
