<template>
  <HiveLayout title="New Application">
    <h1 class="text-2xl font-semibold mb-6">New Application</h1>
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
      <form @submit.prevent="submit">
        <div class="mb-6">
          <label for="programme" class="block text-sm font-medium text-gray-700">Programme</label>
          <select id="programme" v-model="form.programme_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option v-for="programme in programmes" :key="programme.id" :value="programme.id">{{ programme.name }}</option>
          </select>
          <p v-if="form.errors.programme_id" class="mt-2 text-sm text-red-600">{{ form.errors.programme_id }}</p>
        </div>
        <div class="flex justify-end">
          <button type="submit" class="btn btn-primary" :disabled="form.processing">Apply</button>
        </div>
      </form>
    </div>
  </HiveLayout>
</template>

<script setup>
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  programmes: Array,
});

const form = useForm({
  programme_id: props.programmes.length > 0 ? props.programmes[0].id : null,
});

const submit = () => {
  form.post(route('hive.applications.store'));
};
</script>