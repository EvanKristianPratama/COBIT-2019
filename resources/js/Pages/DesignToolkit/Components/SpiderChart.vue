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
        default: () => [],
    },
    /** Optional multiple datasets (overrides data) */
    datasets: {
        type: Array,
        default: null,
    },
    /** Chart height */
    height: {
        type: String,
        default: '400px',
    },
    /** Min value for radial scale */
    min: {
        type: Number,
        default: -100,
    },
    /** Max value for radial scale */
    max: {
        type: Number,
        default: 100,
    },
    /** Step size for ticks */
    stepSize: {
        type: Number,
        default: 25,
    },
    /** Dataset label */
    datasetLabel: {
        type: String,
        default: 'Relative Importance',
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

const toRgba = (color, alpha = 0.2) => {
    if (!color) return `rgba(54, 162, 235, ${alpha})`;
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
    return `rgba(54, 162, 235, ${alpha})`;
};

const createChart = () => {
    if (!canvasRef.value || typeof Chart === 'undefined') return;
    
    const safeLabels = Array.isArray(props.labels) ? props.labels : [];
    const safeData = Array.isArray(props.data) ? props.data : [];

    const displayLabels = props.reverseLabels ? [...safeLabels].reverse() : safeLabels;
    const displayData = props.reverseLabels ? [...safeData].reverse() : safeData;

    const hasDatasets = Array.isArray(props.datasets) && props.datasets.length > 0;
    const datasets = hasDatasets
        ? props.datasets.map((ds) => {
            const color = ds.color || '#2563eb';
            const dsData = props.reverseLabels ? [...(ds.data || [])].reverse() : (ds.data || []);
            return {
                label: ds.label || props.datasetLabel,
                data: dsData,
                backgroundColor: toRgba(color, 0.12),
                borderColor: color,
                borderWidth: 2,
                pointBackgroundColor: color,
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: color,
                tension: 0.4,
            };
        })
        : [{
            label: props.datasetLabel,
            data: displayData,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: getBorderColors(displayData),
            borderWidth: 2,
            pointBackgroundColor: getPointColors(displayData),
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: getPointColors(displayData),
            tension: 0.4,
        }];
    
    chartInstance = new Chart(canvasRef.value, {
        type: 'radar',
        data: {
            labels: displayLabels,
            datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    suggestedMin: props.min,
                    suggestedMax: props.max,
                    ticks: { 
                        stepSize: props.stepSize,
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
                            return `${props.datasetLabel}: ${prefix}${value}`;
                        },
                    },
                },
            },
        },
    });
};

const updateChart = () => {
    if (!chartInstance) return;
    
    const safeLabels = Array.isArray(props.labels) ? props.labels : [];
    const safeData = Array.isArray(props.data) ? props.data : [];
    const displayLabels = props.reverseLabels ? [...safeLabels].reverse() : safeLabels;
    const displayData = props.reverseLabels ? [...safeData].reverse() : safeData;

    const hasDatasets = Array.isArray(props.datasets) && props.datasets.length > 0;
    if (hasDatasets) {
        chartInstance.data.labels = displayLabels;
        chartInstance.data.datasets = props.datasets.map((ds) => {
            const color = ds.color || '#2563eb';
            const dsData = props.reverseLabels ? [...(ds.data || [])].reverse() : (ds.data || []);
            return {
                label: ds.label || props.datasetLabel,
                data: dsData,
                backgroundColor: toRgba(color, 0.12),
                borderColor: color,
                borderWidth: 2,
                pointBackgroundColor: color,
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: color,
                tension: 0.4,
            };
        });
    } else {
        chartInstance.data.labels = displayLabels;
        chartInstance.data.datasets[0].data = displayData;
        chartInstance.data.datasets[0].borderColor = getBorderColors(displayData);
        chartInstance.data.datasets[0].pointBackgroundColor = getPointColors(displayData);
        chartInstance.data.datasets[0].pointHoverBorderColor = getPointColors(displayData);
    }
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

watch(() => [props.data, props.labels, props.datasets], updateChart, { deep: true });

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
