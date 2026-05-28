<template>
  <div class="bg-white p-6 rounded-xl shadow-sm">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Class Averages</h3>
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const props = defineProps({
  classAverages: {
    type: Object,
    required: true,
  },
})

const chartData = {
  labels: Object.keys(props.classAverages),
  datasets: [
    {
      label: 'Class Average',
      backgroundColor: '#FF9800',
      data: Object.values(props.classAverages),
    },
  ],
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
}
</script>