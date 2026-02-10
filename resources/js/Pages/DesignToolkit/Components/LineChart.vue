<script setup>
/**
 * LineChart - Simple vertical line chart for time/sequence values
 *
 * Uses Chart.js for rendering. Intended for compact "Average per Year" views.
 */
import { ref, onMounted, watch, onUnmounted } from 'vue';

const props = defineProps({
    /** Labels for each point */
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
        default: '260px',
    },
    /** Min scale for y-axis */
    min: {
        type: Number,
        default: 0,
    },
    /** Max scale for y-axis */
    max: {
        type: Number,
        default: 5,
    },
    /** Dataset label */
    title: {
        type: String,
        default: 'Average',
    },
    /** Line color */
    lineColor: {
        type: String,
        default: 'rgba(37, 99, 235, 0.9)',
    },
    /** Show area fill */
    fill: {
        type: Boolean,
        default: false,
    },
    /** Tick step size */
    stepSize: {
        type: Number,
        default: 1,
    },
});

const canvasRef = ref(null);
let chartInstance = null;

const toRgba = (color, alpha = 0.12) => {
    if (!color) return `rgba(37, 99, 235, ${alpha})`;
    if (color.startsWith('rgba')) return color.replace(/rgba\(([^)]+)\)/, (match, inner) => {
        const parts = inner.split(',').map(v => v.trim());
        if (parts.length === 4) {
            return `rgba(${parts[0]}, ${parts[1]}, ${parts[2]}, ${alpha})`;
        }
        return `rgba(${inner}, ${alpha})`;
    });
    if (color.startsWith('rgb')) return color.replace('rgb', 'rgba').replace(')', `, ${alpha})`);
    if (color.startsWith('#')) {
        const hex = color.replace('#', '');
        const bigint = parseInt(hex.length === 3 ? hex.split('').map(c => c + c).join('') : hex, 16);
        const r = (bigint >> 16) & 255;
        const g = (bigint >> 8) & 255;
        const b = bigint & 255;
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }
    return `rgba(37, 99, 235, ${alpha})`;
};

const createChart = () => {
    if (!canvasRef.value || typeof Chart === 'undefined') return;

    chartInstance = new Chart(canvasRef.value, {
        type: 'line',
        data: {
            labels: props.labels,
            datasets: [{
                label: props.title,
                data: props.data,
                borderColor: props.lineColor,
                backgroundColor: props.fill ? toRgba(props.lineColor, 0.12) : 'transparent',
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 4,
                tension: 0.25,
                fill: props.fill,
                spanGaps: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        color: 'rgba(148, 163, 184, 0.25)',
                    },
                    ticks: {
                        font: { size: 10 },
                    },
                },
                y: {
                    min: props.min,
                    max: props.max,
                    ticks: {
                        stepSize: props.stepSize,
                        font: { size: 10 },
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.25)',
                    },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${props.title}: ${ctx.raw ?? '-'}`,
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
    chartInstance.data.datasets[0].borderColor = props.lineColor;
    chartInstance.data.datasets[0].backgroundColor = props.fill ? toRgba(props.lineColor, 0.12) : 'transparent';
    chartInstance.data.datasets[0].fill = props.fill;
    chartInstance.options.scales.y.min = props.min;
    chartInstance.options.scales.y.max = props.max;
    chartInstance.options.scales.y.ticks.stepSize = props.stepSize;
    chartInstance.update();
};

onMounted(() => {
    if (typeof Chart !== 'undefined') {
        createChart();
    } else {
        setTimeout(createChart, 100);
    }
});

watch(() => [props.data, props.labels, props.min, props.max, props.stepSize, props.lineColor, props.fill], updateChart, { deep: true });

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
