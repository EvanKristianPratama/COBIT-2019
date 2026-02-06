<script setup>
/**
 * HeroCard.vue - Refined Stats hero section
 */
import { 
    ChartBarIcon, 
    CheckCircleIcon, 
    PencilSquareIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    totalAssessments: { type: Number, default: 0 },
    finishedAssessments: { type: Number, default: 0 },
    draftAssessments: { type: Number, default: 0 }
});
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Count -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col justify-between h-32 relative overflow-hidden">
            <div class="absolute right-0 top-0 p-6">
                <ChartBarIcon class="w-16 h-16 text-sky-600 opacity-100" />
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Assessments</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-900 dark:text-white">{{ totalAssessments.toLocaleString() }}</span>
                    <span class="text-xs font-medium text-slate-400">Items</span>
                </div>
            </div>
            <div class="h-1 w-full bg-blue-600 rounded-full mt-4"></div>
        </div>

        <!-- Finished Count -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col justify-between h-32 relative overflow-hidden">
            <div class="absolute right-0 top-0 p-6">
                <CheckCircleIcon class="w-16 h-16 text-emerald-600 opacity-100" />
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Completed</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-900 dark:text-white">{{ finishedAssessments.toLocaleString() }}</span>
                    <span class="text-xs font-medium text-slate-400">Verified</span>
                </div>
            </div>
            <div class="h-1 w-full bg-emerald-600 rounded-full mt-4"></div>
        </div>

        <!-- Draft Count -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col justify-between h-32 relative overflow-hidden">
            <div class="absolute right-0 top-0 p-6">
                <PencilSquareIcon class="w-16 h-16 text-amber-500 opacity-100" />
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Draft / Pending</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-900 dark:text-white">{{ draftAssessments.toLocaleString() }}</span>
                    <span class="text-xs font-medium text-slate-400">In Progress</span>
                </div>
            </div>
            <div class="h-1 w-full bg-amber-500 rounded-full mt-4"></div>
        </div>
    </div>
</template>
