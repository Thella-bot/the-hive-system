<template>
  <HiveLayout title="Tests">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold text-gray-800">Tests</h1>
    </div>

    <div v-if="tests && tests.length === 0" class="bg-white p-8 text-center rounded shadow">
      <p class="text-gray-500">No tests found.</p>
    </div>

    <div class="grid gap-4">
      <div v-for="test in tests" :key="test.id"
           class="bg-white p-4 shadow rounded hover:shadow-md transition">
        <div class="flex justify-between items-start">
          <div>
            <h2 class="font-semibold text-lg text-gray-800">{{ test.title }}</h2>
            <p class="text-sm text-gray-600 mt-1">{{ test.module?.name || 'No Module' }}</p>
            <div class="mt-2 text-sm text-gray-500">
              <span>Due: {{ formatDate(test.due_date) }}</span>
            </div>
          </div>
          <span :class="['px-2 py-1 text-xs rounded', getTypeClass(test.type)]">
            {{ test.type }}
          </span>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
  tests: Array,
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

const getTypeClass = (type) => {
  const classes = {
    'quiz': 'bg-blue-100 text-blue-800',
    'test': 'bg-purple-100 text-purple-800',
    'assignment': 'bg-green-100 text-green-800',
    'mid-term_exam': 'bg-orange-100 text-orange-800',
    'final_exam': 'bg-red-100 text-red-800',
  };
  return classes[type] || 'bg-gray-100 text-gray-800';
};
</script>