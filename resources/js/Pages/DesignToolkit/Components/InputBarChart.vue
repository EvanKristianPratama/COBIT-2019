<script setup>
/**
 * InputBarChart - Compact horizontal bar chart for input values
 *
 * Designed for DF1-DF4 and DF7 input visualization.
 */
import { ref, onMounted, watch, onUnmounted } from 'vue';

const props = defineProps({
    /** Labels for each input */
    labels: {
        type: Array,
        required: true,
    },
    /** Data values */
    data: {
        type: Array,
        required: true,
    },
    /** Chart height */
    height: {
        type: String,
        default: '320px',
    },
    /** Max scale for x-axis */
    max: {
        type: Number,
        default: 5,
    },
    /** Dataset label */
    title: {
        type: String,
        default: 'Input Values',
    },
    /** Optional bar color */
    barColor: {
        type: String,
        default: 'rgba(59, 130, 246, 0.7)',
    },
});

const canvasRef = ref(null);
let chartInstance = null;

const getStepSize = (max) => {
    if (max <= 5) return 1;
    if (max <= 10) return 2;
    if (max <= 25) return 5;
    if (max <= 50) return 10;
    return 20;
};

const createChart = () => {
    if (!canvasRef.value || typeof Chart === 'undefined') return;

    const borderColor = props.barColor.startsWith('rgba')
        ? props.barColor.replace(/rgba\\(([^,]+),([^,]+),([^,]+),[^)]+\\)/, 'rgba($1,$2,$3,1)')
        : props.barColor;

    chartInstance = new Chart(canvasRef.value, {
        type: 'bar',
        data: {
            labels: props.labels,
            datasets: [{
                label: props.title,
                data: props.data,
                backgroundColor: props.barColor,
                borderColor,
                borderWidth: 1,
                borderRadius: 2,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    min: 0,
                    max: props.max,
                    ticks: {
                        stepSize: getStepSize(props.max),
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.25)',
                    },
                },
                y: {
                    ticks: {
                        autoSkip: false,
                        font: { size: 10 },
                    },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${props.title}: ${ctx.raw}`,
                    },
                },
            },
        },
    });
};

const updateChart = () => {
    if (!chartInstance) return;

    chartInstance.data.labels = props.labels;
    chartInstance.data.datasets[0].data = props.data;
    chartInstance.options.scales.x.max = props.max;
    chartInstance.options.scales.x.ticks.stepSize = getStepSize(props.max);
    chartInstance.update();
};

onMounted(() => {
    if (typeof Chart !== 'undefined') {
        createChart();
    } else {
        setTimeout(createChart, 100);
    }
});

watch(() => [props.data, props.labels, props.max], updateChart, { deep: true });

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
