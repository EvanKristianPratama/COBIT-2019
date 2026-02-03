<script setup>
/**
 * SpiderChart (Radar Chart) - Displays relative importance as a radar/spider diagram
 * 
 * Uses Chart.js for rendering. Shows 40 governance objectives in a circular pattern.
 * Color coding: Blue for positive values, Red for negative values.
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
        default: '400px',
    },
    /** Whether to reverse the labels (for visual consistency) */
    reverseLabels: {
        type: Boolean,
        default: true,
    },
});

const canvasRef = ref(null);
let chartInstance = null;

// Get border colors based on values
const getBorderColors = (data) => {
    return data.map(value => 
        value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
    );
};

// Get point background colors based on values
const getPointColors = (data) => {
    return data.map(value => 
        value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
    );
};

const createChart = () => {
    if (!canvasRef.value || typeof Chart === 'undefined') return;
    
    const displayData = props.reverseLabels ? [...props.data].reverse() : props.data;
    const displayLabels = props.reverseLabels ? [...props.labels].reverse() : props.labels;
    
    chartInstance = new Chart(canvasRef.value, {
        type: 'radar',
        data: {
            labels: displayLabels,
            datasets: [{
                label: 'Relative Importance',
                data: displayData,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: getBorderColors(displayData),
                borderWidth: 2,
                pointBackgroundColor: getPointColors(displayData),
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: getPointColors(displayData),
                tension: 0.4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    suggestedMin: -100,
                    suggestedMax: 100,
                    ticks: { 
                        stepSize: 25,
                        font: { size: 10 },
                    },
                    pointLabels: { 
                        font: { size: 9 },
                    },
                    angleLines: { 
                        color: 'rgba(200, 200, 200, 0.3)',
                    },
                    grid: { 
                        color: 'rgba(200, 200, 200, 0.3)',
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
                            return `Relative Importance: ${prefix}${value}`;
                        },
                    },
                },
            },
        },
    });
};

const updateChart = () => {
    if (!chartInstance) return;
    
    const displayData = props.reverseLabels ? [...props.data].reverse() : props.data;
    
    chartInstance.data.datasets[0].data = displayData;
    chartInstance.data.datasets[0].borderColor = getBorderColors(displayData);
    chartInstance.data.datasets[0].pointBackgroundColor = getPointColors(displayData);
    chartInstance.data.datasets[0].pointHoverBorderColor = getPointColors(displayData);
    chartInstance.update();
};

onMounted(() => {
    // Wait for Chart.js to be available
    if (typeof Chart !== 'undefined') {
        createChart();
    } else {
        // Try again after a short delay (in case Chart.js is loading)
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
