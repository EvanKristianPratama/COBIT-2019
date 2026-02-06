<template>
  <div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
      <h3 class="text-lg font-semibold text-slate-800">Capability Diagram</h3>
      <p class="text-sm text-slate-500 mt-1">Radar chart visualization of capability levels</p>
    </div>

    <!-- Chart Container -->
    <div class="p-6">
      <div class="relative aspect-square max-w-2xl mx-auto">
        <canvas ref="chartCanvas"></canvas>
      </div>

      <!-- Legend -->
      <div class="flex flex-wrap justify-center gap-4 mt-6">
        <div class="flex items-center gap-2">
          <span class="w-4 h-4 bg-blue-500 rounded-sm"></span>
          <span class="text-sm text-slate-600">Capability Level</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-4 h-4 bg-emerald-500 rounded-sm"></span>
          <span class="text-sm text-slate-600">Target Capability</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-4 h-4 bg-orange-400 rounded-sm"></span>
          <span class="text-sm text-slate-600">Maximum Capability</span>
        </div>
      </div>

      <!-- Maturity Score -->
      <div class="text-center mt-6 pt-4 border-t border-slate-100">
        <span class="text-sm text-slate-500">Maturity Score:</span>
        <span class="ml-2 px-4 py-2 bg-blue-600 text-white rounded-full text-lg font-bold">
          {{ maturityScore }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  chartData: {
    type: Object,
    default: () => ({ labels: [], datasets: [] }),
  },
  maturityScore: {
    type: String,
    default: '0.00',
  },
});

const chartCanvas = ref(null);
let chartInstance = null;

const createChart = () => {
  if (!chartCanvas.value || !props.chartData.labels?.length) return;

  // Destroy existing chart
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }

  const ctx = chartCanvas.value.getContext('2d');
  
  chartInstance = new Chart(ctx, {
    type: 'radar',
    data: props.chartData,
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          backgroundColor: 'rgba(15, 43, 92, 0.9)',
          titleFont: { size: 14, weight: 'bold' },
          bodyFont: { size: 12 },
          padding: 12,
          cornerRadius: 8,
        },
      },
      scales: {
        r: {
          min: 0,
          max: 5,
          ticks: {
            stepSize: 1,
            font: { size: 11 },
            color: '#64748b',
          },
          pointLabels: {
            font: { size: 11, weight: '600' },
            color: '#0f2b5c',
          },
          grid: {
            color: 'rgba(203, 213, 225, 0.5)',
          },
          angleLines: {
            color: 'rgba(203, 213, 225, 0.5)',
          },
        },
      },
      elements: {
        line: {
          borderWidth: 2,
        },
        point: {
          radius: 4,
          hoverRadius: 6,
        },
      },
    },
  });
};

onMounted(() => {
  nextTick(() => createChart());
});

watch(
  () => props.chartData,
  () => {
    nextTick(() => createChart());
  },
  { deep: true }
);
</script>
