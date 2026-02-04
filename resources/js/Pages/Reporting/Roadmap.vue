<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    roadmapItems: Array,
});

const getStatusColor = (status) => {
    const colors = {
        completed: 'bg-emerald-500',
        in_progress: 'bg-blue-500',
        planned: 'bg-gray-400 dark:bg-gray-600',
    };
    return colors[status] || colors.planned;
};

const getStatusBadge = (status) => {
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

const getDomainColor = (domain) => {
    if (domain.startsWith('EDM')) return 'from-blue-500 to-blue-600';
    if (domain.startsWith('APO')) return 'from-purple-500 to-purple-600';
    if (domain.startsWith('BAI')) return 'from-amber-500 to-amber-600';
    if (domain.startsWith('DSS')) return 'from-emerald-500 to-emerald-600';
    if (domain.startsWith('MEA')) return 'from-red-500 to-red-600';
    return 'from-gray-500 to-gray-600';
};
</script>

<template>
    <Head title="Capability Roadmap" />

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
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Capability Roadmap 2026</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Rencana peningkatan capability level per quarter</p>
                </div>

                <!-- Demo Notice -->
                <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-sm text-amber-700 dark:text-amber-300">Demo Mode - Data ini adalah contoh roadmap untuk demonstrasi.</p>
                    </div>
                </div>

                <!-- Legend -->
                <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4 mb-8">
                    <div class="flex flex-wrap items-center gap-6">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</span>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Selesai</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Berjalan</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Direncanakan</span>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="relative">
                    <!-- Vertical line -->
                    <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-white/10"></div>

                    <div class="space-y-8">
                        <div 
                            v-for="(quarter, idx) in roadmapItems" 
                            :key="quarter.quarter"
                            class="relative"
                        >
                            <!-- Quarter Header -->
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm z-10">
                                    {{ quarter.quarter }}
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ quarter.quarter }}</h3>
                            </div>

                            <!-- Quarter Items -->
                            <div class="ml-16 space-y-4">
                                <div 
                                    v-for="item in quarter.items" 
                                    :key="`${quarter.quarter}-${item.domain}`"
                                    class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-5 hover:shadow-lg transition-all"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div :class="['w-10 h-10 rounded-lg bg-gradient-to-br flex items-center justify-center text-white text-xs font-bold', getDomainColor(item.domain)]">
                                                    {{ item.domain }}
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.title }}</h4>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.domain }}</p>
                                                </div>
                                            </div>
                                            
                                            <!-- Level Progress -->
                                            <div class="mt-4">
                                                <div class="flex items-center gap-4 mb-2">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Capability Level:</span>
                                                    <div class="flex items-center gap-2">
                                                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-xs font-medium rounded">
                                                            Level {{ item.current }}
                                                        </span>
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                        </svg>
                                                        <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-medium rounded">
                                                            Level {{ item.target }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Level Bar -->
                                                <div class="flex items-center gap-1">
                                                    <div 
                                                        v-for="level in 5" 
                                                        :key="level"
                                                        :class="[
                                                            'flex-1 h-2 rounded-full',
                                                            level <= item.current 
                                                                ? getStatusColor(item.status)
                                                                : level <= item.target 
                                                                    ? 'bg-gray-200 dark:bg-white/10 border border-dashed border-gray-300 dark:border-gray-600' 
                                                                    : 'bg-gray-100 dark:bg-white/5'
                                                        ]"
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Status Badge -->
                                        <span :class="['px-3 py-1.5 text-xs font-medium rounded-full whitespace-nowrap', getStatusBadge(item.status)]">
                                            {{ getStatusLabel(item.status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Footer -->
                <div class="mt-12 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl p-6 text-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold">Target Akhir Tahun 2026</h3>
                            <p class="text-sm text-white/80 mt-1">Mencapai rata-rata capability level 3.5 di semua domain</p>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <p class="text-3xl font-bold">12</p>
                                <p class="text-xs text-white/80">Total Initiatives</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-bold">3</p>
                                <p class="text-xs text-white/80">In Progress</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-bold">2</p>
                                <p class="text-xs text-white/80">Completed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
