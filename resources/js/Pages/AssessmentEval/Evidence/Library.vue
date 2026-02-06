<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EvidenceModal from './Components/EvidenceModal.vue';
import MappingModal from './Components/MappingModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue'; // Updated import
import { debounce } from 'lodash';
import { 
    FolderIcon, 
    DocumentTextIcon, 
    MagnifyingGlassIcon, 
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    LinkIcon,
    FolderOpenIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    evidences: Object,
    filters: Object,
    assessments: { type: Array, default: () => [] }
});

// Search & Filter
const searchQuery = ref(props.filters.search || '');
const selectedGroup = ref(props.filters.group || '');
const selectedYear = ref(props.filters.year || '');
const selectedEvalId = ref(props.filters.eval_id || '');

// Groups
const groups = ['EDM', 'APO', 'BAI', 'DSS', 'MEA'];

const handleFilter = () => {
    router.get(route('assessment-eval.evidence.library'), {
        search: searchQuery.value,
        group: selectedGroup.value,
        year: selectedYear.value,
        eval_id: selectedEvalId.value
    }, {
        preserveState: true,
        replace: true
    });
};

// Debounced search
const updateSearch = debounce((value) => {
    router.get(route('assessment-eval.evidence.library'), { 
        search: value,
        group: selectedGroup.value,
        year: selectedYear.value,
        eval_id: selectedEvalId.value
    }, { preserveState: true, preserveScroll: true, replace: true });
}, 500);

watch(searchQuery, (val) => updateSearch(val));
watch([selectedGroup, selectedYear, selectedEvalId], () => {
    handleFilter();
});

// Modal State
const isModalOpen = ref(false);
const isMappingOpen = ref(false);
const editingEvidence = ref(null);
const mappingEvidence = ref(null);

const openCreateModal = () => {
    editingEvidence.value = null;
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    editingEvidence.value = item;
    isModalOpen.value = true;
};

const openMappingModal = (item) => {
    mappingEvidence.value = item;
    isMappingOpen.value = true;
};

const deleteForm = useForm({});
const showConfirmModal = ref(false); // State for confirm modal
const showSuccessModal = ref(false); // State for success modal
const successModalMessage = ref('');
const itemToDelete = ref(null);
const deleteLoading = ref(false);

// Flash message handling
const page = usePage();
watch(() => page.props.flash?.success, (message) => {
    if (message) {
        successModalMessage.value = message;
        showSuccessModal.value = true;
    }
}, { immediate: true });

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
            // Alert handled by watcher
        },
        onError: () => {
            alert('Failed to delete evidence.');
        },
        onFinish: () => {
            deleteLoading.value = false;
        }
    });
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
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wide mb-1">Knowledge Base</h2>
                    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                        <FolderIcon class="w-8 h-8 text-indigo-600" />
                        Master Evidence Library
                    </h1>
                </div>
                <button @click="openCreateModal"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-md transition-all">
                    <PlusIcon class="w-5 h-5" />
                    Add Evidence
                </button>
            </div>
        </template>

        <div class="max-w-[95rem] mx-auto py-8 space-y-6">
            
            <!-- Filters -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-slate-800 flex flex-wrap items-center gap-4">
                <div class="relative flex-1 min-w-[200px] max-w-md">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                    <input type="text" v-model="searchQuery" placeholder="Search..." 
                        class="pl-10 w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <select v-model="selectedGroup"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                    <option value="">All Groups</option>
                    <option v-for="g in groups" :key="g" :value="g">{{ g }}</option>
                </select>

                <!-- Year Filter -->
                <select v-model="selectedYear"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                    <option value="">All Years</option>
                    <option v-for="y in Array.from({length: 10}, (_, i) => new Date().getFullYear() - i)" :key="y" :value="y">{{ y }}</option>
                </select>

                <!-- Assessment Filter -->
                <div class="relative min-w-[200px]">
                    <select v-model="selectedEvalId" 
                        class="w-full pl-3 pr-10 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 transition-all appearance-none cursor-pointer">
                        <option value="">All Assessments</option>
                        <option v-for="assessment in assessments" :key="assessment.eval_id" :value="assessment.eval_id">
                            {{ assessment.judul }}
                        </option>
                    </select>
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                        <FolderIcon class="w-4 h-4" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase whitespace-nowrap w-12 sticky left-0 bg-slate-50 dark:bg-slate-800 z-10">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase whitespace-nowrap min-w-[200px] sticky left-12 bg-slate-50 dark:bg-slate-800 z-10">Document</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase whitespace-nowrap">Group</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase whitespace-nowrap">Classification</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase whitespace-nowrap">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase whitespace-nowrap">Sub-Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase whitespace-nowrap">Year</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase whitespace-nowrap">Exp. Year</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase whitespace-nowrap">Owner</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase whitespace-nowrap">Validation</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase whitespace-nowrap">Link</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase whitespace-nowrap min-w-[200px]">Summary</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase whitespace-nowrap w-24 sticky right-0 bg-slate-50 dark:bg-slate-800 z-10 border-l border-slate-200 dark:border-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="(item, index) in evidences.data" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="px-4 py-3 text-slate-400 sticky left-0 bg-white dark:bg-slate-900 group-hover:bg-slate-50 dark:group-hover:bg-slate-800/30 z-10">{{ (evidences.current_page - 1) * evidences.per_page + index + 1 }}</td>
                            <td class="px-4 py-3 sticky left-12 bg-white dark:bg-slate-900 group-hover:bg-slate-50 dark:group-hover:bg-slate-800/30 z-10">
                                <div class="flex items-center gap-3">
                                    <DocumentTextIcon class="w-5 h-5 text-slate-400 flex-shrink-0" />
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-white truncate max-w-[300px]" :title="item.judul_dokumen">{{ item.judul_dokumen }}</p>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-slate-500">{{ item.no_dokumen || '-' }}</span>
                                            <span v-if="item.id && !item.eval_id" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700">MASTER</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="item.grup" class="px-2 py-1 text-xs font-semibold rounded" :class="getGroupColor(item.grup)">{{ item.grup }}</span>
                                <span v-else class="text-slate-400">-</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                <span v-if="item.klasifikasi" class="px-2 py-1 text-xs rounded border" 
                                    :class="{
                                        'border-green-200 bg-green-50 text-green-700': item.klasifikasi === 'Public',
                                        'border-blue-200 bg-blue-50 text-blue-700': item.klasifikasi === 'Internal',
                                        'border-orange-200 bg-orange-50 text-orange-700': item.klasifikasi === 'Confidential',
                                        'border-red-200 bg-red-50 text-red-700': item.klasifikasi === 'Restricted'
                                    }">
                                    {{ item.klasifikasi }}
                                </span>
                                <span v-else class="text-slate-400">-</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ item.tipe || '-' }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ item.ket_tipe || '-' }}</td>
                            <td class="px-4 py-3 text-slate-600 border-l border-slate-100 dark:border-slate-800">{{ item.tahun_terbit || '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.tahun_kadaluarsa || '-' }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ item.pemilik_dokumen || '-' }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ item.pengesahan || '-' }}</td>
                            <td class="px-4 py-3">
                                <a v-if="item.link" :href="item.link" target="_blank" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1 text-xs font-medium">
                                    <LinkIcon class="w-4 h-4" /> Open
                                </a>
                                <span v-else class="text-slate-400 text-xs">No Link</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-xs max-w-[250px] truncate" :title="item.summary">{{ item.summary || '-' }}</td>
                            <td class="px-4 py-3 sticky right-0 bg-white dark:bg-slate-900 group-hover:bg-slate-50 dark:group-hover:bg-slate-800/30 z-10 border-l border-slate-100 dark:border-slate-800">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="openMappingModal(item)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Map">
                                        <LinkIcon class="w-4 h-4" />
                                    </button>
                                    <button @click="openEditModal(item)" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg" title="Edit">
                                        <PencilSquareIcon class="w-4 h-4" />
                                    </button>
                                    <button @click="confirmDelete(item)" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg" title="Delete">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!evidences.data?.length">
                            <td colspan="13" class="px-4 py-12 text-center">
                                <FolderOpenIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                                <p class="text-slate-500">No documents found</p>
                                <button @click="openCreateModal" class="mt-2 text-indigo-600 hover:text-indigo-700 text-sm font-medium">Add your first evidence</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

            <!-- Pagination -->
            <div v-if="evidences.links?.length > 3" class="flex justify-center">
                <div class="flex items-center gap-1">
                    <template v-for="(link, k) in evidences.links" :key="k">
                        <span v-if="!link.url" class="px-3 py-1.5 text-sm text-slate-400" v-html="link.label" />
                        <Link v-else :href="link.url" class="px-3 py-1.5 text-sm rounded-lg" 
                            :class="link.active ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100'" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <EvidenceModal :isOpen="isModalOpen" :evalId="0" :evidence="editingEvidence" @close="isModalOpen = false" />
        <MappingModal v-if="isMappingOpen" :isOpen="isMappingOpen" :evidence="mappingEvidence" :assessments="assessments" @close="isMappingOpen = false" />
        
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
