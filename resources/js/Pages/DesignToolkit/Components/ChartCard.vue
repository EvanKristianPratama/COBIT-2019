<script setup>
/**
 * ChartCard - Wrapper component for charts with consistent styling
 * 
 * Used throughout Design Toolkit for consistent chart presentation
 */
defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    height: {
        type: String,
        default: '400px',
    },
    flush: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-700 bg-slate-900 text-white">
            <h3 class="text-lg font-semibold text-white">
                {{ title }}
            </h3>
            <p v-if="subtitle" class="text-sm text-slate-300 mt-1">
                {{ subtitle }}
            </p>
        </div>
        
        <!-- Chart Container -->
        <div :class="flush ? 'p-0' : 'p-6'">
            <div :style="{ height }">
                <slot />
            </div>
        </div>
    </div>
</template>
