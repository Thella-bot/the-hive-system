<template>
  <HiveLayout title="Edit Module" description="Update the details of a module.">
    <div class="max-w-2xl mx-auto">
      <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-6">
        <div>
          <label for="name" class="form-label">Module Name</label>
          <input id="name" v-model="form.name" type="text" class="form-input" />
          <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
        </div>

        <div>
          <label for="code" class="form-label">Module Code</label>
          <input id="code" v-model="form.code" type="text" class="form-input" />
          <div v-if="form.errors.code" class="form-error">{{ form.errors.code }}</div>
        </div>

        <div>
          <label for="description" class="form-label">Description</label>
          <textarea id="description" v-model="form.description" class="form-input"></textarea>
          <div v-if="form.errors.description" class="form-error">{{ form.errors.description }}</div>
        </div>

        <div>
          <label for="credits" class="form-label">Credits</label>
          <input id="credits" v-model="form.credits" type="number" class="form-input" />
          <div v-if="form.errors.credits" class="form-error">{{ form.errors.credits }}</div>
        </div>

        <div>
          <label for="department_id" class="form-label">Department</label>
          <select id="department_id" v-model="form.department_id" class="form-input">
            <option value="">Select a department</option>
            <option v-for="department in departments" :key="department.id" :value="department.id">
              {{ department.name }}
            </option>
          </select>
          <div v-if="form.errors.department_id" class="form-error">{{ form.errors.department_id }}</div>
        </div>

        <div>
          <label for="programme_id" class="form-label">Programme</label>
          <select id="programme_id" v-model="form.programme_id" class="form-input">
            <option value="">Select a programme</option>
            <option v-for="programme in programmes" :key="programme.id" :value="programme.id">
              {{ programme.name }}
            </option>
          </select>
          <div v-if="form.errors.programme_id" class="form-error">{{ form.errors.programme_id }}</div>
        </div>

        <div class="flex justify-between items-center">
          <button type="button" @click="destroy" class="text-red-600 hover:underline">Delete Module</button>
          <button type="submit" class="btn-primary" :disabled="form.processing">
            Update Module
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
  module: Object,
  departments: Array,
  programmes: Array,
});

const form = useForm({
  name: props.module.name,
  code: props.module.code,
  description: props.module.description,
  credits: props.module.credits,
  department_id: props.module.department_id,
  programme_id: props.module.programme_id,
});

const submit = () => {
  form.put(route('hive.modules.update', props.module.id));
};

const destroy = () => {
  if (confirm('Are you sure you want to delete this module?')) {
    form.delete(route('hive.modules.destroy', props.module.id));
  }
};
</script>