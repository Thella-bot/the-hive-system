<template>
  <HiveLayout title="New Application">
    <div class="max-w-md mx-auto bg-white rounded-xl border border-gray-200 dark:border-gray-700 p-8">
      <form @submit.prevent="submit">
        <div class="mb-6">
          <label for="programme" class="block text-sm font-medium text-gray-700">Programme</label>
          <select id="programme" v-model="form.programme_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option v-for="programme in programmes" :key="programme.id" :value="programme.id">{{ programme.name }}</option>
          </select>
          <p v-if="form.errors.programme_id" class="mt-2 text-sm text-red-600">{{ form.errors.programme_id }}</p>
        </div>
        <div class="flex justify-end">
          <button type="submit" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 active:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition disabled:opacity-60" :disabled="form.processing">Apply</button>
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