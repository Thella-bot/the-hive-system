<template>
  <div class="bg-white p-6 rounded-xl shadow-sm">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Pending Leave Requests</h3>
    <Pie :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
  leaveRequestsByType: {
    type: Object,
    required: true,
  },
})

const chartData = {
  labels: Object.keys(props.leaveRequestsByType),
  datasets: [
    {
      backgroundColor: ['#FF5722', '#FF9800', '#FFC107', '#FFEB3B'],
      data: Object.values(props.leaveRequestsByType),
    },
  ],
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
}
</script>