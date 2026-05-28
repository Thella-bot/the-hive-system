<template>
  <div class="bg-white p-6 rounded-xl shadow-sm">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">User Distribution</h3>
    <Pie :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
  totalStudents: {
    type: Number,
    required: true,
  },
  totalInstructors: {
    type: Number,
    required: true,
  },
  totalStaff: {
    type: Number,
    required: true,
  },
})

const chartData = {
  labels: ['Students', 'Instructors', 'Staff'],
  datasets: [
    {
      backgroundColor: ['#FFC107', '#FF9800', '#FF5722'],
      data: [props.totalStudents, props.totalInstructors, props.totalStaff],
    },
  ],
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
}
</script>