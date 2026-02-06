<script setup>
/**
 * AssessmentEval/Index.vue - Refined Assessment list page
 */
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import PageHeader from '@/Components/PageHeader.vue';
import HeroCard from './Components/HeroCard.vue';
import AssessmentTable from './Components/AssessmentTable.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import CreateModal from './Components/CreateModal.vue';
import { 
    ListBulletIcon, 
    UsersIcon, 
    ChartBarIcon, 
    ChevronLeftIcon, 
    ChevronRightIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    myAssessments: { type: Object, default: () => ({ data: [], current_page: 1 }) },
    otherAssessments: { type: Object, default: () => ({ data: [], current_page: 1 }) },
    totalAssessments: { type: Number, default: 0 },
    finishedAssessments: { type: Number, default: 0 },
    draftAssessments: { type: Number, default: 0 },
    routes: { type: Object, default: () => ({}) }
});

// Modal State
const showConfirmModal = ref(false);
const isCreateModalOpen = ref(false);
const itemToDelete = ref(null);
const deleteLoading = ref(false);

const breadcrumbs = [
    { label: 'Dashboard', href: props.routes.home },
    { label: 'Assessments' }
];

// Handle view assessment
const handleView = (assessment) => {
    router.visit(route('assessment-eval.show', assessment.eval_id));
};

// Handle view report
const handleReport = (assessment) => {
    router.visit(route('assessment-eval.report', assessment.eval_id));
};



// Handle delete assessment (Open Modal)
const handleDelete = (assessment) => {
    itemToDelete.value = assessment;
    showConfirmModal.value = true;
};

// Confirm Delete
const confirmDelete = () => {
    if (!itemToDelete.value) return;

    deleteLoading.value = true;
    router.delete(route('assessment-eval.destroy', itemToDelete.value.eval_id), {
        onSuccess: () => {
            closeConfirmModal();
            // Optional: Show toast success
        },
        onError: () => {
             // Optional: Show toast error
             deleteLoading.value = false;
        },
        onFinish: () => {
            deleteLoading.value = false;
        }
    });
};

const closeConfirmModal = () => {
    showConfirmModal.value = false;
    itemToDelete.value = null;
    deleteLoading.value = false;
};

// Open create modal
const openCreateModal = () => {
    isCreateModalOpen.value = true;
};

// Close create modal
const closeCreateModal = () => {
    isCreateModalOpen.value = false;
};
</script>

<template>
    <Head title="COBIT Assessment" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-4">
                <Breadcrumbs :items="breadcrumbs" />
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">
                            Capability & Maturity Governance Assessment
                        </h2>
                        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white flex items-center gap-3">
                            Dashboard Overview
                        </h1>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-8 pb-12">
            <!-- Hero Stats Cards -->
            <HeroCard 
                :totalAssessments="totalAssessments"
                :finishedAssessments="finishedAssessments"
                :draftAssessments="draftAssessments"
            />

            <!-- Daftar Assessments Section -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-white/10 overflow-hidden">
                
                <!-- Card Header -->
                <div class="px-6 py-5 border-b border-slate-200 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight">
                        Assessment List
                    </h2>
                    
                    <div class="flex items-center gap-3">
                        <a :href="route('assessment-eval.evidence.library')" 
                               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700 dark:hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                            </svg>
                            Master Evidence
                        </a>

                        <a v-if="routes.reportAll" :href="routes.reportAll" 
                               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all shadow-sm dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700 dark:hover:text-white">
                            <ChartBarIcon class="w-4 h-4" />
                            Summarized Report
                        </a>

                        <button @click="openCreateModal"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 active:bg-indigo-800 transition-all shadow-md shadow-indigo-600/20 hover:shadow-lg hover:shadow-indigo-600/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Create Assessment
                        </button>
                    </div>
                </div>

                <AssessmentTable 
                    :assessments="myAssessments.data || []"
                    :currentPage="myAssessments.current_page || 1"
                    :showActions="true"
                    @view="handleView"
                    @report="handleReport"
                    @delete="handleDelete"
                />

                <!-- Pagination -->
                <div v-if="myAssessments.last_page > 1" class="px-6 py-3 bg-white dark:bg-white/5 border-t border-slate-100 dark:border-white/5 flex items-center justify-end">
                    <nav class="flex items-center gap-2">
                        <button v-for="link in myAssessments.links" :key="link.label"
                            @click.prevent="link.url && router.visit(link.url)"
                            :disabled="!link.url"
                            class="inline-flex items-center justify-center h-8 min-w-[32px] px-2 text-sm font-medium transition-colors"
                            :class="[
                                link.active 
                                    ? 'text-slate-900 dark:text-white font-bold' 
                                    : 'text-slate-500 dark:text-slate-400',
                                !link.url && 'opacity-50 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />

                    </nav>
                </div>
            </div>
            

        </div>
        
        <!-- Create Modal -->
        <CreateModal 
            :isOpen="isCreateModalOpen"
            :createRoute="routes.create || '/assessment-eval'"
            @close="closeCreateModal"
        />
        
    </AuthenticatedLayout>

    <!-- Confirm Modal -->
    <ConfirmModal 
        :show="showConfirmModal"
        title="Hapus Assessment"
        message="Apakah Anda yakin ingin menghapus assessment ini? Data yang sudah dihapus tidak dapat dikembalikan."
        confirmText="Ya, Hapus"
        cancelText="Batal"
        type="danger"
        :loading="deleteLoading"
        @close="closeConfirmModal"
        @confirm="confirmDelete"
    />
</template>
