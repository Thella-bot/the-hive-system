<template>
  <IntranetLayout>
    <h1 class="text-2xl font-bold mb-4">Assignments</h1>
    <Link v-if="userCanCreate" :href="route('intranet.assignments.create')" class="inline-block mb-4 bg-indigo-600 text-white px-4 py-2 rounded">Create Assignment</Link>
    <div class="space-y-4">
      <div v-for="assignment in assignments" :key="assignment.id" class="bg-white p-4 shadow rounded">
        <div class="flex justify-between">
          <h2 class="font-semibold text-lg">{{ assignment.title }}</h2>
          <span class="text-sm">Due: {{ new Date(assignment.due_date).toLocaleString() }}</span>
        </div>
        <p class="text-sm">{{ assignment.module?.name }}</p>
        <Link :href="route('intranet.assignments.show', assignment.id)" class="text-indigo-600 text-sm">View Details</Link>
      </div>
    </div>
  </IntranetLayout>
</template>
<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';

const props = defineProps({ assignments: Array });
const userCanCreate = computed(() => 
  usePage().props.user.roles.some(r => ['admin','instructor'].includes(r.name))
);
</script>