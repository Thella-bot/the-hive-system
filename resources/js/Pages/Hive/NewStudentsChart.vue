<template>
  <div class="bg-white p-6 rounded-xl shadow-sm">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">New Students This Year</h3>
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const props = defineProps({
  newStudentsByMonth: {
    type: Object,
    required: true,
  },
})

const chartData = {
  labels: Object.keys(props.newStudentsByMonth),
  datasets: [
    {
      label: 'New Students',
      backgroundColor: '#FFC107',
      data: Object.values(props.newStudentsByMonth),
    },
  ],
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
}
</script>