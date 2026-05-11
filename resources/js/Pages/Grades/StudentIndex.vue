<template>
  <IntranetLayout>
    <h1 class="text-2xl font-bold mb-4">My Grades</h1>
    <div v-for="module in modules" :key="module.id" class="mb-6 bg-white p-4 shadow rounded">
      <h2 class="text-lg font-semibold">{{ module.name }}</h2>
      <table class="w-full mt-2">
        <thead><tr><th>Item</th><th>Marks</th><th>Max</th><th>Weight</th><th>Weighted</th></tr></thead>
        <tbody>
          <tr v-for="item in module.grade_items" :key="item.id">
            <td>{{ item.name }}</td>
            <td>{{ item.student_grades[0]?.marks ?? '-' }}</td>
            <td>{{ item.max_marks }}</td>
            <td>{{ (item.weight * 100).toFixed(1) }}%</td>
            <td>{{ item.student_grades[0] ? (item.student_grades[0].marks / item.max_marks * item.weight * 100).toFixed(1) : '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </IntranetLayout>
</template>
<script setup>
import IntranetLayout from '@/Layouts/IntranetLayout.vue';
defineProps({ modules: Array });
</script>