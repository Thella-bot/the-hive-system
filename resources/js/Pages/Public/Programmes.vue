<template>
  <PublicLayout>
    <h1 class="text-3xl font-bold">Our Programmes</h1>
    <div v-for="prog in programmes" :key="prog.id" class="mt-6 bg-white p-6 shadow rounded">
      <h2 class="text-2xl font-semibold">{{ prog.name }}</h2>
      <p class="text-sm text-gray-600">Duration: {{ prog.duration_years }} years</p>
      <p class="mt-2">{{ prog.description }}</p>
      <h3 class="mt-4 font-semibold">Modules</h3>
      <ul class="list-disc ml-5">
        <li v-for="m in prog.modules" :key="m.id">{{ m.name }} ({{ m.code }})</li>
      </ul>
    </div>
  </PublicLayout>
</template>
<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
defineProps({ programmes: Array });
</script>