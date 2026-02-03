<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    spreadsheet: Object
});

const page = usePage();
const csrfToken = computed(() => page.props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.content);

// State
const isFullscreen = ref(false);
const isSaving = ref(false);
const statusMessage = ref('');
const statusType = ref('info');
const sheetsData = ref([]);
const activeSheetIndex = ref(0);
let jspreadsheetInstance = null;

// Delete Sheet Modal
const showDeleteSheetModal = ref(false);
const deleteSheetIndex = ref(null);

// Status helpers
const showStatus = (message, type = 'info') => {
    statusMessage.value = message;
    statusType.value = type;
    if (type === 'success') {
        setTimeout(() => statusMessage.value = '', 3000);
    }
};

// Grid helpers
const getDefaultGrid = (rows = 30, cols = 20) => {
    return Array(rows).fill().map(() => Array(cols).fill(''));
};

const getDefaultColumns = (count = 20) => {
    return Array(count).fill().map(() => ({ type: 'text', width: 120 }));
};

const getDefaultSheet = (name = 'Sheet 1') => ({
    name,
    cells: getDefaultGrid(),
    style: {},
    mergeCells: {},
    colWidths: null
});

// Get worksheet
const getWorksheet = () => {
    return jspreadsheetInstance?.[0] || null;
};

// Save current sheet state
const saveCurrentSheetState = () => {
    const ws = getWorksheet();
    if (ws && sheetsData.value[activeSheetIndex.value]) {
        sheetsData.value[activeSheetIndex.value].cells = ws.getData();
        sheetsData.value[activeSheetIndex.value].style = ws.getStyle();
        sheetsData.value[activeSheetIndex.value].mergeCells = ws.getMerge();
        try { 
            sheetsData.value[activeSheetIndex.value].colWidths = ws.getWidth(); 
        } catch(e) {}
    }
};

// Load sheet into Jspreadsheet
const loadSheet = (sheetData) => {
    const container = document.getElementById('spreadsheet-container');
    if (!container) {
        console.error('[Spreadsheet] Container not found');
        return;
    }
    container.innerHTML = '';

    // Check if jspreadsheet is available
    if (typeof window.jspreadsheet === 'undefined') {
        console.error('[Spreadsheet] jspreadsheet not loaded');
        container.innerHTML = '<div class="p-8 text-center text-gray-500">Loading spreadsheet library...</div>';
        // Retry after a short delay
        setTimeout(() => loadSheet(sheetData), 500);
        return;
    }

    let columns = getDefaultColumns();
    if (sheetData.colWidths?.length) {
        columns = sheetData.colWidths.map(w => ({ type: 'text', width: w || 120 }));
    }

    try {
        jspreadsheetInstance = window.jspreadsheet(container, {
            toolbar: true,
            worksheets: [{
                data: sheetData.cells || getDefaultGrid(),
                style: sheetData.style || {},
                mergeCells: sheetData.mergeCells || {},
                worksheetName: sheetData.name,
                minDimensions: [20, 30],
                tableOverflow: true,
                tableWidth: '100%',
                tableHeight: '500px',
                columnDrag: true,
                rowDrag: true,
                columnResize: true,
                rowResize: true,
                wordWrap: true,
                columns
            }]
        });
        console.log('[Spreadsheet] Loaded successfully');
    } catch (error) {
        console.error('[Spreadsheet] Error loading:', error);
        container.innerHTML = '<div class="p-8 text-center text-red-500">Error loading spreadsheet: ' + error.message + '</div>';
    }
};

// Switch sheet
const switchSheet = (index) => {
    if (index === activeSheetIndex.value) return;
    saveCurrentSheetState();
    activeSheetIndex.value = index;
    loadSheet(sheetsData.value[index]);
};

// Add sheet
const addSheet = () => {
    saveCurrentSheetState();
    const newSheet = getDefaultSheet(`Sheet ${sheetsData.value.length + 1}`);
    sheetsData.value.push(newSheet);
    activeSheetIndex.value = sheetsData.value.length - 1;
    loadSheet(newSheet);
};

// Open delete sheet modal
const openDeleteSheetModal = (index) => {
    deleteSheetIndex.value = index;
    showDeleteSheetModal.value = true;
};

// Confirm delete sheet
const confirmDeleteSheet = () => {
    if (deleteSheetIndex.value === null || sheetsData.value.length <= 1) return;
    
    sheetsData.value.splice(deleteSheetIndex.value, 1);
    if (activeSheetIndex.value >= sheetsData.value.length) {
        activeSheetIndex.value = sheetsData.value.length - 1;
    }
    loadSheet(sheetsData.value[activeSheetIndex.value]);
    showDeleteSheetModal.value = false;
    deleteSheetIndex.value = null;
};

// Rename sheet
const renameSheet = (index) => {
    const newName = prompt('Nama sheet:', sheetsData.value[index].name);
    if (newName?.trim()) {
        sheetsData.value[index].name = newName.trim();
    }
};

// Save to server
const saveData = async () => {
    saveCurrentSheetState();
    isSaving.value = true;
    showStatus('Menyimpan...', 'warning');

    try {
        const response = await fetch(`/spreadsheet/${props.spreadsheet.id}/save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                data: {
                    sheets: sheetsData.value,
                    activeSheet: activeSheetIndex.value
                }
            })
        });

        const result = await response.json();
        if (result.success) {
            showStatus('Tersimpan!', 'success');
        } else {
            throw new Error(result.message || 'Gagal menyimpan');
        }
    } catch (error) {
        showStatus('Error: ' + error.message, 'danger');
    } finally {
        isSaving.value = false;
    }
};

// Fullscreen
const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
};

// Initialize
onMounted(() => {
    let rawData = props.spreadsheet?.data;
    
    if (typeof rawData === 'string' && rawData.length > 0) {
        try { rawData = JSON.parse(rawData); } catch (e) { rawData = null; }
    }

    // Multi-sheet format
    if (rawData?.sheets && Array.isArray(rawData.sheets)) {
        sheetsData.value = rawData.sheets;
        activeSheetIndex.value = rawData.activeSheet || 0;
    } 
    // Legacy format
    else if (rawData && typeof rawData === 'object') {
        const legacySheet = getDefaultSheet();
        if (rawData.cells && Array.isArray(rawData.cells)) {
            legacySheet.cells = rawData.cells;
            if (rawData.style) legacySheet.style = rawData.style;
            if (rawData.mergeCells) legacySheet.mergeCells = rawData.mergeCells;
            if (rawData.colWidths) legacySheet.colWidths = rawData.colWidths;
        } else if (Array.isArray(rawData)) {
            legacySheet.cells = rawData;
        }
        sheetsData.value = [legacySheet];
    } 
    // Empty
    else {
        sheetsData.value = [getDefaultSheet()];
    }

    loadSheet(sheetsData.value[activeSheetIndex.value]);
});

// Escape key for fullscreen
const handleKeydown = (e) => {
    if (e.key === 'Escape' && isFullscreen.value) {
        isFullscreen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Head :title="spreadsheet?.title || 'Spreadsheet'" />

    <div 
        :class="[
            'min-h-screen transition-all',
            isFullscreen ? 'fixed inset-0 z-50 bg-white dark:bg-[#0f0f0f] p-4 overflow-auto' : ''
        ]"
    >
        <AuthenticatedLayout v-if="!isFullscreen" :title="spreadsheet?.title">
            <template #default>
                <div class="space-y-6">
                    <!-- Header -->
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 p-5">
                        <PageHeader 
                            :title="spreadsheet?.title" 
                            :subtitle="spreadsheet?.description || 'Spreadsheet Editor'"
                            :breadcrumbs="[
                                { label: 'Dashboard', url: '/dashboard' },
                                { label: 'Spreadsheets', url: '/spreadsheet' },
                                { label: spreadsheet?.title || 'Edit' }
                            ]"
                        >
                            <template #actions>
                                <button 
                                    @click="saveData"
                                    :disabled="isSaving"
                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white font-medium rounded-xl transition-colors flex items-center gap-2 shadow-lg shadow-emerald-500/20"
                                >
                                    <svg v-if="isSaving" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                    </svg>
                                    {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
                                </button>
                                <a 
                                    :href="`/spreadsheet/${spreadsheet?.id}/export`"
                                    class="px-4 py-2.5 border border-emerald-500 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 font-medium rounded-xl transition-colors flex items-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Excel
                                </a>
                                <button 
                                    @click="toggleFullscreen"
                                    class="px-3 py-2.5 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 rounded-xl transition-colors"
                                    title="Fullscreen"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                </button>
                                <Link 
                                    href="/spreadsheet"
                                    class="px-4 py-2.5 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 rounded-xl transition-colors flex items-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Kembali
                                </Link>
                            </template>
                        </PageHeader>
                    </div>

                    <!-- Status -->
                    <div 
                        v-if="statusMessage"
                        :class="[
                            'px-4 py-2.5 rounded-xl text-sm font-medium',
                            statusType === 'success' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : '',
                            statusType === 'warning' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : '',
                            statusType === 'danger' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : '',
                            statusType === 'info' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : ''
                        ]"
                    >
                        {{ statusMessage }}
                    </div>

                    <!-- Spreadsheet Container -->
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-t-2xl border border-b-0 border-gray-200/80 dark:border-white/5 overflow-x-auto">
                        <div id="spreadsheet-container" class="min-h-[65vh] w-full"></div>
                    </div>

                    <!-- Sheet Tabs -->
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-b-2xl border border-t-0 border-gray-200/80 dark:border-white/5 p-3 flex items-center gap-2 flex-wrap">
                        <button
                            v-for="(sheet, index) in sheetsData"
                            :key="index"
                            @click="switchSheet(index)"
                            @dblclick="renameSheet(index)"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2',
                                index === activeSheetIndex 
                                    ? 'bg-blue-600 text-white' 
                                    : 'bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-white/10'
                            ]"
                        >
                            {{ sheet.name }}
                            <button 
                                v-if="sheetsData.length > 1"
                                @click.stop="openDeleteSheetModal(index)"
                                class="opacity-60 hover:opacity-100"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </button>
                        <button 
                            @click="addSheet"
                            class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-900/50 flex items-center justify-center transition-colors"
                            title="Tambah Sheet"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>

                    <!-- Help -->
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        <strong>Shortcut:</strong> Ctrl+C (Copy) | Ctrl+V (Paste) | Ctrl+Z (Undo) | Ctrl+Y (Redo) | Delete (Clear)
                    </p>
                </div>
            </template>
        </AuthenticatedLayout>

        <!-- Fullscreen Mode -->
        <div v-else class="h-full flex flex-col">
            <div class="flex-shrink-0 flex justify-between items-center mb-4">
                <h2 class="font-bold text-gray-900 dark:text-white text-lg">{{ spreadsheet?.title }}</h2>
                <div class="flex gap-2">
                    <button 
                        @click="saveData"
                        :disabled="isSaving"
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-colors"
                    >
                        {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                    <button 
                        @click="toggleFullscreen"
                        class="px-3 py-2 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-400 rounded-xl"
                        title="Keluar Fullscreen"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex-1 bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 overflow-hidden">
                <div id="spreadsheet-container"></div>
            </div>
        </div>

        <!-- Delete Sheet Modal -->
        <ConfirmModal
            :show="showDeleteSheetModal"
            title="Hapus Sheet?"
            :message="`Apakah Anda yakin ingin menghapus '${sheetsData[deleteSheetIndex]?.name}'?`"
            confirmText="Ya, Hapus"
            cancelText="Batal"
            type="warning"
            @close="showDeleteSheetModal = false"
            @confirm="confirmDeleteSheet"
        />
    </div>
</template>

<style>
/* Spreadsheet Container */
#spreadsheet-container {
    width: 100%;
    overflow-x: auto;
}

/* JSpreadsheet Styling */
.jss {
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif !important;
    width: 100% !important;
}

.jss_container {
    width: 100% !important;
}

.jss > table {
    width: 100% !important;
}

.jtoolbar {
    background-color: #f8f9fa !important;
    border-bottom: 1px solid #e0e0e0 !important;
    padding: 8px 12px !important;
}

.dark .jtoolbar {
    background-color: #1a1a1a !important;
    border-color: rgba(255,255,255,0.05) !important;
}

.jtoolbar-item {
    color: #5f6368 !important;
    border-radius: 6px;
    transition: all 0.15s ease;
}

.jtoolbar-item:hover {
    background-color: #e8eaed !important;
}

.dark .jtoolbar-item {
    color: #9ca3af !important;
}

.dark .jtoolbar-item:hover {
    background-color: rgba(255,255,255,0.1) !important;
}

.jss td {
    border-right: 1px solid #e0e0e0 !important;
    border-bottom: 1px solid #e0e0e0 !important;
}

.dark .jss td {
    border-color: rgba(255,255,255,0.05) !important;
    background-color: #1a1a1a !important;
    color: #fff !important;
}

.jss thead td {
    background-color: #f8f9fa !important;
    font-weight: 600;
    color: #5f6368 !important;
}

.dark .jss thead td {
    background-color: #252525 !important;
    color: #9ca3af !important;
}

.jss tbody tr td:first-child {
    background-color: #f8f9fa !important;
    font-weight: 600;
    color: #5f6368 !important;
}

.dark .jss tbody tr td:first-child {
    background-color: #252525 !important;
    color: #9ca3af !important;
}

/* Ensure the jexcel wrapper fills the container */
.jexcel_content,
.jexcel_container {
    width: 100% !important;
}
</style>
