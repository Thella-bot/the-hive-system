<template>
  <div class="bg-white p-6 rounded-xl shadow-sm">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">New Students This Year</h3>
    <div class="h-64">
      <Bar :data="chartData" :options="chartOptions" />
    </div>
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
  plugins: {
    legend: { display: false },
  },
  scales: {
    x: {
      grid: { display: false },
    },
    y: {
      beginAtZero: true,
      ticks: { precision: 0 },
    },
  },
}
</script>