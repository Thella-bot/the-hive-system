<template>
  <IntranetLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-4">Import Users from CSV</h1>
      <form @submit.prevent="submit">
        <input type="file" @input="form.csv_file = $event.target.files[0]" accept=".csv" />
        <button class="mt-2 px-4 py-2 bg-blue-600 text-white rounded">Upload</button>
      </form>
    </div>
  </IntranetLayout>
</template>

<script setup>
import { Inertia } from '@inertiajs/inertia';
import { useForm } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';

const form = useForm({ csv_file: null });

const submit = () => {
  form.post(route('admin.import-users.post'));
};
</script>