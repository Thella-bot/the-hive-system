<template>
  <IntranetLayout>
    <h1 class="text-2xl font-bold mb-4">My Profile</h1>
    <form @submit.prevent="form.post(route('intranet.profile.update'))" class="max-w-md space-y-4">
      <div>
        <label>First Name</label>
        <input v-model="form.first_name" class="w-full border rounded p-2" />
      </div>
      <div>
        <label>Last Name</label>
        <input v-model="form.last_name" class="w-full border rounded p-2" />
      </div>
      <div>
        <label>Date of Birth</label>
        <input type="date" v-model="form.date_of_birth" class="w-full border rounded p-2" />
      </div>
      <div>
        <label>Phone</label>
        <input v-model="form.phone" class="w-full border rounded p-2" />
      </div>
      <div>
        <label>Address</label>
        <textarea v-model="form.address" class="w-full border rounded p-2"></textarea>
      </div>
      <div>
        <label>Emergency Contact Name</label>
        <input v-model="form.emergency_contact_name" class="w-full border rounded p-2" />
      </div>
      <div>
        <label>Emergency Contact Phone</label>
        <input v-model="form.emergency_contact_phone" class="w-full border rounded p-2" />
      </div>
      <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Save</button>
    </form>
  </IntranetLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';
const props = defineProps({ profile: Object });
const form = useForm({
  first_name: props.profile?.first_name ?? '',
  last_name: props.profile?.last_name ?? '',
  date_of_birth: props.profile?.date_of_birth ?? '',
  phone: props.profile?.phone ?? '',
  address: props.profile?.address ?? '',
  emergency_contact_name: props.profile?.emergency_contact_name ?? '',
  emergency_contact_phone: props.profile?.emergency_contact_phone ?? '',
});
</script>