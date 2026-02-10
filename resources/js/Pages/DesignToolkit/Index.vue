<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import { PlusIcon, LinkIcon, EyeIcon, TrashIcon } from '@heroicons/vue/24/outline';

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

const combinedAssessments = computed(() => (props.assessmentsSame || []));
const activeAssessment = computed(() => {
    if (!props.assessmentId) return null;
    return combinedAssessments.value.find(
        (item) => String(item.assessment_id) === String(props.assessmentId)
    );
});

// Delete handling
const showConfirmModal = ref(false);
const itemToDelete = ref(null);
const deleteLoading = ref(false);

const handleDelete = (assessment) => {
    itemToDelete.value = assessment;
    showConfirmModal.value = true;
};

const confirmDelete = () => {
    if (!itemToDelete.value) return;
    deleteLoading.value = true;
    router.delete(route('design-toolkit.destroy', itemToDelete.value.assessment_id), {
        onFinish: () => {
            deleteLoading.value = false;
        },
        onSuccess: () => {
            showConfirmModal.value = false;
            itemToDelete.value = null;
        },
    });
};

const closeConfirmModal = () => {
    showConfirmModal.value = false;
    itemToDelete.value = null;
    deleteLoading.value = false;
};

const breadcrumbs = [
    { label: 'Dashboard', href: props.routes.dashboard },
    { label: 'Design Toolkit' }
];

const formatDateTime = (value) => {
    if (!value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};
</script>

<template>
    <AuthenticatedLayout title="Design Toolkit Dashboard">
        <template #header>
            <PageHeader 
                title="Design Toolkit"
                subtitle="Manage assessments and continue your design workflow"
                :breadcrumbs="breadcrumbs"
            >
                <template #actions>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-slate-900 rounded-full hover:bg-slate-800 transition shadow-sm"
                    >
                        <PlusIcon class="w-4 h-4" />
                        Create
                    </button>
                    <button
                        @click="openJoinModal"
                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-full bg-white text-slate-700 hover:bg-slate-50 border border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600 dark:hover:bg-slate-700 transition shadow-sm"
                    >
                        <LinkIcon class="w-4 h-4" />
                        Join
                    </button>
                </template>
            </PageHeader>
        </template>

        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Overview -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-white/10 p-4 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-6">
                        <div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Total Assessments</p>
                            <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ combinedAssessments.length }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a
                            v-if="routes.roadmap"
                            :href="routes.roadmap"
                            class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-full bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm"
                        >
                            Roadmap
                        </a>
                    </div>
                </div>
            </div>

            <!-- Assessment List -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-200 dark:border-white/5 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-white tracking-tight">
                        Recent Assessments
                    </h2>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">
                        Showing {{ combinedAssessments.length }} item(s)
                    </span>
                </div>

                <div v-if="isGuest" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                    Guest mode does not show assessment history. Please log in to see your assessments.
                </div>

                <div v-else-if="combinedAssessments.length === 0" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                    No assessments found. Create a new one or join with a code.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-white border-b border-slate-700">
                                <th class="pl-5 pr-3 py-3 text-[10px] font-semibold uppercase tracking-wider w-14">
                                    No
                                </th>
                                <th class="px-3 py-3 text-[10px] font-semibold uppercase tracking-wider">
                                    Assessment Code
                                </th>
                                <th class="px-3 py-3 text-[10px] font-semibold uppercase tracking-wider w-28 text-center">
                                    Year
                                </th>
                                <th class="px-3 py-3 text-[10px] font-semibold uppercase tracking-wider w-32 text-center">
                                    Created At
                                </th>
                                <th class="px-3 py-3 text-[10px] font-semibold uppercase tracking-wider w-32 text-center">
                                    Updated At
                                </th>
                                <th class="pl-3 pr-5 py-3 text-[10px] font-semibold uppercase tracking-wider w-32 text-right">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="(item, index) in combinedAssessments" :key="item.assessment_id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                                <td class="pl-5 pr-3 py-3.5 text-sm font-medium text-slate-500 dark:text-slate-400">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-3 py-3.5">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                        {{ item.kode_assessment }}
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ item.tahun || '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    <span class="text-[11px] text-slate-600 dark:text-slate-300">
                                        {{ formatDateTime(item.created_at) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    <span class="text-[11px] text-slate-600 dark:text-slate-300">
                                        {{ formatDateTime(item.updated_at) }}
                                    </span>
                                </td>
                                <td class="pl-3 pr-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button @click="openAssessment(item.kode_assessment)"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-md transition-all dark:text-slate-200 dark:bg-slate-700/50 dark:hover:bg-slate-700"
                                            title="Open Assessment">
                                            <EyeIcon class="w-4 h-4" />
                                            Open
                                        </button>
                                        <button v-if="item.can_delete" @click="handleDelete(item)"
                                            class="inline-flex items-center justify-center p-1.5 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-md transition-all dark:text-rose-400 dark:hover:bg-rose-900/20"
                                            title="Delete Assessment">
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="combinedAssessments.length > 0" class="h-px w-full bg-slate-100 dark:bg-slate-800"></div>
            </div>
        </div>

        <!-- Join Modal -->
        <div v-if="isJoinModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-md shadow-2xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Join Assessment</h3>
                    <button @click="closeModals" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>
                
                <form @submit.prevent="handleJoin" class="space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Assessment Code</label>
                        <input 
                            v-model="joinForm.kode_assessment"
                            type="text" 
                            placeholder="Enter 6-digit code"
                            class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-slate-400 focus:border-slate-400 outline-none transition-all dark:text-white font-mono text-sm"
                            required
                        />
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">Ask your administrator for the assessment code.</p>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" @click="closeModals" class="flex-1 px-4 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg text-sm font-semibold transition-all">Cancel</button>
                        <button type="submit" :disabled="joinForm.processing" class="flex-1 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-sm font-semibold shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ joinForm.processing ? 'Joining...' : 'Join Assessment' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-md shadow-2xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">New Assessment</h3>
                    <button @click="closeModals" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>
                
                <form @submit.prevent="handleCreate" class="space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Assessment Year</label>
                        <input 
                            v-model="createForm.tahun"
                            type="number" 
                            min="2000"
                            max="2100"
                            class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-slate-400 focus:border-slate-400 outline-none transition-all dark:text-white font-semibold text-sm"
                            required
                        />
                    </div>

                    <div class="flex gap-2">
                        <button type="button" @click="closeModals" class="flex-1 px-4 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg text-sm font-semibold transition-all">Cancel</button>
                        <button type="submit" :disabled="createForm.processing" class="flex-1 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-sm font-semibold shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ createForm.processing ? 'Creating...' : 'Create Assessment' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>

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

<style scoped>
/* Modern scrollbar styling */
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}
.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
