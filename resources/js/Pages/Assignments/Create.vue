<template>
  <IntranetLayout>
    <h1 class="text-2xl font-bold mb-4">Create Assignment</h1>
    <form @submit.prevent="submit" class="max-w-lg space-y-4">
      <div>
        <label>Module</label>
        <select v-model="form.module_id" class="w-full border rounded p-2">
          <option v-for="m in modules" :value="m.id">{{ m.name }}</option>
        </select>
      </div>
      <div><label>Title</label><input v-model="form.title" class="w-full border rounded p-2" required /></div>
      <div><label>Description</label><textarea v-model="form.description" class="w-full border rounded p-2"></textarea></div>
      <div><label>Due Date</label><input type="datetime-local" v-model="form.due_date" class="w-full border rounded p-2" required /></div>
      <div><label>Max File Size (KB)</label><input type="number" v-model="form.max_file_size" class="w-full border rounded p-2" /></div>
      <div><label>Allowed File Types (comma separated)</label><input v-model="form.allowed_types" class="w-full border rounded p-2" /></div>
      <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Create</button>
    </form>
  </IntranetLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';

const props = defineProps({ modules: Array });
const form = useForm({
  module_id: props.modules[0]?.id ?? '',
  title: '',
  description: '',
  due_date: '',
  max_file_size: 10240,
  allowed_types: 'pdf,docx,zip',
});
const submit = () => form.post(route('intranet.assignments.store'));
</script>