<script setup>
/**
 * ImportLibraryModal.vue - Import evidence from master library to current assessment
 */
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    XMarkIcon, 
    ArrowDownTrayIcon,
    CheckIcon,
    MagnifyingGlassIcon,
    DocumentTextIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    isOpen: { type: Boolean, required: true },
    evalId: { type: [String, Number], required: true }
});

const emit = defineEmits(['close', 'imported']);

const loading = ref(false);
const fetchLoading = ref(false);
const searchQuery = ref('');
const selectedItems = ref([]);
const libraryItems = ref([]);

// Fetch library items
onMounted(async () => {
    fetchLoading.value = true;
    try {
        const response = await fetch(`/assessment-eval/${props.evalId}/evidence/previous?per_page=50`);
        const data = await response.json();
        libraryItems.value = data.data || [];
    } catch (e) {
        console.error('Failed to fetch library items');
    }
    fetchLoading.value = false;
});

// Filtered items
const filteredItems = computed(() => {
    if (!searchQuery.value) return libraryItems.value;
    const q = searchQuery.value.toLowerCase();
    return libraryItems.value.filter(item => 
        item.judul_dokumen?.toLowerCase().includes(q) || 
        item.no_dokumen?.toLowerCase().includes(q)
    );
});

const toggleSelect = (item) => {
    const idx = selectedItems.value.findIndex(i => i.id === item.id);
    if (idx > -1) {
        selectedItems.value.splice(idx, 1);
    } else {
        selectedItems.value.push(item);
    }
};

const isSelected = (item) => {
    return selectedItems.value.some(i => i.id === item.id);
};

const close = () => {
    emit('close');
};

const importSelected = async () => {
    if (selectedItems.value.length === 0) return;
    
    loading.value = true;
    
    // Import by mapping each evidence to this eval_id
    for (const item of selectedItems.value) {
        try {
            await fetch('/assessment-eval/evidence/map', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({
                    evidence_id: item.id,
                    eval_id: props.evalId
                })
            });
        } catch (e) {
            console.error('Failed to import', item.id);
        }
    }
    
    loading.value = false;
    emit('imported');
    close();
    
    // Reload page
    router.reload();
};
</script>

<template>
    <Teleport to="body">
        <div 
            v-if="isOpen" 
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <!-- Backdrop -->
            <div 
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                @click="close"
            />

            <!-- Modal -->
            <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden max-h-[85vh] flex flex-col">
                
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl flex items-center justify-center">
                            <ArrowDownTrayIcon class="w-5 h-5 text-indigo-600" />
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white">Import from Library</h3>
                            <p class="text-xs text-slate-500">Select evidence from previous assessments</p>
                        </div>
                    </div>
                    <button @click="close" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Search -->
                    <div class="relative mb-4">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            placeholder="Search documents..." 
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        />
                    </div>

                    <!-- Loading -->
                    <div v-if="fetchLoading" class="py-12 text-center text-slate-500">
                        Loading library...
                    </div>

                    <!-- Items List -->
                    <div v-else class="space-y-2">
                        <div 
                            v-for="item in filteredItems" 
                            :key="item.id"
                            @click="toggleSelect(item)"
                            class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all"
                            :class="isSelected(item) 
                                ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' 
                                : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'"
                        >
                            <div 
                                class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                                :class="isSelected(item) 
                                    ? 'border-indigo-500 bg-indigo-500' 
                                    : 'border-slate-300 dark:border-slate-600'"
                            >
                                <CheckIcon v-if="isSelected(item)" class="w-3 h-3 text-white" />
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0">
                                <DocumentTextIcon class="w-4 h-4 text-slate-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-900 dark:text-white truncate">{{ item.judul_dokumen }}</p>
                                <p class="text-xs text-slate-500">{{ item.no_dokumen || 'No reference' }} • {{ item.grup || '-' }} • {{ item.assessment_year || item.tahun_terbit || '-' }}</p>
                            </div>
                        </div>

                        <!-- Empty -->
                        <div v-if="filteredItems.length === 0 && !fetchLoading" class="py-12 text-center text-slate-500">
                            No documents found in library
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <span class="text-sm text-slate-500">
                        {{ selectedItems.length }} selected
                    </span>
                    <div class="flex items-center gap-3">
                        <button 
                            @click="close"
                            class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="importSelected"
                            :disabled="selectedItems.length === 0 || loading"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <ArrowDownTrayIcon v-if="!loading" class="w-4 h-4" />
                            <span v-if="loading">Importing...</span>
                            <span v-else>Import Selected</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
