<template>
  <IntranetLayout>
    <h1 class="text-2xl font-bold mb-4">Programmes & Modules</h1>
    <div class="flex gap-4">
      <div class="w-1/2">
        <h2>Add Programme</h2>
        <form @submit.prevent="addProgramme" class="space-y-2">
          <input v-model="programmeForm.name" placeholder="Name" class="w-full border p-2" required />
          <textarea v-model="programmeForm.description" placeholder="Description" class="w-full border p-2"></textarea>
          <input type="number" v-model="programmeForm.duration_years" placeholder="Duration (years)" class="w-full border p-2" />
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        </form>
      </div>
      <div class="w-1/2">
        <h2>Add Module</h2>
        <form @submit.prevent="addModule" class="space-y-2">
          <select v-model="moduleForm.programme_id" class="w-full border p-2">
            <option v-for="p in programmes" :value="p.id">{{ p.name }}</option>
          </select>
          <input v-model="moduleForm.name" placeholder="Module name" class="w-full border p-2" required />
          <input v-model="moduleForm.code" placeholder="Code" class="w-full border p-2" required />
          <textarea v-model="moduleForm.description" placeholder="Description" class="w-full border p-2"></textarea>
          <input type="number" v-model="moduleForm.credits" placeholder="Credits" class="w-full border p-2" />
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        </form>
      </div>
    </div>
    <div class="mt-6">
      <h2>Existing Programmes</h2>
      <div v-for="p in programmes" :key="p.id" class="mb-2">
        <strong>{{ p.name }}</strong> ({{ p.duration_years }} years)
        <ul class="ml-4 list-disc">
          <li v-for="m in p.modules" :key="m.id">{{ m.name }} ({{ m.code }})</li>
        </ul>
      </div>
    </div>
  </IntranetLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';
const props = defineProps({ programmes: Array });
const programmeForm = useForm({ name: '', description: '', duration_years: 1 });
const moduleForm = useForm({ programme_id: '', name: '', code: '', description: '', credits: 3 });
const addProgramme = () => programmeForm.post(route('admin.programmes.store'));
const addModule = () => moduleForm.post(route('admin.modules.store'));
</script>
