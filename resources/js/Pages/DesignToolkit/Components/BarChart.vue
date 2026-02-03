<script setup>
/**
 * BarChart - Horizontal bar chart for relative importance visualization
 * 
 * Uses Chart.js for rendering. Shows 40 governance objectives as horizontal bars.
 * Color coding: Blue for positive values, Red for negative values, Gray for zero.
 */
import { ref, onMounted, watch, onUnmounted } from 'vue';

const props = defineProps({
    /** Array of labels (40 objectives: EDM01-MEA04) */
    labels: {
        type: Array,
        required: true,
    },
    /** Array of data values (relative importance -100 to +100) */
    data: {
        type: Array,
        required: true,
    },
    /** Chart height */
    height: {
        type: String,
        default: '700px',
    },
    /** Chart title */
    title: {
        type: String,
        default: 'Relative Importance',
    },
});

const canvasRef = ref(null);
let chartInstance = null;

// Get background colors based on values
const getBackgroundColors = (data) => {
    return data.map(value => {
        if (value > 0) return 'rgba(54, 162, 235, 0.6)';
        if (value < 0) return 'rgba(255, 99, 132, 0.6)';
        return 'rgba(201, 201, 201, 0.6)';
    });
};

// Get border colors based on values
const getBorderColors = (data) => {
    return data.map(value => {
        if (value > 0) return 'rgba(54, 162, 235, 1)';
        if (value < 0) return 'rgba(255, 99, 132, 1)';
        return 'rgba(201, 201, 201, 1)';
    });
};

const createChart = () => {
    if (!canvasRef.value || typeof Chart === 'undefined') return;
    
    chartInstance = new Chart(canvasRef.value, {
        type: 'bar',
        data: {
            labels: props.labels,
            datasets: [{
                label: props.title,
                data: props.data,
                backgroundColor: getBackgroundColors(props.data),
                borderColor: getBorderColors(props.data),
                borderWidth: 1,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    min: -100,
                    max: 100,
                    beginAtZero: true,
                    ticks: { stepSize: 20 },
                    grid: {
                        color: (ctx) => ctx.tick.value === 0 
                            ? 'rgba(0, 0, 0, 0.3)' 
                            : 'rgba(200, 200, 200, 0.3)',
                        lineWidth: (ctx) => ctx.tick.value === 0 ? 2 : 1,
                    },
                },
                y: {
                    ticks: { 
                        autoSkip: false,
                        font: { size: 11 },
                    },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const value = ctx.raw;
                            const prefix = value >= 0 ? '+' : '';
                            return `${props.title}: ${prefix}${value}`;
                        },
                    },
                },
            },
        },
    });
};

const updateChart = () => {
    if (!chartInstance) return;
    
    chartInstance.data.datasets[0].data = props.data;
    chartInstance.data.datasets[0].backgroundColor = getBackgroundColors(props.data);
    chartInstance.data.datasets[0].borderColor = getBorderColors(props.data);
    chartInstance.update();
};

onMounted(() => {
    if (typeof Chart !== 'undefined') {
        createChart();
    } else {
        setTimeout(createChart, 100);
    }
});

watch(() => props.data, updateChart, { deep: true });

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
