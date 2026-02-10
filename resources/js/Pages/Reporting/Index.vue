<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    assessments: Array,
    capabilityData: Array,
    summary: Object,
    roadmap: Array,
    trendData: Array,
    currentYear: String,
});

// Calculate max value for bar chart scaling
const maxCapability = 5;

const getCapabilityPercentage = (value) => {
    return (value / maxCapability) * 100;
};

const getStatusColor = (status) => {
    const colors = {
        completed: 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400',
        in_progress: 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400',
        planned: 'bg-gray-100 dark:bg-gray-500/20 text-gray-700 dark:text-gray-400',
    };
    return colors[status] || colors.planned;
};

const getStatusLabel = (status) => {
    const labels = {
        completed: 'Selesai',
        in_progress: 'Berjalan',
        planned: 'Direncanakan',
    };
    return labels[status] || status;
};

const getPriorityColor = (priority) => {
    const colors = {
        high: 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400',
        medium: 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400',
        low: 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400',
    };
    return colors[priority] || colors.medium;
};

// Calculate trend line max
const maxTrend = computed(() => Math.max(...props.trendData.map(t => t.score)));
const getTrendHeight = (score) => {
    return (score / 100) * 100;
};
</script>

<template>
    <Head :title="`Reporting ${currentYear}`" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Assessment Reporting {{ currentYear }}
                            </h1>
                            <p class="text-gray-500 dark:text-gray-400 mt-1">
                                Dashboard hasil assessment dan roadmap capability
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <Link
                                :href="route('reporting.capability')"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Detail Capability
                            </Link>
                            <Link
                                :href="route('roadmap.index')"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl text-sm font-medium hover:from-emerald-600 hover:to-teal-700 transition-all shadow-lg shadow-emerald-500/25"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                                Lihat Roadmap
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Demo Notice -->
                <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Demo Mode</p>
                            <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">Halaman ini menampilkan data dummy untuk demonstrasi fitur reporting.</p>
                        </div>
                    </div>
                </div>

                <!-- Summary Stats -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.totalAssessments }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Total Assessment</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.completedThisYear }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Selesai Tahun Ini</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.averageScore }}%</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Rata-rata Skor</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.targetAchievement }}%</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Target Tercapai</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-cyan-100 dark:bg-cyan-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.activeProjects }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Proyek Aktif</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.pendingActions }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pending Actions</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Capability Bar Chart -->
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Capability Level per Domain</h3>
                        <div class="space-y-6">
                            <div v-for="cap in capabilityData" :key="cap.domain" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ cap.domain }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">{{ cap.fullName }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                        {{ cap.current }} / {{ cap.target }}
                                    </span>
                                </div>
                                <div class="relative h-6 bg-gray-100 dark:bg-white/5 rounded-lg overflow-hidden">
                                    <!-- Target indicator -->
                                    <div 
                                        class="absolute top-0 bottom-0 w-0.5 bg-gray-400 dark:bg-gray-500 z-10"
                                        :style="{ left: `${getCapabilityPercentage(cap.target)}%` }"
                                    ></div>
                                    <!-- Current value bar -->
                                    <div 
                                        class="h-full rounded-lg transition-all duration-500"
                                        :class="cap.current >= cap.target ? 'bg-emerald-500' : 'bg-gradient-to-r from-emerald-500 to-teal-500'"
                                        :style="{ width: `${getCapabilityPercentage(cap.current)}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 mt-6 pt-4 border-t border-gray-200 dark:border-white/10">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded bg-gradient-to-r from-emerald-500 to-teal-500"></div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Current Level</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-0.5 bg-gray-400 dark:bg-gray-500"></div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Target Level</span>
                            </div>
                        </div>
                    </div>

                    <!-- Trend Chart -->
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Score Trend (6 Bulan Terakhir)</h3>
                        <div class="h-64 flex items-end justify-between gap-2">
                            <div 
                                v-for="(item, index) in trendData" 
                                :key="index"
                                class="flex-1 flex flex-col items-center"
                            >
                                <div class="w-full flex flex-col items-center justify-end h-48">
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">{{ item.score }}%</span>
                                    <div 
                                        class="w-full max-w-[40px] rounded-t-lg transition-all duration-500"
                                        :class="index === trendData.length - 1 ? 'bg-gradient-to-t from-emerald-500 to-teal-400' : 'bg-gray-200 dark:bg-white/10'"
                                        :style="{ height: `${getTrendHeight(item.score)}%` }"
                                    ></div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ item.month }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assessment Summary & Roadmap -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Assessments -->
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-white/10">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Assessment Terbaru</h3>
                        </div>
                        <div class="divide-y divide-gray-200 dark:divide-white/5">
                            <div 
                                v-for="assessment in assessments" 
                                :key="assessment.id"
                                class="p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ assessment.name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ assessment.date }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="text-right">
                                            <p class="text-sm font-bold" :class="assessment.score >= assessment.target ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
                                                {{ assessment.score }}%
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Target: {{ assessment.target }}%</p>
                                        </div>
                                        <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(assessment.status)]">
                                            {{ getStatusLabel(assessment.status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Roadmap Preview -->
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Capability Roadmap</h3>
                            <Link 
                                :href="route('roadmap.index')"
                                class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline"
                            >
                                Lihat Semua →
                            </Link>
                        </div>
                        <div class="divide-y divide-gray-200 dark:divide-white/5">
                            <div 
                                v-for="item in roadmap.slice(0, 4)" 
                                :key="item.id"
                                class="p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-xs font-mono rounded">
                                                {{ item.domain }}
                                            </span>
                                            <span :class="['px-2 py-0.5 text-xs font-medium rounded', getPriorityColor(item.priority)]">
                                                {{ item.priority }}
                                            </span>
                                        </div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mt-2">{{ item.title }}</h4>
                                        <div class="flex items-center gap-4 mt-2">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                Level {{ item.currentLevel }} → {{ item.targetLevel }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ item.timeline }}</span>
                                        </div>
                                    </div>
                                    <div class="w-24">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Progress</span>
                                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ item.progress }}%</span>
                                        </div>
                                        <div class="h-1.5 bg-gray-100 dark:bg-white/10 rounded-full overflow-hidden">
                                            <div 
                                                class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all"
                                                :style="{ width: `${item.progress}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
