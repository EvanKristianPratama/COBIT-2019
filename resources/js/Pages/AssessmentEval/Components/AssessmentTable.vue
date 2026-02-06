<script setup>
/**
 * AssessmentTable.vue - Modern Clean UI
 */
import { 
    EyeIcon, 
    TrashIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    assessments: { type: Array, default: () => [] },
    showActions: { type: Boolean, default: true },
    currentPage: { type: Number, default: 1 },
    perPage: { type: Number, default: 10 }
});

const emit = defineEmits(['view', 'report', 'delete', 'evidence']);

// Helper to get score color class
// Helper to get score color class
const getMaturityColor = (score, target) => {
    // If no target, use default status or gray
    if (!target) return 'text-slate-600 dark:text-slate-400';
    
    // Green if score is greater than or equal to target
    if (score >= target) {
        return 'text-emerald-600 dark:text-emerald-400';
    }
    
    // Yellow if score is close to target (within 0.5)
    if (score >= (target - 0.5)) {
        return 'text-amber-500 dark:text-amber-400';
    }
    
    // Red if score is significantly below target
    return 'text-rose-600 dark:text-rose-400';
};

// Helper to get status info (Updated for Modern Badge Style)
const getStatusClasses = (status) => {
    if (status === 'finished') {
        return 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20';
    }
    return 'bg-slate-50 text-slate-700 ring-slate-600/20 dark:bg-slate-500/10 dark:text-slate-400 dark:ring-slate-500/20';
};

// Format relative time
const formatRelativeTime = (date) => {
    if (!date) return '-';
    try {
        const d = new Date(date);
        const now = new Date();
        const diff = Math.floor((now - d) / 1000);
        
        if (diff < 60) return 'Just now';
        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
        return d.toLocaleDateString('id-ID');
    } catch (e) {
        return '-';
    }
};
</script>

<template>
    <div class="w-full bg-white dark:bg-[#1a1a1a] shadow-sm border border-gray-200/80 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 dark:bg-white/5 dark:border-white/5">
                        <th class="pl-6 pr-3 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">
                            No
                        </th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Assessment ID
                        </th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">
                            Tahun
                        </th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">
                            Scope
                        </th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Last Update
                        </th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">
                            Maturity
                        </th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">
                            Avg Target
                        </th>
                        <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">
                            Goal
                        </th>
                        <th v-if="showActions" class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    <tr v-for="(assessment, index) in assessments" :key="assessment.eval_id || index" 
                        class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-150">
                        
                        <td class="pl-6 pr-3 py-4 text-sm text-gray-700 dark:text-gray-300">
                            {{ (currentPage - 1) * perPage + index + 1 }}
                        </td>

                        <td class="px-4 py-4 text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ assessment.eval_id }}
                        </td>

                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400 text-center">
                            {{ assessment.tahun || new Date(assessment.created_at).getFullYear() }}
                        </td>

                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400 text-center">
                            {{ assessment.scope_count ?? 0 }}
                        </td>

                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <span :class="['w-2 h-2 rounded-full', assessment.status === 'finished' ? 'bg-emerald-500' : 'bg-amber-400']"></span>
                                <span :class="['text-sm font-bold capitalize', assessment.status === 'finished' ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400']">
                                    {{ assessment.status === 'finished' ? 'Finished' : 'Draft' }}
                                </span>
                            </div>
                        </td>

                        <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ formatRelativeTime(assessment.last_saved_at) }}
                        </td>

                        <td class="px-4 py-4 text-center">
                           <span :class="['font-bold text-sm', getMaturityColor(assessment.maturity_score?.score || 0, assessment.avg_target_capability || 0)]">
                                {{ (assessment.maturity_score?.score || 0).toFixed(2) }}
                            </span>
                        </td>

                        <td class="px-4 py-4 text-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ (assessment.avg_target_capability || 0).toFixed(2) }}
                            </span>
                        </td>

                        <td class="px-4 py-4 text-center">
                            <span v-if="assessment.target_maturity" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ assessment.target_maturity.toFixed(2) }}
                            </span>
                            <span v-else class="text-gray-400 text-sm">-</span>
                        </td>

                        <td v-if="showActions" class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="emit('view', assessment)" 
                                    class="inline-flex items-center justify-center p-2 text-sky-600 hover:text-sky-700 rounded-lg transition-colors"
                                    title="View">
                                    <EyeIcon class="w-5 h-5" />
                                </button>
                                <button @click="emit('report', assessment)"
                                    class="inline-flex items-center justify-center p-2 text-indigo-600 hover:text-indigo-700 rounded-lg transition-colors"
                                    title="Report">
                                    <ChartBarIcon class="w-5 h-5" />
                                </button>
                                <template v-if="assessment.status !== 'finished'">
                                    <button @click="emit('delete', assessment)"
                                        class="inline-flex items-center justify-center p-2 text-rose-600 hover:text-rose-700 rounded-lg transition-colors"
                                        title="Delete">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="assessments.length === 0">
                        <td :colspan="showActions ? 8 : 7" class="px-6 py-12 text-center text-sm text-gray-500">
                            No assessments found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div v-if="assessments.length > 0" class="h-1 w-full bg-gradient-to-r from-slate-100 via-slate-200 to-slate-100 dark:from-slate-800 dark:via-slate-700 dark:to-slate-800"></div>
    </div>
</template>