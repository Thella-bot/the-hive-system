<template>
  <HiveLayout>
    <h1 class="text-2xl font-bold mb-4">My Profile</h1>
    <form @submit.prevent="submit" class="max-w-md space-y-4">
      <div v-if="profile?.profile_picture_path" class="mb-4">
        <img :src="'/storage/' + profile.profile_picture_path" alt="Profile picture" class="w-24 h-24 rounded-full object-cover" />
      </div>
      <div>
        <label>Profile Picture</label>
        <input type="file" accept="image/*" @change="form.profile_picture = $event.target.files[0]"
          class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" />
        <div v-if="form.errors.profile_picture" class="text-red-500 text-xs mt-1">{{ form.errors.profile_picture }}</div>
      </div>
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
        <label>Gender</label>
        <select v-model="form.gender" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white bg-white">
          <option :value="null">— None —</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
          <option value="Prefer not to say">Prefer not to say</option>
        </select>
      </div>
      <div>
        <label>National ID Number</label>
        <input v-model="form.national_id_number" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition dark:bg-gray-700 dark:text-white" />
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
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
  user: Object,
  profile: Object,
  departments: Array,
  cohorts: Array,
});

const form = reactive({
  first_name: props.profile?.first_name ?? '',
  last_name: props.profile?.last_name ?? '',
  date_of_birth: props.profile?.date_of_birth ?? '',
  gender: props.user?.gender ?? null,
  national_id_number: props.user?.national_id_number ?? '',
  phone: props.profile?.phone ?? '',
  address: props.profile?.address ?? '',
  emergency_contact_name: props.profile?.emergency_contact_name ?? '',
  emergency_contact_phone: props.profile?.emergency_contact_phone ?? '',
  emergency_contact_relationship: props.profile?.emergency_contact_relationship ?? '',
  dietary_restrictions: props.profile?.dietary_restrictions ?? '',
  bio: props.profile?.bio ?? '',
  specialization: props.profile?.specialization ?? '',
  profile_picture: null,
  errors: {},
  processing: false,
});

const submit = async () => {
  form.processing = true;
  const data = new FormData();
  data.append('first_name', form.first_name);
  data.append('last_name', form.last_name);
  data.append('date_of_birth', form.date_of_birth);
  data.append('gender', form.gender || '');
  data.append('national_id_number', form.national_id_number || '');
  data.append('phone', form.phone || '');
  data.append('address', form.address || '');
  data.append('emergency_contact_name', form.emergency_contact_name);
  data.append('emergency_contact_phone', form.emergency_contact_phone);
  data.append('emergency_contact_relationship', form.emergency_contact_relationship);
  data.append('dietary_restrictions', form.dietary_restrictions || '');
  data.append('bio', form.bio || '');
  data.append('specialization', form.specialization || '');
  if (form.profile_picture instanceof File) {
    data.append('profile_picture', form.profile_picture);
  }

  try {
    const response = await fetch(route('hive.profile.update'), {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
      body: data,
    });

    if (response.ok) {
      router.get(route('hive.profile.show'));
    } else {
      const errorData = await response.json();
      if (errorData.errors) {
        form.errors = errorData.errors;
      }
      form.processing = false;
    }
  } catch (error) {
    console.error('Submission failed:', error);
    form.processing = false;
  }
};
</script>
