<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    domains: Array,
});

const getCapabilityColor = (current, target) => {
    if (current >= target) return 'text-emerald-600 dark:text-emerald-400';
    if (current >= target - 1) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
};

const getCapabilityBgColor = (current, target) => {
    if (current >= target) return 'bg-emerald-500';
    if (current >= target - 1) return 'bg-amber-500';
    return 'bg-red-500';
};

const getLevelLabel = (level) => {
    const labels = {
        0: 'Incomplete',
        1: 'Performed',
        2: 'Managed',
        3: 'Established',
        4: 'Predictable',
        5: 'Optimizing',
    };
    return labels[level] || `Level ${level}`;
};

const getDomainColor = (code) => {
    const colors = {
        EDM: 'from-blue-500 to-blue-600',
        APO: 'from-purple-500 to-purple-600',
        BAI: 'from-amber-500 to-amber-600',
        DSS: 'from-emerald-500 to-emerald-600',
        MEA: 'from-red-500 to-red-600',
    };
    return colors[code] || 'from-gray-500 to-gray-600';
};

// Calculate average for each domain
const domainAverages = computed(() => {
    return props.domains.map(domain => {
        const currentAvg = domain.objectives.reduce((sum, obj) => sum + obj.current, 0) / domain.objectives.length;
        const targetAvg = domain.objectives.reduce((sum, obj) => sum + obj.target, 0) / domain.objectives.length;
        return {
            code: domain.code,
            currentAvg: currentAvg.toFixed(1),
            targetAvg: targetAvg.toFixed(1),
        };
    });
});
</script>

<template>
    <Head title="Capability Report" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <Link 
                        :href="route('reporting.index')"
                        class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors mb-4"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali ke Reporting
                    </Link>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Capability Level</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Analisis mendalam capability level per domain COBIT</p>
                </div>

                <!-- Demo Notice -->
                <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-sm text-amber-700 dark:text-amber-300">Demo Mode - Data ini adalah contoh untuk demonstrasi.</p>
                    </div>
                </div>

                <!-- Domain Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                    <div 
                        v-for="avg in domainAverages" 
                        :key="avg.code"
                        class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4"
                    >
                        <div class="flex items-center gap-3">
                            <div :class="['w-12 h-12 rounded-xl bg-gradient-to-br flex items-center justify-center text-white font-bold', getDomainColor(avg.code)]">
                                {{ avg.code }}
                            </div>
                            <div>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ avg.currentAvg }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Target: {{ avg.targetAvg }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Level Legend -->
                <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4 mb-8">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Capability Level Reference</h3>
                    <div class="flex flex-wrap gap-4">
                        <div v-for="level in [0, 1, 2, 3, 4, 5]" :key="level" class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-white/10 flex items-center justify-center text-sm font-bold text-gray-900 dark:text-white">
                                {{ level }}
                            </div>
                            <span class="text-xs text-gray-600 dark:text-gray-400">{{ getLevelLabel(level) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Domain Details -->
                <div class="space-y-6">
                    <div 
                        v-for="domain in domains" 
                        :key="domain.code"
                        class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 overflow-hidden"
                    >
                        <!-- Domain Header -->
                        <div :class="['p-6 bg-gradient-to-r text-white', getDomainColor(domain.code)]">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-xl bg-white/20 flex items-center justify-center text-xl font-bold">
                                    {{ domain.code }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold">{{ domain.code }} - {{ domain.name }}</h3>
                                    <p class="text-sm text-white/80">{{ domain.objectives.length }} Governance/Management Objectives</p>
                                </div>
                            </div>
                        </div>

                        <!-- Objectives Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-white/5">
                                        <th class="text-left py-3 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Code</th>
                                        <th class="text-left py-3 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Objective</th>
                                        <th class="text-center py-3 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Current</th>
                                        <th class="text-center py-3 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Target</th>
                                        <th class="text-center py-3 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Gap</th>
                                        <th class="text-left py-3 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-48">Progress</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                                    <tr 
                                        v-for="obj in domain.objectives" 
                                        :key="obj.code"
                                        class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                                    >
                                        <td class="py-4 px-6">
                                            <span class="px-2 py-1 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-xs font-mono rounded">
                                                {{ obj.code }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="text-sm text-gray-900 dark:text-white">{{ obj.name }}</span>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <span :class="['text-lg font-bold', getCapabilityColor(obj.current, obj.target)]">
                                                {{ obj.current }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="text-lg font-bold text-gray-600 dark:text-gray-400">{{ obj.target }}</span>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <span 
                                                :class="[
                                                    'px-2 py-1 text-xs font-medium rounded-full',
                                                    obj.current >= obj.target 
                                                        ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400' 
                                                        : 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400'
                                                ]"
                                            >
                                                {{ obj.current >= obj.target ? '✓ Met' : `-${obj.target - obj.current}` }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-1">
                                                <div v-for="level in 5" :key="level" class="flex-1">
                                                    <div 
                                                        :class="[
                                                            'h-2 rounded-full',
                                                            level <= obj.current 
                                                                ? getCapabilityBgColor(obj.current, obj.target)
                                                                : level <= obj.target 
                                                                    ? 'bg-gray-300 dark:bg-gray-600' 
                                                                    : 'bg-gray-100 dark:bg-white/5'
                                                        ]"
                                                    ></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
