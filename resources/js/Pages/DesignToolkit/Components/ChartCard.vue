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
});
</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ title }}
            </h3>
            <p v-if="subtitle" class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ subtitle }}
            </p>
        </div>
        
        <!-- Chart Container -->
        <div class="p-6">
            <div :style="{ height }">
                <slot />
            </div>
        </div>
    </div>
</template>
