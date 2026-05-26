<template>
  <HiveLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-4">Import Users from CSV</h1>
      <form @submit.prevent="submit">
        <input type="file" @input="form.csv_file = $event.target.files[0]" accept=".csv" />
        <button class="mt-2 px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">Upload</button>
      </form>
    </div>
  </HiveLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const form = useForm({ csv_file: null });

const submit = () => {
  form.post(route('hive.admin.import-users.store'));
};
</script>