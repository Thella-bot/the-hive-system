<template>
  <HiveLayout title="Create Module" description="Add a new module to the system.">
    <div class="max-w-2xl mx-auto">
      <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-6">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Module Name</label>
          <input id="name" v-model="form.name" type="text" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
          <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
        </div>

        <div>
          <label for="code" class="block text-sm font-medium text-gray-700">Module Code</label>
          <input id="code" v-model="form.code" type="text" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
          <div v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</div>
        </div>

        <div>
          <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
          <textarea id="description" v-model="form.description" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"></textarea>
          <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
        </div>

        <div>
          <label for="credits" class="block text-sm font-medium text-gray-700">Credits</label>
          <input id="credits" v-model="form.credits" type="number" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
          <div v-if="form.errors.credits" class="text-red-500 text-xs mt-1">{{ form.errors.credits }}</div>
        </div>

        <div>
          <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
          <select id="department_id" v-model="form.department_id" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white">
            <option value="">Select a department</option>
            <option v-for="department in departments" :key="department.id" :value="department.id">
              {{ department.name }}
            </option>
          </select>
          <div v-if="form.errors.department_id" class="text-red-500 text-xs mt-1">{{ form.errors.department_id }}</div>
        </div>

        <div>
          <label for="programme_id" class="block text-sm font-medium text-gray-700">Programme</label>
          <select id="programme_id" v-model="form.programme_id" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white">
            <option value="">Select a programme</option>
            <option v-for="programme in programmes" :key="programme.id" :value="programme.id">
              {{ programme.name }}
            </option>
          </select>
          <div v-if="form.errors.programme_id" class="text-red-500 text-xs mt-1">{{ form.errors.programme_id }}</div>
        </div>

        <div class="flex justify-end">
          <button type="submit" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 active:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition disabled:opacity-60" :disabled="form.processing">
            Create Module
          </button>
        </div>
      </form>
    </div>
  </HiveLayout>
</template>

<script setup>
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  departments: Array,
  programmes: Array,
});

const form = useForm({
  name: '',
  code: '',
  description: '',
  credits: '',
  department_id: '',
  programme_id: '',
});

const submit = () => {
  form.post(route('hive.modules.store'));
};
</script>