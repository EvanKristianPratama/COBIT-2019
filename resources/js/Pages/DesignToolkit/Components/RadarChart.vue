<script setup>
/**
 * RadarChart - Generic radar chart for capability levels
 *
 * Uses Chart.js for rendering. Supports two datasets (e.g. Agreed vs Maximum).
 */
import { ref, onMounted, watch, onUnmounted } from 'vue';

const props = defineProps({
    labels: {
        type: Array,
        required: true,
    },
    data: {
        type: Array,
        required: true,
    },
    maxData: {
        type: Array,
        required: true,
    },
    height: {
        type: String,
        default: '450px',
    },
});

const canvasRef = ref(null);
let chartInstance = null;

const createChart = () => {
    if (!canvasRef.value || typeof Chart === 'undefined') return;

    chartInstance = new Chart(canvasRef.value, {
        type: 'radar',
        data: {
            labels: props.labels,
            datasets: [
                {
                    label: 'Agreed Level',
                    data: props.data,
                    fill: false,
                    borderColor: '#2563eb',
                    borderWidth: 2,
                    pointRadius: 0,
                },
                {
                    label: 'Maximum',
                    data: props.maxData,
                    fill: false,
                    borderColor: '#f59e0b',
                    borderWidth: 2,
                    pointRadius: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    suggestedMin: 0,
                    suggestedMax: 5,
                    ticks: {
                        stepSize: 1,
                        font: { size: 10 },
                    },
                    pointLabels: {
                        font: { size: 9 },
                    },
                },
            },
            plugins: {
                legend: { display: true },
                tooltip: { enabled: false },
            },
        },
    });
};

const updateChart = () => {
    if (!chartInstance) return;
    chartInstance.data.labels = props.labels;
    chartInstance.data.datasets[0].data = props.data;
    chartInstance.data.datasets[1].data = props.maxData;
    chartInstance.update();
};

onMounted(() => {
    if (typeof Chart !== 'undefined') {
        createChart();
    } else {
        setTimeout(createChart, 100);
    }
});

watch(() => [props.data, props.maxData, props.labels], updateChart, { deep: true });

onUnmounted(() => {
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
});
</script>

<template>
    <div :style="{ height }">
        <canvas ref="canvasRef"></canvas>
    </div>
</template>
