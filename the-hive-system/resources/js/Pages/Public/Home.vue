<template>
  <PublicLayout>
    <div class="text-center py-12">
      <h1 class="text-5xl font-bold text-orange-800">Welcome to HBCI</h1>
      <p class="mt-4 text-lg">Professional culinary education in Lesotho</p>
    </div>
    <div class="grid md:grid-cols-3 gap-6 mt-8">
      <div class="bg-white p-6 shadow rounded">
        <h2 class="text-xl font-semibold">Programmes</h2>
        <p class="mt-2">{{ programmes_count }} diplomas offered</p>
        <Link :href="route('programmes')" class="text-indigo-600 mt-4 inline-block">Learn more</Link>
      </div>
      <!-- more blocks if needed -->
    </div>
    <div v-if="announcements.length" class="mt-12">
      <h2 class="text-2xl font-bold mb-4">Announcements</h2>
      <div v-for="a in announcements" :key="a.id" class="bg-white p-4 shadow rounded mb-2">
        <h3>{{ a.title }}</h3>
        <p>{{ a.body.substring(0, 150) }}...</p>
      </div>
    </div>
  </PublicLayout>
</template>
<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
defineProps({ programmes_count: Number, announcements: Array });
</script>