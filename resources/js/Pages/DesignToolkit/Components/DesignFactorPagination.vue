<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    currentDf: {
        type: Number,
        required: true
    },
    routes: {
        type: Object,
        required: true
    },
    position: {
        type: String,
        default: 'bottom'
    }
});
</script>

<template>
    <div 
        :class="[
            'flex flex-col md:flex-row items-center justify-between gap-3',
            position === 'bottom' ? 'mt-6 pt-4 border-t border-slate-200/80 dark:border-slate-700/80' : '',
            position === 'top' ? 'mb-5 pb-4 border-b border-slate-200/80 dark:border-slate-700/80' : ''
        ]"
    >
        <!-- Previous Button -->
        <Link 
            v-if="currentDf > 1" 
            :href="routes.show[currentDf - 1]"
            class="flex items-center gap-2 px-3 py-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="hidden sm:inline">Previous:</span> DF{{ currentDf - 1 }}
        </Link>
        <div v-else class="w-32 hidden md:block"></div>  <!-- Spacer -->

        <!-- Number Navigation -->
        <div class="flex flex-wrap justify-center gap-1.5 min-w-0 overflow-x-auto py-1">
            <template v-for="i in 10" :key="i">
                <Link 
                    :href="routes.show[i]" 
                    :class="[
                        'flex items-center justify-center w-7 h-7 rounded-md text-[11px] font-semibold transition-all',
                        currentDf === i 
                            ? 'bg-[#1f4e79] text-white shadow-sm' 
                            : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:border-[#1f4e79] hover:text-[#1f4e79] dark:hover:text-white'
                    ]"
                    :title="`Go to Design Factor ${i}`"
                >
                    {{ i }}
                </Link>
                <!-- Initial Scope after DF4 -->
                <Link 
                    v-if="i === 4"
                    :href="routes.summaryStep2"
                    :class="[
                        'flex items-center justify-center px-2.5 h-7 rounded-md text-[10px] font-bold uppercase transition-all tracking-tighter',
                        currentDf === 11 
                            ? 'bg-[#1f4e79] text-white shadow-sm' 
                            : 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50 hover:bg-emerald-50 dark:hover:bg-emerald-900/10'
                    ]"
                >
                    Step 2
                </Link>
                <!-- Refined Scope after DF10 -->
                <Link 
                    v-if="i === 10"
                    :href="routes.summaryStep3"
                    :class="[
                        'flex items-center justify-center px-2.5 h-7 rounded-md text-[10px] font-bold uppercase transition-all tracking-tighter',
                        currentDf === 12 
                            ? 'bg-[#1f4e79] text-white shadow-sm' 
                            : 'bg-white dark:bg-slate-800 text-teal-600 dark:text-teal-400 border border-teal-200 dark:border-teal-900/50 hover:bg-teal-50 dark:hover:bg-teal-900/10'
                    ]"
                >
                    Step 3
                </Link>
                <Link 
                    v-if="i === 10 && routes.summaryStep4"
                    :href="routes.summaryStep4"
                    :class="[
                        'flex items-center justify-center px-2.5 h-7 rounded-md text-[10px] font-bold uppercase transition-all tracking-tighter',
                        currentDf === 13 
                            ? 'bg-[#1f4e79] text-white shadow-sm' 
                            : 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/10'
                    ]"
                >
                    Canvas
                </Link>
            </template>
        </div>

        <!-- Next Button -->
        <Link 
            v-if="currentDf < 10" 
            :href="routes.show[currentDf + 1]"
            class="flex items-center gap-2 px-3 py-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors"
        >
             <span class="hidden sm:inline">Next:</span> DF{{ currentDf + 1 }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </Link>
        <div v-else class="w-32 hidden md:block"></div> <!-- Spacer -->
    </div>
</template>
