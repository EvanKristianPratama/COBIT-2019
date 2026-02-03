<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    PlusIcon, 
    LinkIcon, 
    ArrowRightIcon, 
    FolderIcon,
    ChartBarIcon,
    TrophyIcon,
    ComputerDesktopIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    auth: Object,
    isGuest: Boolean,
    assessmentsSame: Array,
    assessmentsOther: Array,
    assessmentId: [String, Number],
    routes: Object,
});

const isJoinModalOpen = ref(false);
const isCreateModalOpen = ref(false);

const joinForm = useForm({
    kode_assessment: '',
});

const createForm = useForm({
    kode_assessment: 'new',
    tahun: new Date().getFullYear(),
});

const openJoinModal = () => isJoinModalOpen.value = true;
const openCreateModal = () => isCreateModalOpen.value = true;

const closeModals = () => {
    isJoinModalOpen.value = false;
    isCreateModalOpen.value = false;
    joinForm.reset();
    createForm.reset();
};

const handleJoin = () => {
    joinForm.post(props.routes.join, {
        onSuccess: () => closeModals(),
    });
};

const handleCreate = () => {
    createForm.post(props.routes.join, {
        onSuccess: () => closeModals(),
    });
};

const openAssessment = (kode) => {
    router.post(props.routes.join, { kode_assessment: kode });
};

const breadcrumbs = [
    { label: 'Dashboard', href: props.routes.dashboard },
    { label: 'Design Toolkit' }
];
</script>

<template>
    <AuthenticatedLayout title="Design Toolkit Dashboard">
        <template #header>
            <PageHeader 
                title="Design Toolkit"
                subtitle="Tailored I&T Governance System Design"
                :breadcrumbs="breadcrumbs"
            />
        </template>

        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Hero Banner -->
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-8 text-white shadow-xl shadow-emerald-500/10">
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="flex-1 space-y-4">
                        <h2 class="text-3xl font-bold leading-tight">
                            COBIT 2019 <br/>
                            <span class="text-emerald-100 italic">Governance Design Toolkit</span>
                        </h2>
                        <p class="text-emerald-50/80 max-w-lg">
                            Analyze design factors to create a governance system tailored to your enterprise's specific context and objectives.
                        </p>
                        
                        <div class="flex flex-wrap gap-4 pt-4">
                            <a :href="routes.target_capability" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl transition-all font-medium">
                                <TrophyIcon class="w-5 h-5" />
                                Target Capability
                            </a>
                            <a :href="routes.target_maturity" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl transition-all font-medium">
                                <ChartBarIcon class="w-5 h-5" />
                                Target Maturity
                            </a>
                            <a :href="routes.summaryStep2" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 hover:bg-white/30 border border-white/20 rounded-xl transition-all font-bold">
                                <span class="bg-white/20 w-6 h-6 flex items-center justify-center rounded-md text-xs">2</span>
                                Initial Scope
                            </a>
                            <a :href="routes.summaryStep3" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 hover:bg-white/30 border border-white/20 rounded-xl transition-all font-bold">
                                <span class="bg-white/20 w-6 h-6 flex items-center justify-center rounded-md text-xs">3</span>
                                Refined Scope
                            </a>
                        </div>
                    </div>
                    <div class="hidden lg:block">
                        <ComputerDesktopIcon class="w-32 h-32 text-emerald-100/20" />
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Assessment List -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <FolderIcon class="w-6 h-6 text-emerald-500" />
                            Recent Assessments
                        </h3>
                    </div>

                    <div v-if="isGuest" class="bg-gray-100 dark:bg-gray-800/50 rounded-2xl p-6 border border-gray-200 dark:border-white/5 text-center">
                        <p class="text-gray-500 dark:text-gray-400">Guest mode does not show assessment history. Please log in to see your assessments.</p>
                    </div>

                    <div v-else-if="assessmentsSame.length === 0 && assessmentsOther.length === 0" class="bg-white dark:bg-gray-800 rounded-2xl p-12 border border-gray-200 dark:border-white/5 text-center shadow-sm">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <FolderIcon class="w-8 h-8 text-gray-400" />
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">No assessments found</h4>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">Start a new project or join an existing one using a code.</p>
                    </div>

                    <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-white/5 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5">
                                    <tr>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Assessment Name</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-right"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                    <tr v-for="item in assessmentsSame" :key="item.assessment_id" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 dark:text-white">{{ item.kode_assessment }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ item.instansi }} ({{ item.tahun }})</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400">
                                                Active
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button @click="openAssessment(item.kode_assessment)" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-all opacity-0 group-hover:opacity-100">
                                                Open
                                                <ArrowRightIcon class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-for="item in assessmentsOther" :key="item.assessment_id" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 dark:text-white">{{ item.kode_assessment }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ item.instansi }} ({{ item.tahun }})</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                External
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button @click="openAssessment(item.kode_assessment)" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-all opacity-0 group-hover:opacity-100">
                                                Open
                                                <ArrowRightIcon class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Actions -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-white/5 shadow-sm space-y-4">
                        <h4 class="font-bold text-gray-900 dark:text-white">Quick Actions</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Join a collaborative assessment or start a new governance tailoring session.</p>
                        
                        <div class="space-y-3 pt-2">
                            <button @click="openCreateModal" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-lg shadow-emerald-600/20 transition-all font-bold">
                                <PlusIcon class="w-5 h-5" />
                                Create Assessment
                            </button>
                            <button @click="openJoinModal" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all font-bold">
                                <LinkIcon class="w-5 h-5" />
                                Join with Code
                            </button>
                        </div>
                    </div>

                    <!-- Info Tip -->
                    <div class="bg-emerald-50 dark:bg-emerald-500/5 rounded-2xl p-6 border border-emerald-100 dark:border-emerald-500/20">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <ComputerDesktopIcon class="w-6 h-6 text-emerald-600" />
                            </div>
                            <div>
                                <h5 class="font-bold text-emerald-900 dark:text-emerald-400 text-sm">Design Tip</h5>
                                <p class="text-xs text-emerald-700 dark:text-emerald-500/80 mt-1 leading-relaxed">
                                    Assess all 10 Design Factors to get the most accurate governance objective mapping for your organization.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Join Modal -->
        <div v-if="isJoinModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 w-full max-w-md shadow-2xl border border-gray-200 dark:border-white/10">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Join Assessment</h3>
                    <button @click="closeModals" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>
                
                <form @submit.prevent="handleJoin" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Assessment Code</label>
                        <input 
                            v-model="joinForm.kode_assessment"
                            type="text" 
                            placeholder="Enter 6-digit code"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition-all dark:text-white font-mono"
                            required
                        />
                        <p class="text-xs text-gray-500 mt-2">Ask your administrator for the assessment code.</p>
                    </div>

                    <div class="flex gap-4">
                        <button type="button" @click="closeModals" class="flex-1 px-6 py-3 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl font-bold">Cancel</button>
                        <button type="submit" :disabled="joinForm.processing" class="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-xl font-bold shadow-lg shadow-emerald-600/20">
                            {{ joinForm.processing ? 'Joining...' : 'Join' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 w-full max-w-md shadow-2xl border border-gray-200 dark:border-white/10">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">New Assessment</h3>
                    <button @click="closeModals" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>
                
                <form @submit.prevent="handleCreate" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Assessment Year</label>
                        <input 
                            v-model="createForm.tahun"
                            type="number" 
                            min="2000"
                            max="2100"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition-all dark:text-white font-bold"
                            required
                        />
                    </div>

                    <div class="flex gap-4">
                        <button type="button" @click="closeModals" class="flex-1 px-6 py-3 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl font-bold">Cancel</button>
                        <button type="submit" :disabled="createForm.processing" class="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-xl font-bold shadow-lg shadow-emerald-600/20">
                            {{ createForm.processing ? 'Creating...' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Optional: Custom scrollbar styling if needed */
.overflow-x-auto::-webkit-scrollbar {
    height: 4px;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
</style>
