<template>
  <PublicLayout>
    <!-- Page Header -->
    <div class="bg-gray-100 py-12">
      <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl font-bold text-amber-800">Our Programmes</h1>
        <p class="mt-2 text-lg text-gray-600">Find the perfect programme to launch your culinary career.</p>
      </div>
    </div>

    <!-- Programmes List -->
    <div class="py-16 bg-white">
      <div class="container mx-auto px-6">
        <div class="space-y-12">
          <div v-for="programme in programmes" :key="programme.id" class="bg-gray-50 p-8 rounded-lg shadow-lg">
            <h2 class="text-3xl font-bold text-amber-800">{{ programme.name }}</h2>
            <p class="mt-2 text-gray-600">{{ programme.description }}</p>
            <p class="mt-4 font-semibold">Duration: {{ programme.duration }}</p>

            <div v-if="programme.modules && programme.modules.length" class="mt-6">
              <h3 class="text-xl font-semibold">Modules</h3>
              <ul class="mt-4 grid md:grid-cols-2 gap-4 list-disc list-inside">
                <li v-for="module in programme.modules" :key="module.id" class="text-gray-700">
                  {{ module.name }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import PublicLayout from './PublicLayout.vue';

defineProps({
  programmes: Array,
});
</script>