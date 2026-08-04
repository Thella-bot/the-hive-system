<template>
  <HiveLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-4">Import Users from CSV</h1>

      <p class="mb-2 text-sm text-gray-600">
        CSV columns:
      </p>
      <code class="block mb-2 p-2 bg-gray-100 rounded text-sm">
        Full Name, Email, Role, Date of Birth, Phone, Address,<br>
        Emergency Contact Name, Emergency Contact Phone, Programme Name,<br>
        Year of Study, Intake Date, Student Number
      </code>

      <p class="mb-4 text-sm text-gray-500">
        Columns 5–11 are optional. For students, intake_date is used to generate the student number.
        If student_number is already provided, it will be used as-is.
      </p>

      <form @submit.prevent="submit">
        <input type="file" @input="form.csv_file = $event.target.files[0]" accept=".csv" />
        <button class="mt-2 px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
          Upload
        </button>
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