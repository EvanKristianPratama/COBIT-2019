<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import EvidenceModal from './Components/EvidenceModal.vue';
import ImportLibraryModal from './Components/ImportLibraryModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { 
    PlusIcon, 
    ArrowLeftIcon, 
    DocumentTextIcon, 
    MagnifyingGlassIcon,
    PencilSquareIcon,
    ArrowDownTrayIcon,
    TrashIcon,
    LinkIcon,
    FolderIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    evaluation: Object,
    evidences: Array,
    evalId: [String, Number],
    isOwner: Boolean
});

// Modal State
const isModalOpen = ref(false);
const isImportModalOpen = ref(false);
const editingEvidence = ref(null);

const openCreateModal = () => {
    editingEvidence.value = null;
    isModalOpen.value = true;
};

const openEditModal = (evidence) => {
    editingEvidence.value = evidence;
    isModalOpen.value = true;
};

const openImportModal = () => {
    isImportModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingEvidence.value = null;
};

// Calculate breadcrumbs
const breadcrumbs = [
    { label: 'Dashboard', url: route('assessment-eval.list') },
    { label: 'Assessment', url: route('assessment-eval.show', props.evalId) },
    { label: 'Evidence Library' }
];

const searchQuery = ref('');

// Flash message handling
const page = usePage();
const showSuccessModal = ref(false);
const successModalMessage = ref('');
const deleteForm = useForm({});

watch(() => page.props.flash?.success, (message) => {
    if (message) {
        successModalMessage.value = message;
        showSuccessModal.value = true;
    }
}, { immediate: true });

// Delete Functionality
const showConfirmModal = ref(false);
const itemToDelete = ref(null);
const deleteLoading = ref(false);

const confirmDelete = (item) => {
    itemToDelete.value = item;
    showConfirmModal.value = true;
};

const handleDeleteConfirm = () => {
    if (!itemToDelete.value) return;
    deleteLoading.value = true;
    deleteForm.delete(route('assessment-eval.evidence.destroy', itemToDelete.value.id), {
        onSuccess: () => {
            showConfirmModal.value = false;
            itemToDelete.value = null;
        },
        onFinish: () => {
            deleteLoading.value = false;
        }
    });
};

const handleSuccess = () => {};
const handleImported = () => {
    router.reload();
};

// Group color helper
const getGroupColor = (grup) => {
    const colors = {
        'EDM': 'bg-rose-100 text-rose-700',
        'APO': 'bg-blue-100 text-blue-700',
        'BAI': 'bg-emerald-100 text-emerald-700',
        'DSS': 'bg-amber-100 text-amber-700',
        'MEA': 'bg-purple-100 text-purple-700',
    };
    return colors[grup] || 'bg-slate-100 text-slate-600';
};

</script>

<template>
    <Head title="Evidence Library" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-4">
                <Breadcrumbs :items="breadcrumbs" />
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                             <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 uppercase tracking-wide">
                                ID: {{ evalId }}
                            </span>
                             <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase tracking-wide">
                                Year: {{ evaluation?.tahun || evaluation?.assessment_year || 'N/A' }}
                            </span>
                        </div>
                        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Evidence Library
                        </h1>
                        <p class="text-slate-500 dark:text-slate-400 font-medium mt-1">
                            Manage master evidence documents for this assessment.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                         <Link :href="route('assessment-eval.show', evalId)" 
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                            <ArrowLeftIcon class="w-4 h-4" />
                            Back to Assessment
                        </Link>

                        <button v-if="isOwner" @click="openImportModal"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100 transition-all">
                            <ArrowDownTrayIcon class="w-4 h-4" />
                            Import from Library
                        </button>
                        
                        <button v-if="isOwner" @click="openCreateModal"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all">
                            <PlusIcon class="w-5 h-5" />
                            Add New Evidence
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-[95rem] mx-auto py-8">
            <!-- Table Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                <!-- Toolbar -->
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">List of Evidence</h3>
                    
                     <div class="relative w-full max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <MagnifyingGlassIcon class="w-5 h-5" />
                        </div>
                        <input type="text" v-model="searchQuery" placeholder="Search documents..." 
                            class="pl-10 w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 transition-all">
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-bold tracking-wider border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-center w-12 sticky left-0 bg-slate-50 dark:bg-slate-800 z-10">No</th>
                                <th class="px-4 py-3 min-w-[300px] sticky left-12 bg-slate-50 dark:bg-slate-800 z-10">Document Title</th>
                                <th class="px-4 py-3">Doc No.</th>
                                <th class="px-4 py-3 text-center">Group</th>
                                <th class="px-4 py-3">Classification</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Sub-Type</th>
                                <th class="px-4 py-3 text-center">Year</th>
                                <th class="px-4 py-3 text-center">Exp. Year</th>
                                <th class="px-4 py-3">Owner</th>
                                <th class="px-4 py-3">Validation</th>
                                <th class="px-4 py-3">Link</th>
                                <th class="px-4 py-3 min-w-[200px]">Summary</th>
                                <th v-if="isOwner" class="px-4 py-3 text-center w-24 sticky right-0 bg-slate-50 dark:bg-slate-800 z-10 border-l border-slate-200 dark:border-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                             <tr v-if="evidences.length === 0" class="bg-white dark:bg-slate-900">
                                <td colspan="14" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <DocumentTextIcon class="w-12 h-12 text-slate-300 mb-3" />
                                        <p class="font-medium">No evidence documents found.</p>
                                        <button v-if="isOwner" @click="openCreateModal" class="mt-2 text-indigo-600 hover:text-indigo-700 font-bold text-sm">Add your first evidence</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="(item, index) in evidences" :key="item.id" 
                                class="bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-4 py-3 text-center font-medium text-slate-400 sticky left-0 bg-white dark:bg-slate-900 group-hover:bg-slate-50 dark:group-hover:bg-slate-800/30 z-10">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-800 dark:text-white sticky left-12 bg-white dark:bg-slate-900 group-hover:bg-slate-50 dark:group-hover:bg-slate-800/30 z-10">
                                    <div class="flex items-center gap-2">
                                        <DocumentTextIcon class="w-4 h-4 text-slate-400 flex-shrink-0" />
                                        <div class="truncate max-w-[300px]" :title="item.judul_dokumen">{{ item.judul_dokumen }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 font-mono text-xs whitespace-nowrap">{{ item.no_dokumen || '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span v-if="item.grup" class="px-2 py-0.5 rounded text-[10px] font-bold" :class="getGroupColor(item.grup)">{{ item.grup }}</span>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                     <span v-if="item.klasifikasi" 
                                        class="px-2 py-0.5 rounded text-[10px] font-bold border"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 border-emerald-200': item.klasifikasi === 'Public',
                                            'bg-blue-50 text-blue-700 border-blue-200': item.klasifikasi === 'Internal',
                                            'bg-amber-50 text-amber-700 border-amber-200': item.klasifikasi === 'Confidential',
                                            'bg-rose-50 text-rose-700 border-rose-200': item.klasifikasi === 'Restricted'
                                        }">
                                        {{ item.klasifikasi }}
                                    </span>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap text-xs">{{ item.tipe || '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap text-xs">{{ item.ket_tipe || '-' }}</td>
                                <td class="px-4 py-3 text-center text-slate-600 text-xs">{{ item.tahun_terbit || '-' }}</td>
                                <td class="px-4 py-3 text-center text-slate-600 text-xs">{{ item.tahun_kadaluarsa || '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap text-xs">{{ item.pemilik_dokumen || '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap text-xs">{{ item.pengesahan || '-' }}</td>
                                <td class="px-4 py-3">
                                    <a v-if="item.link" :href="item.link" target="_blank" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1 text-xs font-medium">
                                        <LinkIcon class="w-4 h-4" /> Open
                                    </a>
                                    <span v-else class="text-slate-400 text-xs">-</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-xs max-w-[250px] truncate" :title="item.summary">{{ item.summary || '-' }}</td>
                                <td v-if="isOwner" class="px-4 py-3 sticky right-0 bg-white dark:bg-slate-900 group-hover:bg-slate-50 dark:group-hover:bg-slate-800/30 z-10 border-l border-slate-200 dark:border-slate-700">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="openEditModal(item)" 
                                            class="p-1.5 text-amber-600 hover:bg-amber-100 rounded-lg transition-colors" title="Edit">
                                            <PencilSquareIcon class="w-4 h-4" />
                                        </button>
                                        <button @click="confirmDelete(item)" 
                                            class="p-1.5 text-rose-600 hover:bg-rose-100 rounded-lg transition-colors" title="Delete">
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <EvidenceModal 
            :isOpen="isModalOpen" 
            :evalId="evalId" 
            :evidence="editingEvidence" 
            @close="closeModal"
            @success="handleSuccess"
        />

        <ImportLibraryModal
            v-if="isImportModalOpen"
            :isOpen="isImportModalOpen"
            :evalId="evalId"
            @close="isImportModalOpen = false"
            @imported="handleImported"
        />

        <ConfirmModal 
            :show="showConfirmModal"
            title="Delete Evidence"
            message="Are you sure you want to delete this evidence? This action cannot be undone."
            confirmText="Yes, Delete"
            cancelText="Cancel"
            type="danger"
            :loading="deleteLoading"
            @close="showConfirmModal = false"
            @confirm="handleDeleteConfirm"
        />

        <ConfirmModal 
            :show="showSuccessModal"
            title="Success"
            :message="successModalMessage"
            confirmText="OK"
            cancelText=""
            type="success"
            @close="showSuccessModal = false"
            @confirm="showSuccessModal = false"
        />
    </AuthenticatedLayout>
</template>
