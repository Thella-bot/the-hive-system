<template>
  <IntranetLayout>
    <h1 class="text-2xl font-bold mb-2">{{ document.title }}</h1>
    <p class="text-sm text-gray-600">{{ document.description }}</p>
    <p class="text-sm mb-4">Category: {{ document.category }} | Created by {{ document.creator.name }}</p>

    <div v-if="canUpdate" class="mb-6">
      <h2 class="font-semibold">Upload New Version</h2>
      <form @submit.prevent="uploadVersion" class="flex gap-2 items-end">
        <input type="file" @input="newVersionForm.file = $event.target.files[0]" required />
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Upload Version</button>
      </form>
    </div>

    <h2 class="text-lg font-semibold mt-6">Version History</h2>
    <table class="w-full mt-2">
      <thead><tr><th>Version</th><th>Uploaded by</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
        <tr v-for="version in document.versions" :key="version.id">
          <td>{{ version.version_number }}</td>
          <td>{{ version.uploader.name }}</td>
          <td>{{ new Date(version.created_at).toLocaleDateString() }}</td>
          <td>
            <a :href="route('documents.version.download', version.id)" class="text-blue-600 underline">Download</a>
          </td>
        </tr>
      </tbody>
    </table>
  </IntranetLayout>
</template>
<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';

const props = defineProps({ document: Object });
const canUpdate = computed(() => {
  const user = usePage().props.user;
  return user.roles.some(r => r.name === 'admin') || user.id === props.document.created_by;
});
const newVersionForm = useForm({ file: null });
const uploadVersion = () => newVersionForm.post(route('documents.versions.store', props.document.id));
</script>