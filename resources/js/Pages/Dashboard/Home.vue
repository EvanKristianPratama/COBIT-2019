<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const tools = [
    {
        key: 'components',
        name: 'COBIT Components',
        description: 'Kamus komponen COBIT',
        url: '/cobit-dictionary',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        color: 'amber',
    },
    {
        key: 'toolkit',
        name: 'Design I&T Governance',
        description: 'Manajemen tata kelola TI',
        url: '/cobit2019/cobit_home',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        color: 'red',
    },
    {
        key: 'design-toolkit',
        name: 'Design Toolkit (Vue)',
        description: 'Design Factors DF1-DF10 + Target',
        url: '/design-toolkit',
        icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
        color: 'purple',
        badge: 'DEV',
        isDisabled: true,
    },
    {
        key: 'assessment',
        name: 'Assessment Maturity',
        description: 'Evaluasi tata kelola TI',
        url: '/assessment-eval',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        color: 'blue',
        badge: 'DEV',
        isDisabled: true,
    },
    {
        key: 'spreadsheet',
        name: 'Spreadsheet Tools',
        description: 'Tools untuk analisis data',
        url: '/spreadsheet',
        icon: 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
        color: 'emerald',
    },
    {
        key: 'reporting',
        name: 'Reporting',
        description: 'Laporan hasil assessment & roadmap capability',
        url: '/reporting',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        color: 'teal',
    },
];


const getColorBgClass = (color) => {
    const map = {
        amber: 'bg-amber-500/10 dark:bg-amber-500/20',
        red: 'bg-red-500/10 dark:bg-red-500/20',
        blue: 'bg-blue-500/10 dark:bg-blue-500/20',
        emerald: 'bg-emerald-500/10 dark:bg-emerald-500/20',
        purple: 'bg-purple-500/10 dark:bg-purple-500/20',
        teal: 'bg-teal-500/10 dark:bg-teal-500/20',
    };
    return map[color] || 'bg-gray-500/10';
};

const getTextColorClass = (color) => {
    const map = {
        amber: 'text-amber-500',
        red: 'text-red-500',
        blue: 'text-blue-500',
        emerald: 'text-emerald-500',
        purple: 'text-purple-500',
        teal: 'text-teal-500',
    };
    return map[color] || 'text-gray-500';
};

const getAccentClass = (color) => {
    const map = {
        amber: 'bg-amber-500',
        red: 'bg-red-500',
        blue: 'bg-blue-500',
        emerald: 'bg-emerald-500',
        purple: 'bg-purple-500',
        teal: 'bg-teal-500',
    };
    return map[color] || 'bg-gray-500';
};

const handleToolClick = (tool) => {
    if (tool.isDisabled) {
        pendingUrl.value = tool.url;
        showDevModal.value = true;
    }
};

const gotoDevFeature = () => {
    if (pendingUrl.value) {
        window.location.href = pendingUrl.value;
    }
};

const showDevModal = ref(false);
const pendingUrl = ref(null);
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <div class="space-y-6">
            <PageHeader 
                title="COBIT 2019 Tools" 
                subtitle="Akses cepat ke alat tata kelola COBIT 2019"
                :breadcrumbs="[{ label: 'Dashboard' }]"
            />

            <!-- Tools Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <component
                :is="tool.isDisabled ? 'div' : 'a'"
                v-for="tool in tools"
                :key="tool.key"
                :href="tool.isDisabled ? undefined : tool.url"
                @click="handleToolClick(tool)"
                class="group relative bg-white dark:bg-[#1a1a1a] rounded-2xl p-6 border border-gray-200/80 dark:border-white/5 hover:border-gray-300 dark:hover:border-white/10 transition-all duration-300 shadow-sm hover:shadow-lg hover:shadow-gray-200/50 dark:hover:shadow-black/20"
                :class="[tool.isDisabled ? 'cursor-default opacity-90' : 'cursor-pointer hover:-translate-y-0.5']"
            >
                <!-- Color accent bar -->
                <div 
                    class="absolute top-0 left-6 right-6 h-1 rounded-b-full opacity-0 group-hover:opacity-100 transition-opacity"
                    :class="getAccentClass(tool.color)"
                ></div>
                
                <div class="flex items-start justify-between">
                    <div 
                        class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors"
                        :class="getColorBgClass(tool.color)"
                    >
                        <svg 
                            class="w-6 h-6"
                            :class="getTextColorClass(tool.color)"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="tool.icon" />
                        </svg>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-white/5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-lg">{{ tool.name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ tool.description }}</p>
                </div>

                <!-- Status badge -->
                <div class="mt-4 flex items-center">
                    <span v-if="tool.badge" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 uppercase tracking-wider">
                        {{ tool.badge }}
                    </span>
                    <span v-else class="inline-flex items-center text-xs font-medium text-emerald-600 dark:text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        Tersedia
                    </span>
                </div>
            </component>
        </div>
        </div>

        <!-- Development Alert Modal -->
        <ConfirmModal
            :show="showDevModal"
            title="Sedang Dikembangkan"
            message="Fitur ini masih dalam tahap pengembangan dan mungkin belum stabil. Apakah Anda tetap ingin masuk?"
            confirm-text="Ya, Tetap Masuk"
            cancel-text="Batal"
            type="warning"
            @close="showDevModal = false"
            @confirm="gotoDevFeature"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
