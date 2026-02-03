<script setup>
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
    height: {
        type: String,
        default: '300px',
    },
});

const canvasRef = ref(null);
let chartInstance = null;

const colors = [
    'rgba(59, 130, 246, 0.8)', // Blue
    'rgba(16, 185, 129, 0.8)', // Green
    'rgba(168, 85, 247, 0.8)', // Purple
    'rgba(249, 115, 22, 0.8)', // Orange
    'rgba(6, 182, 212, 0.8)', // Cyan
    'rgba(236, 72, 153, 0.8)', // Pink
    'rgba(245, 158, 11, 0.8)', // Amber
];

const createChart = () => {
    if (!canvasRef.value || typeof Chart === 'undefined') return;

    chartInstance = new Chart(canvasRef.value, {
        type: 'pie',
        data: {
            labels: props.labels,
            datasets: [{
                data: props.data,
                backgroundColor: colors.slice(0, props.data.length),
                borderWidth: 2,
                borderColor: '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + '%';
                        }
                    }
                }
            }
        }
    });
};

const updateChart = () => {
    if (!chartInstance) return;
    chartInstance.data.labels = props.labels;
    chartInstance.data.datasets[0].data = props.data;
    chartInstance.data.datasets[0].backgroundColor = colors.slice(0, props.data.length);
    chartInstance.update();
};

onMounted(() => {
    if (typeof Chart !== 'undefined') {
        createChart();
    } else {
        setTimeout(createChart, 500);
    }
});

watch(() => props.data, updateChart, { deep: true });
watch(() => props.labels, updateChart, { deep: true });

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
