<template>
  <IntranetLayout>
    <h1 class="text-2xl font-bold mb-4">Upload Document</h1>
    <form @submit.prevent="form.post(route('intranet.documents.store'))" class="max-w-lg space-y-4">
      <div>
        <label class="block text-sm">Title</label>
        <input v-model="form.title" class="w-full border rounded p-2" required />
      </div>
      <div>
        <label class="block text-sm">Description</label>
        <textarea v-model="form.description" class="w-full border rounded p-2"></textarea>
      </div>
      <div>
        <label class="block text-sm">Category</label>
        <input v-model="form.category" class="w-full border rounded p-2" placeholder="e.g., Policies, Recipes" required />
      </div>
      <div>
        <label class="block text-sm">Visible to Roles (leave empty for all)</label>
        <div class="flex gap-2">
          <label v-for="role in ['student','instructor','hr_staff','admin']" :key="role">
            <input type="checkbox" :value="role" v-model="form.visible_to_roles" /> {{ role }}
          </label>
        </div>
      </div>
      <div>
        <label class="block text-sm">File (max 20 MB)</label>
        <input type="file" @input="form.file = $event.target.files[0]" class="w-full" required />
      </div>
      <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Upload</button>
    </form>
  </IntranetLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';

const form = useForm({
  title: '',
  description: '',
  category: '',
  visible_to_roles: [],
  file: null,
});
</script>